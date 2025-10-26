<?php
declare(strict_types=1);

namespace Modules\Orm\Support;

final class Page
{
    /**
     * @param array<int,mixed> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $perPage,
        public int $currentPage,
        public int $lastPage,
    ) {}
}
