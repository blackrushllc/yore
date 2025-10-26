<?php
declare(strict_types=1);

namespace Modules\Orm\Core;

use Modules\Orm\Contracts\DriverInterface;
use Modules\Orm\Exceptions\DriverException;
use Modules\Orm\Exceptions\QueryException;

/**
 * Connection manager for Yore ORM.
 */
final class DB
{
    /** @var array<string,DriverInterface> */
    private static array $connections = [];
    private static ?string $default = null;

    public static function register(string $name, DriverInterface $driver, bool $asDefault = false): void
    {
        self::$connections[$name] = $driver;
        if ($asDefault || self::$default === null) {
            self::$default = $name;
        }
    }

    public static function connection(?string $name = null): DriverInterface
    {
        $key = $name ?? self::$default;
        if ($key === null || !isset(self::$connections[$key])) {
            throw new DriverException("Database connection '{$key}' is not registered");
        }
        return self::$connections[$key];
    }

    /**
     * Execute a transaction, committing on success and rolling back on exception.
     * Rethrows the original exception.
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
            try { $driver->rollBack(); } catch (\Throwable) {}
            throw $e;
        }
    }

    /** Optional convenience that routes based on SQL verb. */
    public static function raw(string $sql, array $params = [], ?string $connection = null): array|int|bool
    {
        $driver = self::connection($connection);
        $verb = strtoupper(strtok(ltrim($sql), " \t\r\n"));
        return match ($verb) {
            'SELECT', 'WITH' => $driver->select($sql, $params),
            'INSERT' => $driver->insert($sql, $params) !== null,
            'UPDATE' => $driver->update($sql, $params),
            'DELETE' => $driver->delete($sql, $params),
            default => $driver->statement($sql, $params),
        };
    }
}
