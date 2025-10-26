<?php
//declare(strict_types=1);

namespace Modules\Orm\Core;

use Modules\Orm\Exceptions\ModelNotFoundException;
use Modules\Orm\Support\Collection;

/**
 * Minimal Active Record-style model for Yore ORM.
 */
class Model
{
    protected static string $table; // required in subclasses
    protected static string $primaryKey = 'id';
    protected static string $connection = 'default';
    /** @var string[] */
    protected static array $fillable = [];

    /** @var array<string,mixed> */
    protected array $attributes = [];
    protected bool $exists = false;

    public function __construct(array $attributes = [], bool $exists = false)
    {
        $this->fill($attributes);
        $this->exists = $exists;
    }

    public static function hydrate(array $row, ?string $connection = null): static
    {
        $model = new static($row, true);
        if ($connection !== null) {
            $model->setConnection($connection);
        }
        return $model;
    }

    public function fill(array $attrs): void
    {
        if (static::$fillable !== []) {
            $allowed = array_flip(static::$fillable);
            foreach ($attrs as $k => $v) {
                if (isset($allowed[$k])) {
                    $this->attributes[$k] = $v;
                }
            }
        } else {
            foreach ($attrs as $k => $v) {
                $this->attributes[$k] = $v;
            }
        }
    }

    public function getAttribute(string $key): mixed { return $this->attributes[$key] ?? null; }
    public function setAttribute(string $key, mixed $value): void { $this->attributes[$key] = $value; }

    public function getKeyName(): string { return static::$primaryKey; }
    public function getKey(): mixed { return $this->getAttribute($this->getKeyName()); }

    public function getTable(): string { return static::$table; }
    public function getConnectionName(): string { return static::$connection; }
    public function setConnection(string $name): void { static::$connection = $name; }

    public static function query(): QueryBuilder
    {
        return new QueryBuilder(static::$table, static::class, static::$connection);
    }

    public static function all(): Collection
    {
        return static::query()->get();
    }

    public static function find(mixed $id): static
    {
        $row = static::query()->where(static::$primaryKey, '=', $id)->first();
        if (!$row instanceof static) {
            throw new ModelNotFoundException(static::class . ' not found for id ' . (string)$id);
        }
        return $row;
    }

    public static function findOrNull(mixed $id): ?static
    {
        $row = static::query()->where(static::$primaryKey, '=', $id)->first();
        return $row instanceof static ? $row : null;
    }

    public static function create(array $attributes): static
    {
        $m = new static($attributes, false);
        $m->save();
        return $m;
    }

    // Alias: exactly like create()
    public static function store(array $attrs): static
    {
        return static::create($attrs);
    }

    // Find or create variants
    public static function firstOrNew(array $match, array $values = []): static
    {
        $qb = static::query();
        foreach ($match as $k => $v) {
            $qb->where((string)$k, '=', $v);
        }
        $row = $qb->first();
        if ($row instanceof static) {
            return $row;
        }
        return new static($match + $values, false);
    }

    public static function firstOrCreate(array $match, array $values = []): static
    {
        $m = static::firstOrNew($match, $values);
        if (!$m->exists()) {
            $m->save();
        }
        return $m;
    }

    public static function updateOrCreate(array $match, array $values): static
    {
        $qb = static::query();
        foreach ($match as $k => $v) { $qb->where((string)$k, '=', $v); }
        $row = $qb->first();
        if ($row instanceof static) {
            $row->updateAttributes($values);
            return $row;
        }
        return static::create($match + $values);
    }

    // Bulk update based on where conditions
    public static function updateWhere(array $values, array $where): int
    {
        $qb = static::query();
        foreach ($where as $k => $v) {
            $qb->where((string)$k, '=', $v);
        }
        return $qb->update($values);
    }

    // Bulk insert (multi-row)
    public static function insertMany(array $rows): int
    {
        if ($rows === []) { return 0; }
        $count = 0;
        foreach ($rows as $row) {
            static::create((array)$row);
            $count++;
        }
        return $count;
    }

    // Portable upsert skeleton; TODO implement per-driver optimization
    public static function upsert(array $rows, array $uniqueBy, array $updateCols): int
    {
        // Minimal stub for now; follow-up prompt will implement properly
        return 0; // TODO: implement ON DUPLICATE KEY/ON CONFLICT variants
    }

    // Utility sugar
    public static function findBy(string $column, mixed $value): ?static
    {
        $row = static::query()->where($column, '=', $value)->first();
        return $row instanceof static ? $row : null;
    }

    public static function whereIn(string $column, array $values): QueryBuilder
    {
        return static::query()->where($column, 'IN', $values);
    }

    // Meta
    public static function lastInsertId(): ?string
    {
        return DB::connection(static::$connection)->lastInsertId();
    }

    // Instance helpers
    public function updateAttributes(array $values): void
    {
        $this->fill($values);
        $this->save();
    }

    public function refresh(): void
    {
        $pk = $this->getKey();
        if ($pk === null) { return; }
        $fresh = static::find($pk);
        $this->attributes = $fresh->toArray();
        $this->exists = true;
    }

    /**
     * DataTables-ready index with flexible input.
     * @param array $params
     * @param array $options
     * @return array{draw:int,recordsTotal:int,recordsFiltered:int,data:array}
     */
    public static function index(array $params, array $options = []): array
    {
        $columns    = array_values(array_filter($options['columns'] ?? [], 'is_string'));
        $searchable = array_values(array_filter($options['searchable'] ?? $columns, 'is_string'));
        $orderable  = array_values(array_filter($options['orderable'] ?? $columns, 'is_string'));
        $as         = ($options['as'] ?? 'array') === 'models' ? 'models' : 'array';
        $maxLength  = (int)($options['maxLength'] ?? 1000);
        if ($maxLength <= 0) { $maxLength = 1000; }

        $total = (int)static::query()->count();

        $qb = static::query();

        $isDataTables = array_key_exists('draw', $params) || array_key_exists('columns', $params) || array_key_exists('order', $params);

        if (!$isDataTables) {
            // Simple map
            $term = null;
            foreach ($params as $k => $v) {
                if ($k === 'find') { $term = is_scalar($v) ? (string)$v : null; continue; }
                if (!is_scalar($v)) { continue; }
                $k = (string)$k;
                if (in_array($k, $columns, true) || in_array($k, $searchable, true)) {
                    $qb->where($k, '=', $v);
                }
            }
            if ($term !== null && $term !== '' && $searchable !== []) {
                // OR across searchable columns (not grouped; acceptable for minimal pass)
                $first = true;
                foreach ($searchable as $col) {
                    if ($first) { $qb->where($col, 'LIKE', '%' . $term . '%'); $first = false; }
                    else { $qb->orWhere($col, 'LIKE', '%' . $term . '%'); }
                }
            }
            $draw = 0;
            $start = 0;
            $length = min($maxLength, (int)($params['length'] ?? 10));
            if ($length <= 0) { $length = $maxLength; }
        } else {
            // DataTables
            $draw = (int)($params['draw'] ?? 0);
            $start = max(0, (int)($params['start'] ?? 0));
            $length = (int)($params['length'] ?? 10);
            if ($length < 0) { $length = 10; }
            if ($length > $maxLength) { $length = $maxLength; }

            $global = trim((string)($params['search']['value'] ?? ''));
            if ($global !== '' && $searchable !== []) {
                $first = true;
                foreach ($searchable as $col) {
                    if ($first) { $qb->where($col, 'LIKE', '%' . $global . '%'); $first = false; }
                    else { $qb->orWhere($col, 'LIKE', '%' . $global . '%'); }
                }
            }

            if (isset($params['columns']) && is_array($params['columns'])) {
                foreach ($params['columns'] as $i => $colDef) {
                    $sv = trim((string)($colDef['search']['value'] ?? ''));
                    // Determine column name via whitelist by index or provided data/name
                    $cName = $columns[(int)$i] ?? (string)($colDef['data'] ?? $colDef['name'] ?? '');
                    if ($sv !== '' && in_array($cName, $columns, true)) {
                        $qb->where($cName, 'LIKE', '%' . $sv . '%');
                    }
                }
            }

            if (isset($params['order']) && is_array($params['order'])) {
                foreach ($params['order'] as $ord) {
                    $idx = (int)($ord['column'] ?? -1);
                    $dir = strtolower((string)($ord['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
                    $cName = $columns[$idx] ?? null;
                    if ($cName && in_array($cName, $orderable, true)) {
                        $qb->orderBy($cName, $dir);
                    }
                }
            }
        }

        $filtered = (int)(clone $qb)->count();

        $qb->limit($length)->offset($start);
        $rows = $qb->get()->all();

        if ($as === 'array') {
            $data = [];
            foreach ($rows as $r) {
                $data[] = $r instanceof static ? $r->toArray() : (array)$r;
            }
        } else {
            // models
            $data = [];
            foreach ($rows as $r) {
                $data[] = $r instanceof static ? $r->toArray() : (array)$r;
            }
        }

        return [
            'draw' => (int)$draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data,
        ];
    }

    /**
     * Convenience wrapper that consumes $_REQUEST directly.
     */
    public static function indexFromRequest(?array $options = null): array
    {
        return static::index($_REQUEST ?? [], $options ?? []);
    }

    public function save(): void
    {
        $driver = DB::connection(static::$connection);
        $tableId = $driver->quoteIdentifier(static::$table);

        if ($this->exists === false) {
            // INSERT
            $cols = array_keys($this->attributes);
            $placeholders = implode(', ', array_fill(0, count($cols), '?'));
            $sql = 'INSERT INTO ' . $tableId . ' (' . implode(', ', array_map([$driver, 'quoteIdentifier'], $cols)) . ') VALUES (' . $placeholders . ')';
            $params = array_values($this->attributes);
            $driver->insert($sql, $params);
            // Set PK if not provided
            $pk = static::$primaryKey;
            if (!array_key_exists($pk, $this->attributes) || $this->attributes[$pk] === null) {
                $id = $driver->lastInsertId();
                if ($id !== null) {
                    $this->attributes[$pk] = ctype_digit((string)$id) ? (int)$id : $id;
                }
            }
            $this->exists = true;
        } else {
            // UPDATE by PK
            $pk = static::$primaryKey;
            $keyVal = $this->getKey();
            $assign = [];
            $params = [];
            foreach ($this->attributes as $col => $val) {
                if ($col === $pk) { continue; }
                $assign[] = $driver->quoteIdentifier($col) . ' = ?';
                $params[] = $val;
            }
            if ($assign === []) {
                return; // nothing to update
            }
            $sql = 'UPDATE ' . $tableId . ' SET ' . implode(', ', $assign) . ' WHERE ' . $driver->quoteIdentifier($pk) . ' = ?';
            $params[] = $keyVal;
            $driver->update($sql, $params);
        }
    }

    public function delete(): void
    {
        if (!$this->exists) { return; }
        $driver = DB::connection(static::$connection);
        $pk = static::$primaryKey;
        $sql = 'DELETE FROM ' . $driver->quoteIdentifier(static::$table) . ' WHERE ' . $driver->quoteIdentifier($pk) . ' = ?';
        $driver->delete($sql, [$this->getKey()]);
        $this->exists = false;
    }

    // Shortcuts
    public static function where(string $c, string $op, mixed $v): QueryBuilder { return static::query()->where($c, $op, $v); }
    public static function orderBy(string $c, string $dir = 'asc'): QueryBuilder { return static::query()->orderBy($c, $dir); }
    public static function pluck(string $c): array { return static::query()->pluck($c); }

    public function toArray(): array { return $this->attributes; }

    public function exists(): bool { return $this->exists; }
}
