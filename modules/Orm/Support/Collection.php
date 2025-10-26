<?php
declare(strict_types=1);

namespace Modules\Orm\Support;

use ArrayAccess;
use IteratorAggregate;
use Traversable;

/**
 * Tiny collection helper used by the ORM.
 */
class Collection implements ArrayAccess, IteratorAggregate
{
    /** @param array<int,array<string,mixed>> $items */
    public function __construct(private array $items = []) {}

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function offsetExists(mixed $offset): bool { return isset($this->items[$offset]); }
    public function offsetGet(mixed $offset): mixed { return $this->items[$offset] ?? null; }
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) { $this->items[] = $value; } else { $this->items[$offset] = $value; }
    }
    public function offsetUnset(mixed $offset): void { unset($this->items[$offset]); }

    /** @return array<int,array<string,mixed>> */
    public function all(): array { return $this->items; }

    /**
     * @return array<int,mixed>
     */
    public function pluck(string $key): array
    {
        $out = [];
        foreach ($this->items as $row) {
            if (is_array($row) && array_key_exists($key, $row)) {
                $out[] = $row[$key];
            } elseif (is_object($row) && isset($row->{$key})) {
                $out[] = $row->{$key};
            }
        }
        return $out;
    }

    public function sortBy(string $key): self
    {
        $copy = $this->items;
        usort($copy, function ($a, $b) use ($key) {
            $av = is_array($a) ? ($a[$key] ?? null) : ($a->{$key} ?? null);
            $bv = is_array($b) ? ($b[$key] ?? null) : ($b->{$key} ?? null);
            return $av <=> $bv;
        });
        return new self($copy);
    }

    public function sortByDesc(string $key): self
    {
        $copy = $this->items;
        usort($copy, function ($a, $b) use ($key) {
            $av = is_array($a) ? ($a[$key] ?? null) : ($a->{$key} ?? null);
            $bv = is_array($b) ? ($b[$key] ?? null) : ($b->{$key} ?? null);
            return $bv <=> $av;
        });
        return new self($copy);
    }
}
