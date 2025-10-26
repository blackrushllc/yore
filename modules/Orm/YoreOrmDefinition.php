<?php
declare(strict_types=1);

/**
 * Yore ORM – Definition v0.1 (SPEC-ONLY, NOT IMPLEMENTED)
 *
 * Goals
 * - Lightweight, Eloquent/Doctrine-inspired API for CRUD, query builder, and schema ops.
 * - PDO-backed; drivers: MySQL-family (MySQL/MariaDB/Percona/Aurora-MySQL) and SQLite.
 * - Safe by default: prepared statements & bound parameters everywhere.
 * - Minimal, predictable surface area suitable for small apps & modules.
 *
 * Non-Goals (v0.1)
 * - No complex relations (belongsTo/hasMany/etc.), no lazy/eager loading, no migrations CLI.
 * - No advanced types beyond a shared subset workable on both MySQL and SQLite.
 * - No polymorphic queries, global scopes, or soft-deletes (could be v0.2+).
 *
 * Naming
 * - Namespace: Modules\Orm
 * - Public API stability target: SemVer minor changes allowed; breaking changes trigger major.
 */

namespace Modules\Orm;

use Closure;
use ArrayAccess;
use IteratorAggregate;
use Traversable;
use PDO;
use PDOStatement;

/* =========================
 * Exceptions
 * ========================= */

class OrmException extends \RuntimeException {}
class DriverException extends OrmException {}
class QueryException extends OrmException {}
class ModelNotFoundException extends OrmException {}

/* =========================
 * Connection & Drivers
 * ========================= */

/**
 * Unified, minimal database driver contract.
 * Implementations MUST use prepared statements & parameter binding.
 */
interface DriverInterface
{
    /**
     * Execute a SELECT and return all rows as associative arrays.
     * @param string $sql SQL with named or positional parameters.
     * @param array<int|string,mixed> $params
     * @return array<int,array<string,mixed>>
     * @throws QueryException
     */
    public function select(string $sql, array $params = []): array;

    /**
     * Execute an INSERT returning the last-insert id if available (string due to PDO).
     * @throws QueryException
     */
    public function insert(string $sql, array $params = []): ?string;

    /**
     * Execute an UPDATE returning rows affected.
     * @throws QueryException
     */
    public function update(string $sql, array $params = []): int;

    /**
     * Execute a DELETE returning rows affected.
     * @throws QueryException
     */
    public function delete(string $sql, array $params = []): int;

    /**
     * Execute arbitrary DDL (CREATE/ALTER/DROP).
     * Return true on success.
     * @throws QueryException
     */
    public function statement(string $sql, array $params = []): bool;

    /** Transaction helpers. */
    public function beginTransaction(): void;
    public function commit(): void;
    public function rollBack(): void;

    /** Return a driver-specific identifier/grammar helper for quoting names. */
    public function quoteIdentifier(string $name): string;

    /** Last insert id (string per PDO) or null if not available. */
    public function lastInsertId(): ?string;

    /** Expose underlying PDO for expert use (read-only use recommended). */
    public function getPdo(): PDO;
}

/**
 * MySQL-family driver (MySQL/MariaDB/Percona/Aurora-MySQL).
 * DSN examples: mysql:host=localhost;dbname=test;charset=utf8mb4
 */
final class PdoMySqlDriver implements DriverInterface
{
    public function __construct(
        private PDO $pdo
    ) {}

    /* SPEC: implement per interface; all methods must throw QueryException on failure. */
    public function select(string $sql, array $params = []): array { throw new \LogicException('spec'); }
    public function insert(string $sql, array $params = []): ?string { throw new \LogicException('spec'); }
    public function update(string $sql, array $params = []): int { throw new \LogicException('spec'); }
    public function delete(string $sql, array $params = []): int { throw new \LogicException('spec'); }
    public function statement(string $sql, array $params = []): bool { throw new \LogicException('spec'); }
    public function beginTransaction(): void { throw new \LogicException('spec'); }
    public function commit(): void { throw new \LogicException('spec'); }
    public function rollBack(): void { throw new \LogicException('spec'); }
    public function quoteIdentifier(string $name): string { return '`' . str_replace('`','``',$name) . '`'; }
    public function lastInsertId(): ?string { throw new \LogicException('spec'); }
    public function getPdo(): PDO { return $this->pdo; }
}

/**
 * SQLite driver.
 * DSN examples: sqlite::memory:, sqlite:/path/to/db.sqlite
 */
final class PdoSqliteDriver implements DriverInterface
{
    public function __construct(
        private PDO $pdo
    ) {}

    public function select(string $sql, array $params = []): array { throw new \LogicException('spec'); }
    public function insert(string $sql, array $params = []): ?string { throw new \LogicException('spec'); }
    public function update(string $sql, array $params = []): int { throw new \LogicException('spec'); }
    public function delete(string $sql, array $params = []): int { throw new \LogicException('spec'); }
    public function statement(string $sql, array $params = []): bool { throw new \LogicException('spec'); }
    public function beginTransaction(): void { throw new \LogicException('spec'); }
    public function commit(): void { throw new \LogicException('spec'); }
    public function rollBack(): void { throw new \LogicException('spec'); }
    public function quoteIdentifier(string $name): string { return '"' . str_replace('"','""',$name) . '"'; }
    public function lastInsertId(): ?string { throw new \LogicException('spec'); }
    public function getPdo(): PDO { return $this->pdo; }
}

/**
 * Connection manager for one or more named connections.
 * Simple static facade to keep footprint small.
 */
final class DB
{
    /** @var array<string,DriverInterface> */
    private static array $connections = [];

    /** Default connection name. */
    private static string $default = 'default';

    /**
     * Register a connection.
     * Example:
     * DB::register('default', new PdoMySqlDriver(new PDO(...)));
     */
    public static function register(string $name, DriverInterface $driver, bool $asDefault = false): void
    {
        self::$connections[$name] = $driver;
        if ($asDefault || !isset(self::$connections[self::$default])) {
            self::$default = $name;
        }
    }

    public static function connection(?string $name = null): DriverInterface
    {
        $name ??= self::$default;
        if (!isset(self::$connections[$name])) {
            throw new DriverException("Connection '{$name}' is not registered.");
        }
        return self::$connections[$name];
    }

    /**
     * Transaction helper: runs $callback within a transaction on the chosen connection.
     * Rolls back on exception and re-throws.
     * @template T
     * @param callable():T $callback
     * @return T
     */
    public static function transaction(callable $callback, ?string $connection = null): mixed
    {
        $driver = self::connection($connection);
        $driver->beginTransaction();
        try {
            $result = $callback();
            $driver->commit();
            return $result;
        } catch (\Throwable $e) {
            $driver->rollBack();
            throw $e;
        }
    }
}

/* =========================
 * Collections
 * ========================= */

/**
 * Simple collection supporting pluck and sorting for in-memory results.
 * Intentionally tiny to avoid bringing in external deps.
 * @implements IteratorAggregate<int,mixed>
 */
final class Collection implements ArrayAccess, IteratorAggregate
{
    /** @param array<int,mixed> $items */
    public function __construct(private array $items = []) {}

    public function offsetExists(mixed $offset): bool { return isset($this->items[$offset]); }
    public function offsetGet(mixed $offset): mixed { return $this->items[$offset] ?? null; }
    public function offsetSet(mixed $offset, mixed $value): void { $offset === null ? $this->items[] = $value : $this->items[$offset] = $value; }
    public function offsetUnset(mixed $offset): void { unset($this->items[$offset]); }

    /** @return Traversable<int,mixed> */
    public function getIterator(): Traversable { yield from $this->items; }

    /** Return raw array. @return array<int,mixed> */
    public function all(): array { return $this->items; }

    /** Pluck column from array of arrays/objects/models. @return array<int,mixed> */
    public function pluck(string $key): array
    {
        $out = [];
        foreach ($this->items as $item) {
            if (is_array($item) && array_key_exists($key, $item)) $out[] = $item[$key];
            elseif (is_object($item) && isset($item->{$key})) $out[] = $item->{$key};
            elseif (is_object($item) && method_exists($item, 'getAttribute')) $out[] = $item->getAttribute($key);
        }
        return $out;
    }

    /** Sort by key (asc). Returns new collection (does not mutate). */
    public function sortBy(string $key): self
    {
        $copy = $this->items;
        usort($copy, static function($a,$b) use ($key) {
            $av = is_array($a) ? ($a[$key] ?? null) : (is_object($a) ? ($a->{$key} ?? null) : null);
            $bv = is_array($b) ? ($b[$key] ?? null) : (is_object($b) ? ($b->{$key} ?? null) : null);
            return $av <=> $bv;
        });
        return new self($copy);
    }

    /** Sort by key (desc). */
    public function sortByDesc(string $key): self
    {
        $copy = $this->items;
        usort($copy, static function($a,$b) use ($key) {
            $av = is_array($a) ? ($a[$key] ?? null) : (is_object($a) ? ($a->{$key} ?? null) : null);
            $bv = is_array($b) ? ($b[$key] ?? null) : (is_object($b) ? ($b->{$key} ?? null) : null);
            return $bv <=> $av;
        });
        return new self($copy);
    }
}

/* =========================
 * Query Builder (no relations)
 * ========================= */

/**
 * Fluent query builder for a single table.
 * Supports where/orWhere, basic joins, order/limit/offset, aggregates, and in-place update/delete.
 */
final class QueryBuilder
{
    private string $table;
    private ?string $modelClass = null;
    private string $connection = 'default';

    /** @var list<string> */
    private array $columns = ['*'];

    /** @var list<string> */
    private array $wheres = [];      // SQL fragments with placeholders
    /** @var array<int|string,mixed> */
    private array $bindings = [];    // flat bindings in order

    /** @var list<string> */
    private array $joins = [];       // pre-validated join fragments
    /** @var list<string> */
    private array $orders = [];
    private ?int $limit = null;
    private ?int $offset = null;

    public function __construct(string $table, ?string $modelClass = null, string $connection = 'default')
    {
        $this->table = $table;
        $this->modelClass = $modelClass;
        $this->connection = $connection;
    }

    public function select(string ...$columns): self
    {
        if ($columns) $this->columns = $columns;
        return $this;
    }

    /** where('age', '>=', 18) / where('status','=','active') / where('id','IN',[1,2,3]) */
    public function where(string $column, string $operator, mixed $value): self
    {
        if (strtoupper($operator) === 'IN' && is_array($value)) {
            $placeholders = implode(',', array_fill(0, count($value), '?'));
            $this->wheres[] = $this->id($column) . " IN ($placeholders)";
            array_push($this->bindings, ...array_values($value));
        } else {
            $this->wheres[] = $this->id($column) . " {$operator} ?";
            $this->bindings[] = $value;
        }
        return $this;
    }

    /** orWhere convenience. */
    public function orWhere(string $column, string $operator, mixed $value): self
    {
        if (empty($this->wheres)) {
            return $this->where($column, $operator, $value);
        }
        $last = array_pop($this->wheres);
        $this->wheres[] = "({$last} OR " . $this->id($column) . " {$operator} ?)";
        $this->bindings[] = $value;
        return $this;
    }

    /** Basic INNER/LEFT JOIN: ->join('profiles','users.id','=','profiles.user_id') */
    public function join(string $table, string $left, string $operator, string $right, string $type = 'INNER'): self
    {
        $t = $this->id($table);
        $this->joins[] = sprintf('%s JOIN %s ON %s %s %s',
            strtoupper($type) === 'LEFT' ? 'LEFT' : 'INNER',
            $t,
            $this->id($left),
            $operator,
            $this->id($right)
        );
        return $this;
    }

    /** orderBy('created_at','desc') */
    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $dir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orders[] = $this->id($column) . " {$dir}";
        return $this;
    }

    public function limit(int $limit): self { $this->limit = $limit; return $this; }
    public function offset(int $offset): self { $this->offset = $offset; return $this; }

    /** Fetch rows and cast to models if a model class is set, else assoc arrays. */
    public function get(): Collection
    {
        $sql = $this->toSelectSql();
        $rows = $this->driver()->select($sql, $this->bindings);
        if ($this->modelClass) {
            $out = [];
            foreach ($rows as $row) {
                /** @var class-string<Model> $cls */
                $cls = $this->modelClass;
                $out[] = $cls::hydrate($row, connection: $this->connection);
            }
            return new Collection($out);
        }
        return new Collection($rows);
    }

    /** First row or null. */
    public function first(): Model|array|null
    {
        $clone = clone $this;
        $clone->limit ??= 1;
        $col = $clone->get()->all();
        return $col[0] ?? null;
    }

    /** Get single scalar value from the first row. */
    public function value(string $column): mixed
    {
        $this->select($column);
        $row = $this->first();
        if ($row instanceof Model) return $row->getAttribute($column);
        return is_array($row) ? ($row[$column] ?? null) : null;
    }

    /** Pluck a column from all rows. */
    public function pluck(string $column): array
    {
        return $this->get()->pluck($column);
    }

    /** Exists check. */
    public function exists(): bool
    {
        $clone = clone $this;
        $clone->select('1');
        $clone->limit(1);
        return $clone->first() !== null;
    }

    /** Aggregates */
    public function count(): int { return (int) $this->aggregate('COUNT(*)'); }
    public function max(string $column): mixed { return $this->aggregate("MAX(".$this->id($column).")"); }
    public function min(string $column): mixed { return $this->aggregate("MIN(".$this->id($column).")"); }
    public function sum(string $column): mixed { return $this->aggregate("SUM(".$this->id($column).")"); }
    public function avg(string $column): mixed { return $this->aggregate("AVG(".$this->id($column).")"); }

    /** Update rows matching the where clause. @param array<string,mixed> $values */
    public function update(array $values): int
    {
        if (empty($values)) return 0;
        $sets = [];
        $binds = [];
        foreach ($values as $k => $v) { $sets[] = $this->id($k) . ' = ?'; $binds[] = $v; }
        $sql = 'UPDATE ' . $this->id($this->table)
            . ' SET ' . implode(', ', $sets)
            . $this->whereSql()
            . $this->orderSql()
            . $this->limitSql();
        return $this->driver()->update($sql, array_merge($binds, $this->bindings));
    }

    /** Delete rows matching where. */
    public function delete(): int
    {
        $sql = 'DELETE FROM ' . $this->id($this->table)
            . $this->whereSql()
            . $this->orderSql()
            . $this->limitSql();
        return $this->driver()->delete($sql, $this->bindings);
    }

    /* ---------- internal helpers ---------- */

    private function toSelectSql(): string
    {
        $sql = 'SELECT ' . implode(', ', array_map([$this,'idOrStar'], $this->columns))
            . ' FROM ' . $this->id($this->table);
        if ($this->joins) { $sql .= ' ' . implode(' ', $this->joins); }
        $sql .= $this->whereSql() . $this->orderSql() . $this->limitSql();
        return $sql;
    }

    private function whereSql(): string
    {
        return $this->wheres ? ' WHERE ' . implode(' AND ', $this->wheres) : '';
    }

    private function orderSql(): string
    {
        return $this->orders ? ' ORDER BY ' . implode(', ', $this->orders) : '';
    }

    private function limitSql(): string
    {
        $sql = '';
        if ($this->limit !== null) $sql .= ' LIMIT ' . (int)$this->limit;
        if ($this->offset !== null) $sql .= ' OFFSET ' . (int)$this->offset;
        return $sql;
    }

    private function aggregate(string $expr): mixed
    {
        $clone = clone $this;
        $clone->columns = [$expr . ' AS _agg'];
        $row = $clone->first();
        if ($row instanceof Model) return $row->getAttribute('_agg');
        return is_array($row) ? ($row['_agg'] ?? null) : null;
    }

    private function driver(): DriverInterface
    {
        return DB::connection($this->connection);
    }

    private function id(string $name): string
    {
        // Allow table.column; split and quote each piece.
        $driver = $this->driver();
        if (str_contains($name, '.')) {
            [$t,$c] = explode('.', $name, 2);
            return $driver->quoteIdentifier($t) . '.' . $driver->quoteIdentifier($c);
        }
        return $driver->quoteIdentifier($name);
    }

    private function idOrStar(string $name): string
    {
        return $name === '*' ? '*' : $this->id($name);
    }
}

/* =========================
 * Base Model
 * ========================= */

abstract class Model
{
    /** Table name (required). */
    protected static string $table;

    /** Primary key column (default 'id'). */
    protected static string $primaryKey = 'id';

    /** Connection name (default 'default'). */
    protected static string $connection = 'default';

    /** Mass-assignment allowlist; empty = allow all (simple v0.1 behavior). */
    protected static array $fillable = [];

    /** @var array<string,mixed> */
    protected array $attributes = [];

    /** Track if model exists in DB. */
    protected bool $exists = false;

    public function __construct(array $attributes = [], bool $exists = false)
    {
        $this->fill($attributes);
        $this->exists = $exists;
    }

    /** INTERNAL: used by QueryBuilder to hydrate a row into a model. */
    public static function hydrate(array $row, ?string $connection = null): static
    {
        $m = new static($row, true);
        if ($connection) $m->setConnection($connection);
        return $m;
    }

    public function setConnection(string $name): void { static::$connection = $name; }

    /** Mass-assign safe attributes. */
    public function fill(array $attrs): void
    {
        if (static::$fillable) {
            foreach ($attrs as $k => $v) {
                if (in_array($k, static::$fillable, true)) {
                    $this->attributes[$k] = $v;
                }
            }
        } else {
            $this->attributes = $attrs + $this->attributes;
        }
    }

    public function getKeyName(): string { return static::$primaryKey; }
    public function getKey(): mixed { return $this->attributes[$this->getKeyName()] ?? null; }

    public function getTable(): string { return static::$table; }
    public function getConnectionName(): string { return static::$connection; }

    public function getAttribute(string $key): mixed { return $this->attributes[$key] ?? null; }
    public function setAttribute(string $key, mixed $value): void { $this->attributes[$key] = $value; }

    /** CRUD */
    public static function query(): QueryBuilder
    {
        return new QueryBuilder(static::$table, static::class, static::$connection);
    }

    public static function all(): Collection { return static::query()->get(); }

    public static function find(mixed $id): static
    {
        $pk = static::$primaryKey;
        $m = static::query()->where($pk, '=', $id)->first();
        if (!$m) throw new ModelNotFoundException(static::class."({$id}) not found");
        return $m;
    }

    public static function findOrNull(mixed $id): ?static
    {
        $pk = static::$primaryKey;
        $m = static::query()->where($pk, '=', $id)->first();
        return $m instanceof static ? $m : null;
    }

    public static function create(array $attributes): static
    {
        $m = new static();
        $m->fill($attributes);
        $m->save();
        return $m;
    }

    /** Insert or update based on $this->exists. */
    public function save(): void
    {
        $driver = DB::connection(static::$connection);
        $pk = $this->getKeyName();
        $table = $driver->quoteIdentifier(static::$table);

        if ($this->exists) {
            // UPDATE
            $updates = [];
            $values = [];
            foreach ($this->attributes as $k => $v) {
                if ($k === $pk) continue;
                $updates[] = $driver->quoteIdentifier($k) . ' = ?';
                $values[] = $v;
            }
            if (!$updates) return;
            $values[] = $this->getKey();
            $sql = "UPDATE {$table} SET " . implode(', ', $updates)
                . " WHERE " . $driver->quoteIdentifier($pk) . " = ?";
            $driver->update($sql, $values);
        } else {
            // INSERT
            $cols = array_keys($this->attributes);
            $placeholders = implode(',', array_fill(0, count($cols), '?'));
            $colSql = implode(', ', array_map([$driver,'quoteIdentifier'], $cols));
            $sql = "INSERT INTO {$table} ({$colSql}) VALUES ({$placeholders})";
            $id = $driver->insert($sql, array_values($this->attributes));
            // If PK is auto-increment and not set, adopt lastInsertId
            if ($id !== null && !isset($this->attributes[$pk])) {
                $this->attributes[$pk] = is_numeric($id) ? (int)$id : $id;
            }
            $this->exists = true;
        }
    }

    public function delete(): void
    {
        if (!$this->exists) return;
        $driver = DB::connection(static::$connection);
        $table = $driver->quoteIdentifier(static::$table);
        $pk = $driver->quoteIdentifier(static::$primaryKey);
        $sql = "DELETE FROM {$table} WHERE {$pk} = ?";
        $driver->delete($sql, [$this->getKey()]);
        $this->exists = false;
    }

    /** Convenience passthroughs */
    public static function where(string $c, string $op, mixed $v): QueryBuilder { return static::query()->where($c,$op,$v); }
    public static function orderBy(string $c, string $dir='asc'): QueryBuilder { return static::query()->orderBy($c,$dir); }
    public static function pluck(string $c): array { return static::query()->pluck($c); }

    /** Convert to array */
    public function toArray(): array { return $this->attributes; }
}

/* =========================
 * Schema Builder (DDL)
 * ========================= */

/**
 * Cross-driver schema builder with a conservative column set compatible with MySQL & SQLite.
 * For anything fancy, use raw statements via DB::connection(...)->statement().
 */
final class Schema
{
    /** CREATE TABLE. */
    public static function create(string $table, Closure $blueprint, string $connection = 'default'): bool
    {
        $driver = DB::connection($connection);
        $bp = new Blueprint($table, $driver);
        $bp->create = true;
        $blueprint($bp);
        $sql = $bp->toSql();
        return $driver->statement($sql);
    }

    /** ALTER TABLE. */
    public static function table(string $table, Closure $blueprint, string $connection = 'default'): bool
    {
        $driver = DB::connection($connection);
        $bp = new Blueprint($table, $driver);
        $blueprint($bp);
        $sql = $bp->toSql();
        return $driver->statement($sql);
    }

    /** DROP TABLE. */
    public static function drop(string $table, string $connection = 'default'): bool
    {
        $driver = DB::connection($connection);
        $t = $driver->quoteIdentifier($table);
        return $driver->statement("DROP TABLE {$t}");
    }

    /** DROP IF EXISTS (SQLite & MySQL both support IF EXISTS). */
    public static function dropIfExists(string $table, string $connection = 'default'): bool
    {
        $driver = DB::connection($connection);
        $t = $driver->quoteIdentifier($table);
        return $driver->statement("DROP TABLE IF EXISTS {$t}");
    }
}

/**
 * Table blueprint used by Schema::create/table.
 * Only provides a conservative set of column helpers.
 */
final class Blueprint
{
    public bool $create = false;

    /** @var list<string> */
    private array $columns = [];
    /** @var list<string> */
    private array $commands = []; // add index/unique; rename/drop column are driver-dependent & limited

    public function __construct(
        private string $table,
        private DriverInterface $driver
    ) {}

    /* ----- Column helpers (subset) ----- */

    public function increments(string $name = 'id'): self
    {
        // MySQL: INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
        // SQLite: INTEGER PRIMARY KEY AUTOINCREMENT
        $col = $this->driver instanceof PdoSqliteDriver
            ? $this->id($name) . ' INTEGER PRIMARY KEY AUTOINCREMENT'
            : $this->id($name) . ' INT UNSIGNED AUTO_INCREMENT PRIMARY KEY';
        $this->columns[] = $col;
        return $this;
    }

    public function bigIncrements(string $name = 'id'): self
    {
        $col = $this->driver instanceof PdoSqliteDriver
            ? $this->id($name) . ' INTEGER PRIMARY KEY AUTOINCREMENT'
            : $this->id($name) . ' BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY';
        $this->columns[] = $col;
        return $this;
    }

    public function integer(string $name, bool $unsigned = false, bool $nullable = false): self
    {
        $type = $this->driver instanceof PdoSqliteDriver ? 'INTEGER' : ($unsigned ? 'INT UNSIGNED' : 'INT');
        $this->columns[] = $this->id($name) . " {$type}" . ($nullable ? ' NULL' : ' NOT NULL');
        return $this;
    }

    public function string(string $name, int $length = 255, bool $nullable = false): self
    {
        $type = $this->driver instanceof PdoSqliteDriver ? 'TEXT' : "VARCHAR({$length})";
        $this->columns[] = $this->id($name) . " {$type}" . ($nullable ? ' NULL' : ' NOT NULL');
        return $this;
    }

    public function text(string $name, bool $nullable = false): self
    {
        $this->columns[] = $this->id($name) . ' TEXT' . ($nullable ? ' NULL' : ' NOT NULL');
        return $this;
    }

    public function boolean(string $name, bool $nullable = false): self
    {
        $type = $this->driver instanceof PdoSqliteDriver ? 'INTEGER' : 'TINYINT(1)';
        $this->columns[] = $this->id($name) . " {$type}" . ($nullable ? ' NULL' : ' NOT NULL');
        return $this;
    }

    public function timestamp(string $name, bool $nullable = true): self
    {
        // SQLite lacks native TIMESTAMP; use TEXT
        $type = $this->driver instanceof PdoSqliteDriver ? 'TEXT' : 'TIMESTAMP';
        $this->columns[] = $this->id($name) . " {$type}" . ($nullable ? ' NULL' : ' NOT NULL');
        return $this;
    }

    public function timestamps(): self
    {
        return $this->timestamp('created_at', true)->timestamp('updated_at', true);
    }

    /** Simple index/unique (single column only in v0.1). */
    public function index(string $column, ?string $name = null): self
    {
        $name ??= "idx_{$this->table}_{$column}";
        $this->commands[] = "CREATE INDEX " . $this->id($name) . " ON " . $this->id($this->table) . " (" . $this->id($column) . ")";
        return $this;
    }

    public function unique(string $column, ?string $name = null): self
    {
        $name ??= "uk_{$this->table}_{$column}";
        $this->commands[] = "CREATE UNIQUE INDEX " . $this->id($name) . " ON " . $this->id($this->table) . " (" . $this->id($column) . ")";
        return $this;
    }

    /**
     * Render SQL.
     * - For CREATE: generates CREATE TABLE + post indexes.
     * - For ALTER (limited in SQLite): attempts ALTER TABLE ADD COLUMN only; other ops should use raw SQL.
     */
    public function toSql(): string
    {
        if ($this->create) {
            $body = implode(",\n  ", $this->columns) ?: '';
            $sql = "CREATE TABLE " . $this->id($this->table) . " (\n  {$body}\n)";
            if ($this->commands) {
                $sql .= ";\n" . implode(";\n", $this->commands);
            }
            return $sql;
        }

        // ALTER: support only ADD COLUMN (portable). Advanced alters should use DriverInterface::statement manually.
        if (count($this->columns) === 1 && str_contains($this->columns[0], ' ')) {
            return "ALTER TABLE " . $this->id($this->table) . " ADD COLUMN " . $this->columns[0];
        }

        throw new OrmException('Blueprint ALTER operations beyond ADD COLUMN are not supported in v0.1. Use raw SQL via DB::connection()->statement().');
    }

    private function id(string $name): string
    {
        return $this->driver->quoteIdentifier($name);
    }
}

/* =========================
 * Pagination (optional helper)
 * ========================= */
final class Page
{
    /** @param array<int,mixed> $items */
    public function __construct(
        public array $items,
        public int $total,
        public int $perPage,
        public int $currentPage,
        public int $lastPage
    ) {}
}

final class Paginator
{
    public static function paginate(QueryBuilder $qb, int $perPage = 15, int $page = 1): Page
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        // Clone: count total
        $total = $qb->count();
        // Clone: fetch items
        $items = (clone $qb)->limit($perPage)->offset($offset)->get()->all();
        $last = (int)ceil($total / max(1, $perPage));
        return new Page($items, $total, $perPage, $page, max(1, $last));
    }
}

/* =========================
 * Usage Examples (for reviewers)
 * ========================= */

/*
use Yore\ORM\{DB,PdoMySqlDriver,PdoSqliteDriver,Model,Schema,Blueprint,QueryBuilder};

// 1) Register connections
DB::register('default', new PdoMySqlDriver(new PDO('mysql:host=127.0.0.1;dbname=test;charset=utf8mb4','user','pass')), asDefault:true);
// Or SQLite:
// DB::register('default', new PdoSqliteDriver(new PDO('sqlite:' . __DIR__ . '/app.sqlite')), asDefault:true);

// 2) Define a model
final class User extends Model
{
    protected static string $table = 'users';
    protected static string $primaryKey = 'id';
    protected static array $fillable = ['name','email','is_active','created_at','updated_at'];
}

// 3) Schema create (portable subset)
Schema::create('users', function(Blueprint $t) {
    $t->increments('id');
    $t->string('name', 120);
    $t->string('email', 190);
    $t->boolean('is_active');
    $t->timestamps();
    $t->unique('email');
});

// 4) CRUD
$user = User::create(['name'=>'Alice','email'=>'a@example.com','is_active'=>1]);
$user->setAttribute('name','Alice A.'); $user->save();
$found = User::find($user->getKey());
$found->delete();

// 5) Queries
$active = User::where('is_active','=','1')->orderBy('created_at','desc')->limit(10)->get();
$emails = User::where('is_active','=','1')->pluck('email');
$hasAny = User::where('email','IN',['a@example.com','b@example.com'])->exists();
$count = User::where('is_active','=','1')->count();

// 6) Pagination
$page = \Yore\ORM\Paginator::paginate(User::where('is_active','=','1')->orderBy('id'), perPage:20, page:2);
*/

/* =========================
 * End of spec v0.1
 * ========================= */
