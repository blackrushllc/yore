<?php
declare(strict_types=1);

namespace Modules\Orm\Contracts;

interface DriverInterface
{
    /**
     * Execute a SELECT and return all rows as associative arrays.
     * @param string $sql
     * @param array<int|string,mixed> $params
     * @return array<int,array<string,mixed>>
     */
    public function select(string $sql, array $params = []): array;

    /** Execute an INSERT, returning the last-insert id if available. */
    public function insert(string $sql, array $params = []): ?string;

    /** Execute an UPDATE returning rows affected. */
    public function update(string $sql, array $params = []): int;

    /** Execute a DELETE returning rows affected. */
    public function delete(string $sql, array $params = []): int;

    /** Execute arbitrary DDL (CREATE/ALTER/DROP). Return true on success. */
    public function statement(string $sql, array $params = []): bool;

    // Transactions
    public function beginTransaction(): void;
    public function commit(): void;
    public function rollBack(): void;

    // Grammar helpers
    public function quoteIdentifier(string $name): string;

    // Introspection
    public function lastInsertId(): ?string;
    public function getPdo(): \PDO;
}
