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

# I'll Be Right Back (disable the hole tire site)
$brb = false;

# This is where all Yore pages start
if ($brb) exit('<h1 style="text-align:center;width:100%;font-size:700%;font-family: tahoma, serif;margin: 10% 0 0 0;">BRB ...</h1>');

// Set the session timeout to 1 hour (3600 seconds) * 48
ini_set('session.gc_maxlifetime', 3600 * 48);

$host = $_SERVER['HTTP_HOST'];
$domain = explode(':', $host)[0];


// Make sure the session cookie reflects the same lifetime
//session_set_cookie_params(3600 * 48);
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => $domain,
    'secure' => true,
    'httponly' => true,
    'samesite' => 'None', // Required for cross-site POSTs to include cookies
]);

// set session timeout to 24 hours
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.cookie_lifetime', 86400);
session_start();

# This thing does everything
use \App\Controller;

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
if (file_exists('../vendor/autoload.php')) {
    require '../vendor/autoload.php';
}


# Show me all my mitsakes
error_reporting(E_ALL);
ini_set('display_errors', '1');

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
    echo "</textarea><br/><br/><br/><br/><br/><br/><br/><br/><br/>";

    exit;
}


