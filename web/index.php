<?php
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
Copyright (C) 2024, Blackrush LLC, All Rights Reserved
Created by Erik Olson, Tarpon Springs, Florida
For more information, visit BlackrushDrive.com

/web/index.php - This is the main launching point for the whole system. All HTML and API endpoints start here

*/

# I'll Be Right Back (disable the hole tire site)
$brb = false;

# This is where all Yore pages start
if ($brb) exit('<h1 style="text-align:center;width:100%;font-size:700%;font-family: tahoma, serif;margin: 10% 0 0 0;">BRB ...</h1>');

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

if(isset($_GET['debug'])) dd($C);

function dd($var) {

    echo "<textarea style='position:relative;width:100%;min-height:200px;color:yellow;background-color:black;font-size:50%;bottom:0;'>";
    if (gettype($var) == 'string') echo $var; else echo json_encode($var,JSON_PRETTY_PRINT);
    echo "</textarea>";

    exit;
}


