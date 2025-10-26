<?php
#/web/index.php - This is the main launching point for the whole system. All HTML and API endpoints start here

/*


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
 Copyright (C) 2026, Blackrush LLC, All Rights Reserved
Created by Erik Olson, Tarpon Springs, Florida
For more information, visit BlackrushDrive.com

MIT License

Copyright (c) 2025 Erik Lee Olson for Blackrush, LLC

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.


*/

use Modules\Orm\Core\DB;
use Modules\Orm\Drivers\PdoMySqlDriver;
//use Modules\Orm\Drivers\PdoSqliteDriver;
# This thing does everything
use \App\Controller;
use App\Domain\DomainAutoload;

// Ensure session garbage collection and cookie lifetimes align
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.cookie_lifetime', 86400);

// Configure 24-hour session lifetime
$host = $_SERVER['HTTP_HOST'];
$domain = explode(':', $host)[0];

// Check if we're in local development mode (set by router.php)
$isLocalDev = isset($_SERVER['YORE_LOCAL_DEV']) && $_SERVER['YORE_LOCAL_DEV'] === 'true';

// For local dev, use localhost as cookie domain, otherwise use the actual domain
$cookieDomain = $isLocalDev ? 'localhost' : $domain;

// Check if we're on HTTPS
$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || $_SERVER['SERVER_PORT'] == 443
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');



// Apply cookie params for this request (must be before session_start)
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => $cookieDomain,
    'secure' => $isSecure,  // Only require HTTPS when actually using HTTPS
    'httponly' => true,
    'samesite' => $isSecure ? 'None' : 'Lax', // Use 'Lax' for HTTP, 'None' for HTTPS
]);

session_start();
# Show me all my mitsakes
error_reporting(E_ALL);
ini_set('display_errors', '1');
# I'll Be Right Back (disable the hole tire site)
$brb = false;

# This is where all Yore pages start
if ($brb) exit('<h1 style="text-align:center;width:100%;font-size:700%;font-family: tahoma, serif;margin: 10% 0 0 0;">BRB ...</h1>');









// Autoload function
spl_autoload_register(function ($class_name) {
    // Define the base directory for the "App" namespace
    $base_dir = __DIR__ . '/../app/';

    // Check if the class is within the "App" namespace
    $namespace = 'App\\';
    if (strncmp($namespace, $class_name, strlen($namespace)) === 0) {
        // Remove the namespace prefix
        $relative_class = substr($class_name, strlen($namespace));

        // Replace the namespace separator with the directory separator
        $file = $base_dir . str_replace('\\', DIRECTORY_SEPARATOR, $relative_class) . '.php';

        // If the file exists, require it
        if (file_exists($file)) {
            require $file;
        }
    }
});
# If we're gonna be using Evo Comm Tech
#use Olsonhost\Ect\Init;

# This gives us the power of many!!
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Register domain-scoped Models namespace to the domain Models directory (after autoloaders)
$domainPath = __DIR__ . '/../pages/_domains/' . $domain;
DomainAutoload::registerModels($domain, $domainPath);



# Setup the ORM database connections here
$host = getenv('DB_HOST') ?: 'localhost';
$port = (int)(getenv('DB_PORT') ?: 3306);
$name = getenv('DB_NAME') ?: 'yore';
$user = getenv('DB_USER') ?: 'heidi';
$pass = getenv('DB_PASS') ?: 'Mermaid7!!';

if (true) { // ($mode === 'mysql' || ($host && $name)) {
    $dsn = 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $name . ';charset=utf8mb4';
    $pdo = new PDO($dsn, (string)$user, (string)$pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    DB::register('default', new PdoMySqlDriver($pdo), true);

}

// Now create the controller
new App\Controller();



# Now do everything!!
$C = new Controller;

# And show us what we did
echo $C->page;

# Here's some trash I won't pick up
// This is how we would instantiate ECT (which instantiates Twilio)
//$ECT = new Init();
// This invokes the Twilio webhook which invokes $this((Twilio))->ect->test(); which outputs *** TEST!!! ***
//$ECT->whtest();

//$phat = new Fred;

//$output = "aaaaaaaaaaa @blackrush() aaaaaaaaaaaaaaa";

//$output = $phat->view($output, [], []);

//echo $output;

//exit('<img style="width:72px" src="/images/spronzer.png"><br/>Halo Welt');

if(isset($_GET['debug'])) {
    $C->settings = "Not Shown";
    $C->modules['Mail']->settings = "Not Shown";
    $C->modules['Users']->settings = "Not Shown";
    dd($C);
}

function dd($var) {

    echo "<textarea style='position:relative;width:100%;min-height:200px;color:yellow;background-color:black;font-size:80%;bottom:0;'>";
    if (gettype($var) == 'string') echo $var; else var_dump($var);
     // dump the calls stack now:

    $calls = debug_backtrace();
    foreach ($calls as $call) {
        echo "\n" . $call['file'] . ':' . $call['line'] . ' ' . $call['function'] . "\n";
    }

    echo "</textarea><br/><br/><br/><br/><br/><br/><br/><br/><br/>";

    exit;
}
