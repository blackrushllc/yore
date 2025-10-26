<?php
declare(strict_types=1);

namespace Modules\Orm\Schema;

use Closure;
use Modules\Orm\Core\DB;

final class Schema
{
    public static function create(string $table, Closure $blueprint, string $connection = 'default'): bool
    {
        $driver = DB::connection($connection);
        $bp = new Blueprint($table, $driver);
        $bp->create = true;
        $blueprint($bp);
        $sql = $bp->toSql();
        foreach (self::splitStatements($sql) as $stmt) {
            if (trim($stmt) === '') { continue; }
            $driver->statement($stmt);
        }
        return true;
    }

    public static function table(string $table, Closure $blueprint, string $connection = 'default'): bool
    {
        $driver = DB::connection($connection);
        $bp = new Blueprint($table, $driver);
        $blueprint($bp);
        $sql = $bp->toSql();
        foreach (self::splitStatements($sql) as $stmt) {
            if (trim($stmt) === '') { continue; }
            $driver->statement($stmt);
        }
        return true;
    }

    public static function drop(string $table, string $connection = 'default'): bool
    {
        $driver = DB::connection($connection);
        return $driver->statement('DROP TABLE ' . $driver->quoteIdentifier($table));
    }

    public static function dropIfExists(string $table, string $connection = 'default'): bool
    {
        $driver = DB::connection($connection);
        return $driver->statement('DROP TABLE IF EXISTS ' . $driver->quoteIdentifier($table));
    }

    /**
     * Split semicolon-joined SQL into individual statements.
     */
    private static function splitStatements(string $sql): array
    {
        $parts = preg_split('/;\s*\n?/', $sql) ?: [];
        return array_filter($parts, fn($s) => trim($s) !== '');
    }
}
