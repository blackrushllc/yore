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

use App\Fred\Fred;
use App\Fred\MarkdownExtra;

/**
 *
 */
class Library
{

    /**
     * @var
     */
    public $domain;

    public $params, $site, $name, $arg1, $arg2, $arg3, $api = false, $is_debug = false, $is_remote = false, $is_module=false;

    public $error_code, $error_message, $error_details;

    public $modules;

    public $users=null, $database=null, $logging=null, $mail=null; // These are shortcuts from the $modules array for convenience


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


        $this->domain = $_SERVER['SERVER_NAME'] ?? 'local';
        $this->site = !empty($this->params[0] ?? null) ? $this->params[0] : 'default';
        $this->name = !empty($this->params[1] ?? null) ? $this->params[1] : 'home';
        $this->arg1 = $this->params[2] ?? false;
        $this->arg2 = $this->params[3] ?? false;
        $this->arg3 = $this->params[4] ?? false;

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

                $subdirPath = $modulesDir . $subdir;

                // Check if the path is a directory
                if (is_dir($subdirPath)) {

                    $filePath = $subdirPath . "/$SPACE_NAME.php"; // Literally "/modules/Hello/Module.php" and someday "/modules/Hello/Api.php" 👁👁

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

        http_response_code($this->error_code);

        $details = print_r($details, true);

        $exitString = "
            <html>
            <body>
            <h1>$code</h1>
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
            </body>
            </html>
            ";

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
     * Go back to referring page with a message
     */
    public function home($msg = '', $data = false) {

        $page = "/";
        $_SESSION['redirect_success'] = $msg;
        $_SESSION['redirect_data'] = $data;
        header('Location: ' . $page);
        exit();

    }
}