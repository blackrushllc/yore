<?php
declare(strict_types=1);

namespace Domain\AppYorewebCom\Models;

use Modules\Orm\Core\Model;

final class Orders extends Model
{
    protected static string $table = 'orders';
    protected static string $primaryKey = 'id';
    protected static array  $fillable = ['customer_id','number','status','total','created_at','updated_at'];

    // API endpoint to insert a row (example)
    public function api_store($controller, array $request)
    {
        // naive validation omitted for brevity
        $row = [
            'customer_id' => $request['customer_id'] ?? null,
            'number'      => $request['number']      ?? null,
            'status'      => $request['status']      ?? 'new',
            'total'       => $request['total']       ?? 0,
            'created_at'  => date('c'),
            'updated_at'  => date('c'),
        ];
        $order = static::store($row);
        return ['ok'=>true, 'id'=>$order->getKey()];
    }

    // Web endpoint to list orders with DataTables (uses raw $_REQUEST via indexFromRequest)
    public function web_index($controller, array $request)
    {
        //dd([$request]);

        return "<div class='container'><h1>Orders</h1><hr><p>Hello World!</p></div>";

        $payload = static::indexFromRequest([
            'columns'    => ['id','customer_id','number','status','total','created_at'],
            'searchable' => ['number','status'],
            'orderable'  => ['id','number','status','created_at'],
            'as'         => 'array',
            'maxLength'  => 1000,
        ]);

        // Extremely simple HTML render; in real pages you’d use a view file.
        $rows = $payload['data'];
        $lis  = array_map(fn($r) => "<li>#{$r['id']} {$r['number']} {$r['status']} {$r['total']}</li>", $rows);
        return "<div class='container'><h1>Orders</h1><ul>".implode('', $lis)."</ul></div>";
    }
}
