<?php
declare(strict_types=1);

namespace Modules\Orm\Support;

use Modules\Orm\Core\QueryBuilder;

final class Paginator
{
    public static function paginate(QueryBuilder $qb, int $perPage = 15, int $page = 1): Page
    {
        if ($perPage < 1) { $perPage = 1; }
        if ($page < 1) { $page = 1; }

        $total = $qb->count();
        $lastPage = (int)max(1, (int)ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $items = $qb->limit($perPage)->offset($offset)->get()->all();

        return new Page($items, $total, $perPage, $page, $lastPage);
    }
}
