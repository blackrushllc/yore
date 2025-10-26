Yore - A Framework for Applications
```
▄▄▄▄    ██▓    ▄▄▄       ▄████▄   ██ ▄█▀ ██▀███   █    ██   ██████  ██░ ██
▓█████▄ ▓██▒   ▒████▄    ▒██▀ ▀█   ██▄█▒ ▓██ ▒ ██▒ ██  ▓██▒▒██    ▒ ▓██░ ██▒
▒██▒ ▄██▒██░   ▒██  ▀█▄  ▒▓█    ▄ ▓███▄░ ▓██ ░▄█ ▒▓██  ▒██░░ ▓██▄   ▒██▀▀██░
▒██░█▀  ▒██░   ░██▄▄▄▄██ ▒▓▓▄ ▄██▒▓██ █▄ ▒██▀▀█▄  ▓▓█  ░██░  ▒   ██▒░▓█ ░██
░▓█  ▀█▓░██████▒▓█   ▓██▒▒ ▓███▀ ░▒██▒ █▄░██▓ ▒██▒▒▒█████▓ ▒██████▒▒░▓█▒░██▓
░▒▓███▀▒░ ▒░▓  ░▒▒   ▓▒█░░ ░▒ ▒  ░▒ ▒▒ ▓▒░ ▒▓ ░▒▓░░▒▓▒ ▒ ▒ ▒ ▒▓▒ ▒ ░ ▒ ░░▒░▒
▒░▒   ░ ░ ░ ▒  ░ ▒   ▒▒ ░  ░  ▒   ░ ░▒ ▒░  ░▒ ░ ▒░░░▒░ ░ ░ ░ ░▒  ░ ░ ▒ ░▒░ ░
░    ░   ░ ░    ░   ▒   ░        ░ ░░ ░   ░░   ░  ░░░ ░ ░ ░  ░  ░   ░  ░░ ░
░          ░  ░     ░  ░░ ░      ░  ░      ░        ░           ░   ░  ░  ░
░                  ░

```

```
Copyright (C) 2025, Blackrush LLC, All Rights Reserved
Created by Erik Olson, Tarpon Springs, Florida
For more information, visit BlackrushDrive.com
```

# Models

Yore ships with a small, pragmatic Active Record–style ORM. To define a model:

- Create a PHP class under your domain Models folder:
  pages/_domains/{domain}/Models
- Namespace follows Domain\\{StudlyDomain}\\Models.
- Extend Modules\\Orm\\Core\\Model and declare table, primaryKey, and fillable fields.

Example:

```php
<?php
namespace Domain\AppYorewebCom\Models;
use Modules\Orm\Core\Model;

final class Orders extends Model
{
    protected static string $table = 'orders';
    protected static string $primaryKey = 'id';
    protected static array  $fillable = ['customer_id','number','status','total','created_at','updated_at'];
}
```

Common usage:

```php
// Create/store
$order = Orders::store([
    'customer_id' => 1,
    'number' => 'INV-123',
    'status' => 'new',
    'total' => 9.99,
]);

// Find helpers
$first = Orders::firstOrNew(['number' => 'INV-123']);
$real  = Orders::firstOrCreate(['number' => 'INV-124'], ['status' => 'new']);

// Update or create
$up = Orders::updateOrCreate(['number' => 'INV-125'], ['status' => 'paid']);

// Bulk ops
Orders::updateWhere(['status' => 'archived'], ['status' => 'paid']);
Orders::insertMany([
  ['customer_id'=>1,'number'=>'INV-200','status'=>'new','total'=>1],
  ['customer_id'=>2,'number'=>'INV-201','status'=>'new','total'=>2],
]);

// Simple querying
$paid = Orders::where('status', '=', 'paid')->orderBy('created_at', 'desc')->get();
$any  = Orders::findBy('number', 'INV-123');

// Instance helpers
$any?->updateAttributes(['status' => 'shipped']);
$any?->refresh();
```

DataTables index (server-side):

```php
// As simple map
$payload = Orders::index(['find' => 'INV'], [
  'columns' => ['id','number','status','total','created_at'],
  'searchable' => ['number','status'],
  'orderable' => ['id','number','created_at'],
]);

// Directly from $_REQUEST (ideal for DataTables ajax)
$payload = Orders::indexFromRequest([
  'columns' => ['id','number','status','total','created_at'],
  'searchable' => ['number','status'],
  'orderable' => ['id','number','created_at'],
]);
```

For working examples, visit: https://app.yoreweb.com

# Controllers

Domain pages can have an optional custom Controller: pages/_domains/{domain}/{site}/Controller.php

Define routeable methods named with prefixes:
- web_{action}: returns HTML (string). It is wrapped in the domain theme.
- api_{action}: returns raw data (arrays/objects JSON-encoded automatically).

Signatures are flexible; simplest form receives slug args. Example:

```php
<?php
class Controller
{
    // Initialize with the core controller if you need access to modules, settings, etc.
    public function yore_controller_init($core) { $this->core = $core; }

    public function web_index(): string
    {
        // Render basic HTML or use Blade views in /views
        return '<div class="container"><h1>Welcome</h1></div>';
    }

    public function api_status(): array
    {
        return ['ok' => true, 'time' => date('c')];
    }
}
```

Routing:
- A normal page URL like /{site}/{name} maps to web_{name} if present; otherwise the standard page JSON/view pipeline runs.
- For API-style responses within a site, define api_{name} and call it via your own XHR/fetch against the same page path (Yore will detect api_* when invoked by custom controller dispatch). For full details, see docs/ROUTING.md.

For working examples, visit: https://app.yoreweb.com

# Modeltrollers

A “Modeltroller” is a Model class that can also act like a controller by exposing routable methods. Define methods on the model with these prefixes:
- web_*: returns HTML body, themed like any page.
- api_*: returns raw output (arrays/objects are JSON-encoded).

URL pattern for routable models:
/{site}/models/{Model}/{action}[/{arg1}/{arg2}/{arg3}]

Resolution rules (summary):
- Namespace: Domain\\{StudlyDomain}\\Models\\{StudlyModel}
- File path: /pages/_domains/{domain}/Models/{StudlyModel}.php
- Router prefers web_* for /…/models/…; if missing, api_* is used.
- Method receives ($controller, $_REQUEST, ...$extraArgs)

Example (excerpt from the demo Orders model):

```php
final class Orders extends Model
{
    // ... table/keys/fillable

    public function api_store($controller, array $request): array
    {
        $order = static::store([
            'customer_id' => $request['customer_id'] ?? null,
            'number'      => $request['number']      ?? null,
            'status'      => $request['status']      ?? 'new',
            'total'       => $request['total']       ?? 0,
            'created_at'  => date('c'),
            'updated_at'  => date('c'),
        ]);
        return ['ok' => true, 'id' => $order->getKey()];
    }

    public function web_index($controller, array $request): string
    {
        $payload = static::indexFromRequest([
            'columns'    => ['id','customer_id','number','status','total','created_at'],
            'searchable' => ['number','status'],
            'orderable'  => ['id','number','status','created_at'],
        ]);
        $items = array_map(fn($r) => "<li>#{$r['id']} {$r['number']} {$r['status']}</li>", $payload['data']);
        return '<div class="container"><h1>Orders</h1><ul>' . implode('', $items) . '</ul></div>';
    }
}
```

Try these on the demo site:
- /customers/models/orders/store?customer_id=1&number=INV-123&total=9.99
- /customers/models/orders/index?search[value]=INV

More details in docs/MODELS.md and docs/ROUTING.md. Live examples: https://app.yoreweb.com

