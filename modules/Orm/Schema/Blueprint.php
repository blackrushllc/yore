<?php
declare(strict_types=1);

namespace Modules\Orm\Schema;

use Modules\Orm\Contracts\DriverInterface;
use Modules\Orm\Exceptions\OrmException;

final class Blueprint
{
    public bool $create = false;

    /** @var list<string> */
    private array $columns = [];
    /** @var list<string> */
    private array $commands = [];

    public function __construct(private string $table, private DriverInterface $driver) {}

    // Column helpers
    public function increments(string $name = 'id'): self
    {
        if ($this->isSqlite()) {
            $this->columns[] = $this->id($name) . ' INTEGER PRIMARY KEY AUTOINCREMENT';
        } else {
            $this->columns[] = $this->id($name) . ' INT UNSIGNED AUTO_INCREMENT PRIMARY KEY';
        }
        return $this;
    }

    public function bigIncrements(string $name = 'id'): self
    {
        if ($this->isSqlite()) {
            $this->columns[] = $this->id($name) . ' INTEGER PRIMARY KEY AUTOINCREMENT';
        } else {
            $this->columns[] = $this->id($name) . ' BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY';
        }
        return $this;
    }

    public function integer(string $name, bool $unsigned = false, bool $nullable = false): self
    {
        $type = $this->isSqlite() ? 'INTEGER' : ($unsigned ? 'INT UNSIGNED' : 'INT');
        $this->columns[] = $this->id($name) . ' ' . $type . ($nullable ? ' NULL' : ' NOT NULL');
        return $this;
    }

    public function string(string $name, int $length = 255, bool $nullable = false): self
    {
        $type = $this->isSqlite() ? 'TEXT' : 'VARCHAR(' . $length . ')';
        $this->columns[] = $this->id($name) . ' ' . $type . ($nullable ? ' NULL' : ' NOT NULL');
        return $this;
    }

    public function text(string $name, bool $nullable = false): self
    {
        $this->columns[] = $this->id($name) . ' TEXT' . ($nullable ? ' NULL' : ' NOT NULL');
        return $this;
    }

    public function boolean(string $name, bool $nullable = false): self
    {
        $type = $this->isSqlite() ? 'INTEGER' : 'TINYINT(1)';
        $this->columns[] = $this->id($name) . ' ' . $type . ($nullable ? ' NULL' : ' NOT NULL');
        return $this;
    }

    public function timestamp(string $name, bool $nullable = true): self
    {
        $type = $this->isSqlite() ? 'TEXT' : 'TIMESTAMP';
        $this->columns[] = $this->id($name) . ' ' . $type . ($nullable ? ' NULL' : ' NOT NULL');
        return $this;
    }

    public function timestamps(): self
    {
        return $this->timestamp('created_at', true)->timestamp('updated_at', true);
    }

    public function index(string $column, ?string $name = null): self
    {
        $name ??= 'idx_' . $this->table . '_' . $column;
        $this->commands[] = 'CREATE INDEX ' . $this->id($name) . ' ON ' . $this->id($this->table) . ' (' . $this->id($column) . ')';
        return $this;
    }

    public function unique(string $column, ?string $name = null): self
    {
        $name ??= 'uk_' . $this->table . '_' . $column;
        $this->commands[] = 'CREATE UNIQUE INDEX ' . $this->id($name) . ' ON ' . $this->id($this->table) . ' (' . $this->id($column) . ')';
        return $this;
    }

    /**
     * Render SQL. For CREATE: returns CREATE TABLE plus any index statements joined by ';'.
     * For ALTER: supports single ADD COLUMN only.
     */
    public function toSql(): string
    {
        if ($this->create) {
            $body = implode(",\n  ", $this->columns) ?: '';
            $sql = 'CREATE TABLE ' . $this->id($this->table) . " (\n  {$body}\n)";
            if ($this->commands) {
                $sql .= ";\n" . implode(";\n", $this->commands);
            }
            return $sql;
        }

        if (count($this->columns) === 1 && str_contains($this->columns[0], ' ')) {
            return 'ALTER TABLE ' . $this->id($this->table) . ' ADD COLUMN ' . $this->columns[0];
        }

        throw new OrmException('Blueprint ALTER operations beyond ADD COLUMN are not supported in v0.1. Use raw SQL via DB::connection()->statement().');
    }

    private function id(string $name): string
    {
        if (str_contains($name, '.')) {
            return implode('.', array_map([$this->driver, 'quoteIdentifier'], explode('.', $name)));
        }
        return $this->driver->quoteIdentifier($name);
    }

    private function isSqlite(): bool
    {
        try {
            return $this->driver->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'sqlite';
        } catch (\Throwable) {
            return false;
        }
    }
}
