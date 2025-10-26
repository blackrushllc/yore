<?php
declare(strict_types=1);

use Modules\Orm\Core\DB;
use Modules\Orm\Core\Model;
use Modules\Orm\Drivers\PdoSqliteDriver;
use Modules\Orm\Schema\Schema;

require __DIR__ . '/../../../vendor/autoload.php';

// Register an in-memory SQLite connection for demo purposes
DB::register('default', new PdoSqliteDriver(new PDO('sqlite::memory:')), true);

// Define a simple model
class User extends Model {
    protected static string $table = 'orm_demo_users';
    protected static array $fillable = ['name', 'email'];
}

// Build demo schema
Schema::dropIfExists('orm_demo_users');
Schema::create('orm_demo_users', function ($t) {
    $t->increments('id');
    $t->string('name');
    $t->string('email');
    $t->unique('email');
    $t->timestamps();
});

// Seed
User::create(['name' => 'Alice', 'email' => 'alice@example.test']);
User::create(['name' => 'Bob', 'email' => 'bob@example.test']);
User::create(['name' => 'Carol', 'email' => 'carol@example.test']);

// Queries
$all = User::all()->all();
$names = User::pluck('name');
$count = User::query()->count();
$first = User::where('name', 'LIKE', 'A%')->first();

// Output
function printTable(array $rows): void {
    if (!$rows) { echo "(no rows)\n"; return; }
    $cols = array_keys((array)$rows[0]);
    echo implode("\t", $cols) . "\n";
    foreach ($rows as $r) {
        echo implode("\t", array_map(fn($v) => (string)$v, (array)$r)) . "\n";
    }
}

echo "All users:\n";
printTable(array_map(fn($m) => $m->toArray(), $all));

echo "\nNames: " . implode(', ', $names) . "\n";

echo "\nTotal: {$count}\n";

echo "\nFirst starting with 'A': " . ($first?->getAttribute('name') ?? '(none)') . "\n";
