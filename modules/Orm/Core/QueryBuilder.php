<?php
declare(strict_types=1);

namespace Modules\Orm\Core;

use Modules\Orm\Contracts\DriverInterface;
use Modules\Orm\Support\Collection;

/**
 * Minimal SQL query builder. Identifiers are quoted via the active driver.
 */
class QueryBuilder
{
    private string $table;
    private ?string $modelClass;
    private string $connection;

    /** @var string[] */
    private array $columns = ['*'];

    /** @var array<int,array{bool:string,col:string,op:string,val:mixed}> */
    private array $wheres = [];

    /** @var array<int,array{type:string,table:string,left:string,op:string,right:string}> */
    private array $joins = [];

    /** @var array<int,array{col:string,dir:string}> */
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
        if ($columns) {
            $this->columns = $columns;
        }
        return $this;
    }

    public function where(string $column, string $operator, mixed $value): self
    {
        $this->wheres[] = ['bool' => 'AND', 'col' => $column, 'op' => strtoupper($operator), 'val' => $value];
        return $this;
    }

    public function orWhere(string $column, string $operator, mixed $value): self
    {
        $this->wheres[] = ['bool' => 'OR', 'col' => $column, 'op' => strtoupper($operator), 'val' => $value];
        return $this;
    }

    public function join(string $table, string $left, string $operator, string $right, string $type = 'INNER'): self
    {
        $type = strtoupper($type);
        if (!in_array($type, ['INNER', 'LEFT'], true)) {
            $type = 'INNER';
        }
        $this->joins[] = ['type' => $type, 'table' => $table, 'left' => $left, 'op' => $operator, 'right' => $right];
        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $dir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orders[] = ['col' => $column, 'dir' => $dir];
        return $this;
    }

    public function limit(int $n): self
    {
        if ($n < 0) { $n = 0; }
        $this->limit = $n;
        return $this;
    }

    public function offset(int $n): self
    {
        if ($n < 0) { $n = 0; }
        $this->offset = $n;
        return $this;
    }

    public function get(): Collection
    {
        $driver = $this->driver();
        $sql = $this->toSelectSql($driver, $params);
        $rows = $driver->select($sql, $params);
        if ($this->modelClass) {
            $models = [];
            $cls = $this->modelClass;
            foreach ($rows as $row) {
                $models[] = $cls::hydrate($row, $this->connection);
            }
            return new Collection($models);
        }
        return new Collection($rows);
    }

    public function first(): Model|array|null
    {
        $this->limit(1);
        $items = $this->get()->all();
        if (!$items) { return null; }
        return $items[0];
    }

    public function value(string $column): mixed
    {
        $row = $this->select($column)->first();
        if (is_array($row)) { return $row[$column] ?? null; }
        if ($row instanceof Model) { return $row->getAttribute($column); }
        return null;
    }

    public function pluck(string $column): array
    {
        return $this->get()->pluck($column);
    }

    public function exists(): bool
    {
        return $this->count() > 0;
    }

    public function count(): int { return (int)$this->aggregate('COUNT(*)'); }
    public function max(string $column): mixed { return $this->aggregate('MAX(' . $this->id($column) . ')'); }
    public function min(string $column): mixed { return $this->aggregate('MIN(' . $this->id($column) . ')'); }
    public function sum(string $column): mixed { return $this->aggregate('SUM(' . $this->id($column) . ')'); }
    public function avg(string $column): mixed { return $this->aggregate('AVG(' . $this->id($column) . ')'); }

    public function update(array $values): int
    {
        $driver = $this->driver();
        $assign = [];
        $params = [];
        foreach ($values as $col => $val) {
            $assign[] = $this->id($col) . ' = ?';
            $params[] = $val;
        }
        $sql = 'UPDATE ' . $this->id($this->table) . ' SET ' . implode(', ', $assign);
        $sql .= $this->whereSql($driver, $params);
        return $driver->update($sql, $params);
    }

    public function delete(): int
    {
        $driver = $this->driver();
        $params = [];
        $sql = 'DELETE FROM ' . $this->id($this->table) . $this->whereSql($driver, $params);
        return $driver->delete($sql, $params);
    }

    private function toSelectSql(DriverInterface $driver, ?array &$params = null): string
    {
        $params = [];
        $cols = $this->columns === ['*'] ? '*' : implode(', ', array_map(fn($c) => $this->idOrStar($c), $this->columns));
        $sql = 'SELECT ' . $cols . ' FROM ' . $this->id($this->table);
        foreach ($this->joins as $j) {
            $sql .= ' ' . $j['type'] . ' JOIN ' . $this->id($j['table']) . ' ON ' . $this->id($j['left']) . ' ' . $j['op'] . ' ' . $this->id($j['right']);
        }
        $sql .= $this->whereSql($driver, $params) . $this->orderSql($driver) . $this->limitSql();
        return $sql;
    }

    private function whereSql(DriverInterface $driver, array &$params): string
    {
        if (!$this->wheres) { return ''; }
        $parts = [];
        foreach ($this->wheres as $idx => $w) {
            $bool = $idx === 0 ? 'WHERE' : $w['bool'];
            $op = strtoupper($w['op']);
            if ($op === 'IN' && is_array($w['val'])) {
                if (count($w['val']) === 0) {
                    $parts[] = $bool . ' 1=0';
                    continue;
                }
                $placeholders = implode(', ', array_fill(0, count($w['val']), '?'));
                $parts[] = $bool . ' ' . $this->id($w['col']) . ' IN (' . $placeholders . ')';
                array_push($params, ...array_values($w['val']));
            } else {
                $parts[] = $bool . ' ' . $this->id($w['col']) . ' ' . $op . ' ?';
                $params[] = $w['val'];
            }
        }
        return ' ' . implode(' ', $parts);
    }

    private function orderSql(DriverInterface $driver): string
    {
        if (!$this->orders) { return ''; }
        $parts = [];
        foreach ($this->orders as $o) {
            $parts[] = $this->id($o['col']) . ' ' . $o['dir'];
        }
        return ' ORDER BY ' . implode(', ', $parts);
    }

    private function limitSql(): string
    {
        $sql = '';
        if ($this->limit !== null) {
            $sql .= ' LIMIT ' . (int)$this->limit;
        }
        if ($this->offset !== null) {
            $sql .= ' OFFSET ' . (int)$this->offset;
        }
        return $sql;
    }

    private function aggregate(string $expr): mixed
    {
        $driver = $this->driver();
        $params = [];
        $sql = 'SELECT ' . $expr . ' AS agg FROM ' . $this->id($this->table) . $this->whereSql($driver, $params);
        $row = $driver->select($sql, $params)[0] ?? null;
        return $row['agg'] ?? null;
    }

    private function driver(): DriverInterface
    {
        return DB::connection($this->connection);
    }

    private function id(string $name): string
    {
        // Support dotted notation: table.column
        $driver = $this->driver();
        if (str_contains($name, '.')) {
            return implode('.', array_map(fn($p) => $driver->quoteIdentifier($p), explode('.', $name)));
        }
        return $driver->quoteIdentifier($name);
    }

    private function idOrStar(string $name): string
    {
        $trim = trim($name);
        if ($trim === '*') { return '*'; }
        // rudimentary allowance for raw expressions like COUNT(*) or aliases already present
        if (preg_match('/\b(as)\b/i', $trim) === 1 || str_contains($trim, '(')) {
            return $trim;
        }
        return $this->id($trim);
    }
}
