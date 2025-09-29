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

/app/Library.php - This is the Controller Parent class, all controllers rendering pages or API data must extend this

*/

##       #### ########  ########     ###    ########  ##    ##
##        ##  ##     ## ##     ##   ## ##   ##     ##  ##  ##
##        ##  ##     ## ##     ##  ##   ##  ##     ##   ####
##        ##  ########  ########  ##     ## ########     ##
##        ##  ##     ## ##   ##   ######### ##   ##      ##
##        ##  ##     ## ##    ##  ##     ## ##    ##     ##
######## #### ########  ##     ## ##     ## ##     ##    ##

namespace App;

use App\BladeRenderer;
use App\Fred\Fred;
use App\Fred\MarkdownExtra;
use App\TenantResolver;
use Illuminate\Container\Container;

/**
 *
 */
class Library
{
    /**
     * @var
     */
    public $domain, $settings;

    public $params, $site, $name, $arg1, $arg2, $arg3, $api = false, $is_debug = false, $is_remote = false, $is_module=false;

    public $error_code, $error_message, $error_details;

    public $modules;

    public $blade;

    public $modules_array;

    public $flash;

    public $users=null, $database=null, $logging=null, $mail=null; // These are shortcuts from the $modules array for convenience


##     ## ######## #### ##       #### ######## ##    ##
##     ##    ##     ##  ##        ##     ##     ##  ##
##     ##    ##     ##  ##        ##     ##      ####
##     ##    ##     ##  ##        ##     ##       ##
##     ##    ##     ##  ##        ##     ##       ##
##     ##    ##     ##  ##        ##     ##       ##
 #######     ##    #### ######## ####    ##       ##

    /**
     * Check if the application is running in a development environment.
     * Best practice is to set an 'APP_ENV' environment variable to 'development'.
     * @return bool
     */
    public static function isDevelopmentMode(): bool
    {
        // 1. Check Environment Variable (Best Practice)
        $appEnv = getenv('APP_ENV');
        if ($appEnv === 'development') {
            return true;
        }

        // 2. Fallback to Hostname for simple local setups
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (str_starts_with($host, 'localhost') || str_ends_with($host, '.local')) {
            return true;
        }

        return false;
    }



#### ##    ## #### ########
 ##  ###   ##  ##     ##
 ##  ####  ##  ##     ##
 ##  ## ## ##  ##     ##
 ##  ##  ####  ##     ##
 ##  ##   ###  ##     ##
#### ##    ## ####    ##

// Get public controller properties https://domain/site/name/arg1/arg2/arg3
    /**
     * @return void
     */
    public function init() {

        $uri = $_SERVER['REQUEST_URI'] ?? '';

        // Remove any GET parameters that might be in the REQUEST_URI
        $uri = explode('?', $uri);

        $this->params = explode('/',
            strtolower(
                trim(
                    str_replace(
                        [' ','-','.'],
                        '_',
                        trim($uri[0], '/')
                    )
                )
            )
        );


        // This determines that the whole URI is an API call
        if ( ($this->params[0] ?? null) == 'api') {
            $this->api = true;
            array_shift($this->params);
        }

        // This determines that the whole URI is a Module function
        if ( ($this->params[0] ?? null) == 'module') {
            $this->is_module = true;
            array_shift($this->params);
        }


        // This determines that the whole URI is a remote domain call*
        if ( ($this->params[0] ?? null) == 'remote') {
            /*
             *  Instead of "remote", we could have some kind of notation like @olsonhost-mydomain to indicate that
             *  all of the page JSON and view longtexts should come from a remote data source like Github or some
             *  other web resource rather than picking these things up from the local disk (or local database, like
             *  we are intending to eventually do)
             */
            $this->is_remote = true;
            array_shift($this->params);
        }


        $this->domain = TenantResolver::current();
        $this->site = !empty($this->params[0] ?? null) ? $this->params[0] : 'default';
        $this->name = !empty($this->params[1] ?? null) ? $this->params[1] : 'home';
        $this->arg1 = $this->params[2] ?? false;
        $this->arg2 = $this->params[3] ?? false;
        $this->arg3 = $this->params[4] ?? false;

        // Use the centralized development mode detection
        $this->is_debug = self::isDevelopmentMode();

        $modules_temp = '../pages/_domains/' . $this->domain . '/modules.json';
        if(file_exists($modules_temp)) {
            $this->modules_array = json_decode(file_get_contents($modules_temp));
            $this->is_debug = $this->modules_array->debug ?? false;
        }

        $env_temp = '../pages/_domains/' . $this->domain . '/env.json';
        if(file_exists($env_temp)) {
            $this->settings = json_decode(file_get_contents($env_temp), true);
        } else $this->settings = [];

        if ($this->is_debug) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }
        // Register all plugins by instantiating each

        $modulesDir = __DIR__ . '/../modules/';
        $this->modules = [];

        if ($this->api) {
            $SPACE_NAME = "Module"; // This will change to "Api" if we decide to separate Web and API module classes 👁👁
        } else {
            $SPACE_NAME = "Module"; // But for now we are using "Module" for both Web and API 👁👁
        }

        if (is_dir($modulesDir)) {
            $subdirectories = scandir($modulesDir);

            foreach ($subdirectories as $subdir) {
                // Skip the current and parent directory links
                if ($subdir === '.' || $subdir === '..') {
                    continue;
                }

                if(in_array($subdir, $this->modules_array->exclude ?? [])) {
                    continue;
                }

                $subdirPath = $modulesDir . $subdir;

                // Check if the path is a directory
                if (is_dir($subdirPath)) {

                    $filePath = $subdirPath . "/$SPACE_NAME.php"; // Literally "/modules/Hello/Module.php" and someday "/modules/Hello/Api.php" 👁��

                    // Check if the "Module.php" file exists in the subdirectory, if not then leave it alone
                    // TODO: Maybe support a Module-dev.php version for debug mode, etc.. Module-noauth, Module-admin, 😊
                    if (file_exists($filePath)) {
                        // Include the file
                        include_once $filePath;

                        // Define the expected namespace and class
                        $namespace = "Modules\\" . $subdir;
                        $className = $namespace . "\\$SPACE_NAME"; // Literally "Modules\Hello\Module" and someday "Modules\Hello\Api" 👁👁

                        // Check if the class exists in the namespace
                        if (class_exists($className)) {
                            // Instantiate the Main class and add it to the $modules array
                            $this->modules[$subdir] = new $className();
                            // 👁👁 Here is something we can do: Instead of
                            //          $this->modules[$subdir] = new $className();
                            // we could do
                            //          $this->modules[$subdir] = $className;
                            // Then, later if we ever want to use this module and we find that $this->modules[$subdir] is
                            // not an object, that's when we instantiate it.  I'm not saying do this now, but this is
                            // a way that we could load module classes on demand and not all at load time. Also, we are
                            // going to need a way to differentiate between modules we load when the user is logged in vs
                            // not logged in, and also a way to enable and disable modules so some don't load at all but
                            // we can still have them there on disk. When we do this, we could also determine that a module
                            // is instantiated at load time vs on demand

                            // But for now, we're just instantiating them all and performance be durnnnndt



                        } else {
                            echo "Class 'Module' not found in namespace: " . $namespace . PHP_EOL;
                        }
                    }
                }
            }

        } else {
            $this->abort(400,"The modules directory does not exist");
        }

        /*

        Me: You and me are going to be best friends
        ChatGPT: I'm honored to hear that! Let's make some awesome things together! 😊

        (So, just pointing out, we're here now.)

         */

        foreach ($this->modules as $key => $module) {
            // TODO: Log this or something, and throw an error if the init fails too
            //echo "Module: $key, Message: " . $module->yore_module_init($this, $key) . PHP_EOL;
            if (method_exists($module, 'yore_module_init'))
                $module->yore_module_init($this, $key);

           // These are provided for code readability. You can use these classes later, referenced in $controller
           // without having to use the $controller->modules array to find the one you want
           switch ($key) {
               case 'Users':    $this->users    = $module; break; // i.e. $controller->users
               case 'Database': $this->database = $module; break;
               case 'Logging':  $this->logging  = $module; break;
               case 'Mail':  $this->mail  = $module; break;
           }
       }

        foreach ($this->modules as $key => $module) {
            // Check if a form field $key_post (value) exists and call it
            $key = strtolower($key);
            if (isset($_REQUEST[$key . '_post'])) {
                if (method_exists($module, 'yore_module_post'))
                    $module->yore_module_post($this, $_REQUEST[$key . '_post']);
            }
        }

        foreach ($this->modules as $key => $module) {

            if (method_exists($module, 'yore_module_post_init'))
                $module->yore_module_post_init();

        }



        $cache = __DIR__ . '/../storage/cache/views';
        // make sure the cache dir exists and is writable
        if (!is_dir($cache)) {
            @mkdir($cache, 0775, true);
        }
        if (!is_writable($cache)) {
            $this->abort(500, "The cache directory is not writable: " . $cache);
        }

        $viewDir = '/var/www/yore/pages/_domains/' . $this->domain . '/' . $this->site . '/views/';

        $globals = [
            'appName' => 'Yore',
            'env'     => getenv('APP_ENV') ?: 'development',
            'controller' => $this
        ];


        if (!Container::getInstance()) {
            Container::setInstance(new Container());
        }


        $this->blade = new BladeRenderer($viewDir, $cache, $globals);

    }


##     ## #### ######## ##      ##
##     ##  ##  ##       ##  ##  ##
##     ##  ##  ##       ##  ##  ##
##     ##  ##  ######   ##  ##  ##
 ##   ##   ##  ##       ##  ##  ##
  ## ##    ##  ##       ##  ##  ##
   ###    #### ########  ###  ###


    /**
     * @param $output
     * @param $data
     * @return mixed|string
     */
    public function view($output, $data = []) {

        // If we want to keep F as separate project, send these vars to phat object rather than setting them here

        // Not sure cuz we may want these (i.e. for the edit button) for header, footer, multiple views, nav bar, whtvr

        $vars = get_defined_vars();

        // the main reason for doing this right now is so Fred knows what #view is, to use in @edit(#view)
        foreach ($vars as $var => $val) {
            if (gettype($val) == 'string')
                $output = str_replace('#' . $var, $val, $output);
        }

        // Render the output using the Markdown viewer if the page permits

//        if ($this->data->markdown ?? false) {
//            $output = MarkdownExtra::defaultTransform($output);
//        }

        // Render the output using the Fred viewer
        $fred = new Fred;

        $output = $fred->view($output, $data, $this->modules);

        foreach ($this->modules as $key => $module) {
            if (method_exists($module, 'yore_output'))
                $module->yore_output($this, $output);

        }

        return $output;

    }

    function render(string $name, array $data = []): string
    {
        /** @var \App\BladeRenderer $renderer */
        return $this->blade->render($name, $data);
    }


########  ########  ######  ##     ## ##       ########
##     ## ##       ##    ## ##     ## ##          ##
##     ## ##       ##       ##     ## ##          ##
########  ######    ######  ##     ## ##          ##
##   ##   ##             ## ##     ## ##          ##
##    ##  ##       ##    ## ##     ## ##          ##
##     ## ########  ######   #######  ########    ##
    /**
     * @param $returnResult
     * @return mixed
     */
    function result($returnResult) {
        if ($returnResult) {
            return $returnResult;
        } else {
            $this->error_details = debug_backtrace();
            return $returnResult;
        }
    }


   ###    ########   #######  ########  ########
  ## ##   ##     ## ##     ## ##     ##    ##
 ##   ##  ##     ## ##     ## ##     ##    ##
##     ## ########  ##     ## ########     ##
######### ##     ## ##     ## ##   ##      ##
##     ## ##     ## ##     ## ##    ##     ##
##     ## ########   #######  ##     ##    ##
// Abort the current process with a detailed (or not) managed error page

    /**
     * @param $code
     * @param $message
     * @param $details
     * @return void
     */
    public function abort($code=500, $message='An error has occurred', $details = 'No details')
    {

        // Todo: Use a(n optional) template for this

        $code = $this->error_code ?? $code;
        $message = $this->error_message ?? $message;
        $details = $this->error_details ?? $details;

        http_response_code($code);

        $details = print_r($details, true);

        $stack = $this->getCallStackAsString();

        if (($this->name == 'home') and ($this->site == 'default')) {
            $this->is_debug = true;

        }

        if ($code == 401) {
            // Redirect to root page with message
            $this->home("You must be logged in to view that page.");
        }

        if ($code == 404) {
            if ($this->is_debug) {
                $message = "<pre>$message</pre>";
                $message .= "<hr><h1>Attention</h1>The page you requested, <b><u><i>{$this->name}</i></u></b>, could not be found 
                under the site slug <b><u><i>{$this->site}</i></u></b> for domain <b><u><i>{$this->domain}</i></u></b>.
                <br/><br/>
                Would you like to create a page at this URL? <br/><br/>
                
                <a href='/api/debug/create/page/{$this->site}/{$this->name}' class='btn btn-primary'>Create Page</a>
                <a href='/' class='btn btn-secondary'>Go Home</a>
                <br/><br/>
                If you believe this is an error, please contact the site administrator.
                <br/><hr><br/>
                
                ";
            } else {
                $message = "The page you requested, <b><u><i>{$this->name}</i></u></b>, could not be found 
                under the site slug <b><u><i>{$this->site}</i></u></b> for domain <b><u><i>{$this->domain}</i></u></b>.
                Please check the URL and try again, 
                or contact the site administrator if you believe this is an error.";
            }
        }

        $exitString = <<< EOT
<html>
<head>
<title>Error $code - $message</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- Include bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">

<style>
    body { font-family: Arial, sans-serif; margin: 20px; padding: 20px; background-color: #f4f4f4; color: #333; }
    h1 { color: #d9534f; }
    pre { padding: 10px; border-radius: 5px; overflow-x: auto; }
    ul { list-style-type: none; padding: 0; }
    li { margin-bottom: 5px; }
</style>
</head>
<body>

<h1>$code <small><small> - That's an error :/</small></small></h1>
<p>$message</p>
<ul>
    <li>Domain: {$this->domain}</li>
    <li>Site: {$this->site}</li>
    <li>Page Name: {$this->name}</li>
    <li>Arg1: {$this->arg1}</li>
    <li>Arg2: {$this->arg2}</li>
    <li>Arg3: {$this->arg3}</li>
</ul>
<pre style='width:100%;color:#ffaa55;background-color:black;'>$details</pre>
<pre style='width:100%;color:#ffaa55;background-color:black;'>$stack</pre>
</body>
</html>
EOT;

        exit($exitString);
    }

    /**
     * @param $msg
     * @return void
     *
     * Go back to referring page with a message
     */
    public function back($msg = '', $data = false) {

        $page = $_SERVER['HTTP_REFERER'] ?? "/";
        $_SESSION['redirect_msg'] = $msg;
        $_SESSION['redirect_data'] = $data;
        header('Location: ' . $page);
        exit();

    }

    /**
     * @param $msg
     * @return void
     *
     * Display the current page with a message
     */
    public function flash($msg) {
        $this->flash = $msg;
    }

    /**
     * @param $msg
     * @return void
     *
     * Display the current page with a message
     */
    public function success($msg = '', $data = false) {
        $_SESSION['redirect_msg'] = $msg;
        $_SESSION['redirect_data'] = $data;
    }

    /**
     * @param $msg
     * @return void
     *
     * Go back to referring page with a message
     */
    public function home($msg = '', $data = false) {

        $page = "/";
        $_SESSION['redirect_success'] = $msg;
        $_SESSION['redirect_data'] = $data;
        header('Location: ' . $page);
        exit();

    }

    function getCallStackAsString(): string
    {
        $stack = debug_backtrace();
        $output = "";

        foreach ($stack as $index => $frame) {
            $output .= "Stack level: $index\n";
            $output .= "File: " . ($frame['file'] ?? '[internal]') . "\n";
            $output .= "Line: " . ($frame['line'] ?? '[internal]') . "\n";
            $output .= "Function: " . $frame['function'] . "\n";
            $output .= "Args: " . json_encode($frame['args']) . "\n\n";
        }

        return $output;
    }

    public function hasRole($roles) {

        $r = $_SESSION['role'] ?? false;

        if ($r and in_array($r, $roles)) {
            return true;
        }

        return false;

    }

    public function request($parameter) {
        return $_REQUEST[$parameter] ?? false;
    }

    function cleanPhoneNumber($input) {
        // Remove all non-numeric characters
        $digits = preg_replace('/\D/', '', $input);

        // Remove the first character if it's a "1"
        if (substr($digits, 0, 1) === '1') {
            $digits = substr($digits, 1);
        }

        return $digits;
    }

    function encodeAll($str) {
        $hex = unpack('H*', $str);
        return preg_replace('~..~', '%$0', strtoupper($hex[1]));
    }

    function cleanTextFile($filename)
    {
//        // If the file extension is pgp or gpg then call a function to decrypt it
//        if (strpos($filename, '.pgp') !== false || strpos($filename, '.gpg') !== false) {
//            echo ("\nDecrypting $filename...\n");
//            // If a method exists in the current class named "decryptFile" then call it
//            if (method_exists($this, 'decryptFile')) {
//                $filename = $this->decryptFile($filename);
//            } else {
//                throw new \Exception("File is encrypted but no decryptFile method exists in class " . get_class($this));
//            }
//
//        }

        if (!file_exists($filename) || !is_readable($filename)) {
            throw new Exception("File does not exist or is not readable: $filename");
        }

        $contents = file_get_contents($filename);
        if ($contents === false) {
            throw new Exception("Failed to read the file: $filename");
        }

        // Remove high ASCII chars (>127) and control chars except CR (13) and LF (10)
        $cleaned = '';
        $length = strlen($contents);

        for ($i = 0; $i < $length; $i++) {
            $ascii = ord($contents[$i]);
            if (
                ($ascii === 10 || $ascii === 13) || // Allow LF and CR
                ($ascii >= 32 && $ascii <= 127)     // Allow standard printable ASCII
            ) {
                $cleaned .= $contents[$i];
            }
        }

        if (file_put_contents($filename, $cleaned) === false) {
            throw new Exception("Failed to write cleaned content to the file: $filename");
        }

        return $filename;
    }
}

