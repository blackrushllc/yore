<?php
namespace Modules\Debug;

# This is the module code for the Debug module

/*

# Debug
Turning this module on adds lots of debugging features to Yore
+ Adds a debug element to every page with useful information
+ Sets the $this->debug flag to true in the controller
+ Turns on all of the possible Php debugging things
+ Logs a bunch of extra shit
+ Puts some kind of obvious warning on the UI that debugging is on with a quick way to turn it off
+ Relaxes security, maybe adds the ability to log in as users, create temporary users
+ Override notifications or irreversable machinations like issuing credentials or charging fees, alerts etc
+ Enables Asserts or tests and things
+ Allows for an alternate Sandbox data source, storage, logger and other resources if they exist
+ Lets module creators do things like disable context menu override, timeouts, show quiz answers, etc

 */

use App\Controller;
use App\Modules;

/**
 *
 */
class Module extends Modules {

    /**
     *
     */
    public function __construct() {
        // Constructor is optional
        //echo "Hello World! Main class instantiated." . PHP_EOL;

        // Constructor in Parent does nothing right now, but all modules should call it anyway
        parent::__construct();
    }

    /**
     * @return int
     */
    public function fred_isdebug() {
        return 1;
    }

    // Optional method to modify output before it gets rendered, after it gets Phatted

    /**
     * @param $controller
     * @param $output
     * @return void
     */
    public function yore_output($controller, &$output) {
        //if ($controller->is_debug) { // TODO: Use the Debug module for stuff like this
            $str_data = json_encode($controller->data, JSON_PRETTY_PRINT);
            $uri = $_SERVER['REQUEST_URI'];
            $output .= // make this a template
                "<button class='btn btn-danger btn-sm debug-info-button' onclick=\"$('.debug-info').toggle()\">Debug</button><br/>
<textarea class='debug-info' style='width:100%; height:500px; display:none;'>
Domain: {$controller->domain}  Site: {$controller->site}   Page Name: {$controller->name}   Arg1: {$controller->arg1}   Arg2: {$controller->arg2}   Arg3: {$controller->arg3}

URI: $uri

Data: $str_data
                
Modules:
========
";
            foreach ($controller->modules as $name => $module) {
                $output .= $name . " = " . $module->moduleStatus . "\n";
            }


            $output .= "</textarea>";
        //}
    }
}