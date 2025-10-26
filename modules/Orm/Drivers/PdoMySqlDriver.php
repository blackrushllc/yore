<?php
declare(strict_types=1);

namespace Modules\Orm\Drivers;

use Modules\Orm\Contracts\DriverInterface;
use Modules\Orm\Exceptions\QueryException;
use PDO;
use PDOException;

/**
 * PDO MySQL-family driver (MySQL/MariaDB/Percona/Aurora-MySQL).
 * Implements DriverInterface using prepared statements and bound params.
 */
final class PdoMySqlDriver implements DriverInterface
{
    public function __construct(private PDO $pdo)
    {
        // Safe defaults
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    public function select(string $sql, array $params = []): array
    {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll() ?: [];
    }

    public function insert(string $sql, array $params = []): ?string
    {
        $this->execute($sql, $params);
        try {
            return $this->pdo->lastInsertId() ?: null;
        } catch (PDOException) {
            return null;
        }
    }

    public function update(string $sql, array $params = []): int
    {
        $stmt = $this->execute($sql, $params);
        return $stmt->rowCount();
    }

    public function delete(string $sql, array $params = []): int
    {
        $stmt = $this->execute($sql, $params);
        return $stmt->rowCount();
    }

    public function statement(string $sql, array $params = []): bool
    {
        $stmt = $this->execute($sql, $params);
        return $stmt !== null;
    }

    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollBack(): void
    {
        $this->pdo->rollBack();
    }

    public function quoteIdentifier(string $name): string
    {
        return '`' . str_replace('`', '``', $name) . '`';
    }

    public function lastInsertId(): ?string
    {
        try {
            return $this->pdo->lastInsertId() ?: null;
        } catch (PDOException) {
            return null;
        }
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    private function execute(string $sql, array $params): \PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($this->normalizeParams($params));
            return $stmt;
        } catch (PDOException $e) {
            $msg = $this->formatError($sql, $params, $e);
            throw new QueryException($msg, (int)$e->getCode(), $e);
        }
    }

    /** Ensure numeric keys are zero-based and preserve named params. */
    private function normalizeParams(array $params): array
    {
        if ($params === []) {
            return $params;
        }
        // If positional numeric array, ensure zero-based
        $isNumeric = array_keys($params) === range(0, count($params) - 1);
        return $isNumeric ? array_values($params) : $params;
    }

    private function formatError(string $sql, array $params, PDOException $e): string
    {
        $shortSql = strlen($sql) > 400 ? substr($sql, 0, 400) . '…' : $sql;
        $shortParams = $this->shortParams($params);
        return 'MySQL QueryException: ' . $e->getMessage() . ' | SQL: ' . $shortSql . ' | Params: ' . $shortParams;
    }

    private function shortParams(array $params): string
    {
        $out = [];
        foreach ($params as $k => $v) {
            if (is_scalar($v) || $v === null) {
                $s = (string)$v;
                if (strlen($s) > 120) {
                    $s = substr($s, 0, 120) . '…';
                }
                $out[] = (is_int($k) ? "[$k]" : (string)$k) . '=' . $s;
            } else {
                $out[] = (is_int($k) ? "[$k]" : (string)$k) . '=<' . gettype($v) . '>';
            }
        }
        return '[' . implode(', ', $out) . ']';
    }
}
