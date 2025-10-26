# Yore ORM (Modules\\Orm)

A lightweight, Eloquent-style ORM for the Yore framework. It provides a small, safe, and portable API for basic CRUD, a fluent query builder, and simple schema helpers for MySQL and SQLite via PDO.

Key features:
- PDO-only drivers (MySQL-family and SQLite)
- Safe by default: prepared statements and bound parameters
- Minimal active record Model
- Fluent QueryBuilder for common operations
- Tiny Collection & Paginator helpers
- Portable Schema builder (CREATE TABLE, add column, single-column indexes)

This module implements the “Yore ORM – Definition v0.1”.

## Quick start

1) Install/enable the module in your domain's modules.json like other modules.

2) Configure connections (optional). The module looks for `modules/Orm/config/orm.php`:

```php
return [
  'default' => 'default',
  'connections' => [
    'default' => [
      'driver' => 'sqlite', // 'mysql' | 'sqlite'
      'dsn'    => 'sqlite::memory:',
      'user'   => null,
      'pass'   => null,
      'options'=> [],
    ],
  ],
];
```
If not provided, the module falls back to your per-tenant DB settings via controller settings and registers a default MySQL connection.

3) Define a model:

```php
use Modules\Orm\Core\Model;

class User extends Model {
  protected static string $table = 'users';
  protected static array $fillable = ['name', 'email'];
}
```

4) Create a table (SQLite example via in-memory DB):

```php
use Modules\Orm\Schema\Schema;
use Modules\Orm\Core\DB;
use Modules\Orm\Drivers\PdoSqliteDriver;

DB::register('default', new PdoSqliteDriver(new PDO('sqlite::memory:')), true);

Schema::create('users', function ($t) {
  $t->increments('id');
  $t->string('name');
  $t->string('email');
  $t->unique('email');
  $t->timestamps();
});
```

5) CRUD examples:

```php
// Create
$user = User::create(['name' => 'Alice', 'email' => 'a@example.test']);

// Read
$found = User::find($user->getKey());

// Update
$found->setAttribute('name', 'Alice Smith');
$found->save();

// Delete
$found->delete();
```

6) Query examples:

```php
use Modules\Orm\Core\QueryBuilder;

// Fluent filters
$rows = User::where('name', 'LIKE', 'Ali%')
  ->orderBy('id', 'desc')
  ->limit(10)
  ->get();

// Aggregates
$count = User::query()->count();
$maxId = User::query()->max('id');

// Pluck values
$names = User::pluck('name');
```

7) Pagination:

```php
use Modules\Orm\Support\Paginator;

$page = Paginator::paginate(User::query(), perPage: 15, page: 1);
```

8) Schema examples:

```php
use Modules\Orm\Schema\Schema;

Schema::dropIfExists('users');
Schema::create('users', function ($t) {
  $t->increments('id');
  $t->string('name');
  $t->string('email');
  $t->unique('email');
});

// Add a column later
Schema::table('users', function ($t) {
  $t->integer('age', unsigned: false, nullable: true);
});
```

## Portability notes
- SQLite maps: string => TEXT, boolean => INTEGER, timestamp => TEXT.
- CREATE TABLE and single-column indexes are supported in the blueprint.
- ALTER support is limited to ADD COLUMN; for anything else, run raw SQL via `DB::connection()->statement(...)`.

## Safety & limitations
- No relations or soft-deletes in v0.1.
- Always uses prepared statements — never interpolate parameters in SQL.
- Public APIs are stable; internals may evolve.

## Demo
See `modules/Orm/examples/demo_usage.php` for a self-contained demo using SQLite in-memory. Optionally wire a CLI command to run it (e.g. `orm:demo`).
