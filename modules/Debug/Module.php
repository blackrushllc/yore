<?php
namespace Modules\Debug;

/*


 â–„â–„â–„â–„    â–ˆâ–ˆâ–“    â–„â–„â–„       â–„â–ˆâ–ˆâ–ˆâ–ˆâ–„   â–ˆâ–ˆ â–„â–ˆâ–€ â–ˆâ–ˆâ–€â–ˆâ–ˆâ–ˆ   â–ˆ    â–ˆâ–ˆ   â–ˆâ–ˆâ–ˆâ–ˆâ–ˆâ–ˆ  â–ˆâ–ˆâ–‘ â–ˆâ–ˆ
â–“â–ˆâ–ˆâ–ˆâ–ˆâ–ˆâ–„ â–“â–ˆâ–ˆâ–’   â–’â–ˆâ–ˆâ–ˆâ–ˆâ–„    â–’â–ˆâ–ˆâ–€ â–€â–ˆ   â–ˆâ–ˆâ–„â–ˆâ–’ â–“â–ˆâ–ˆ â–’ â–ˆâ–ˆâ–’ â–ˆâ–ˆ  â–“â–ˆâ–ˆâ–’â–’â–ˆâ–ˆ    â–’ â–“â–ˆâ–ˆâ–‘ â–ˆâ–ˆâ–’
â–’â–ˆâ–ˆâ–’ â–„â–ˆâ–ˆâ–’â–ˆâ–ˆâ–‘   â–’â–ˆâ–ˆ  â–€â–ˆâ–„  â–’â–“â–ˆ    â–„ â–“â–ˆâ–ˆâ–ˆâ–„â–‘ â–“â–ˆâ–ˆ â–‘â–„â–ˆ â–’â–“â–ˆâ–ˆ  â–’â–ˆâ–ˆâ–‘â–‘ â–“â–ˆâ–ˆâ–„   â–’â–ˆâ–ˆâ–€â–€â–ˆâ–ˆâ–‘
â–’â–ˆâ–ˆâ–‘â–ˆâ–€  â–’â–ˆâ–ˆâ–‘   â–‘â–ˆâ–ˆâ–„â–„â–„â–„â–ˆâ–ˆ â–’â–“â–“â–„ â–„â–ˆâ–ˆâ–’â–“â–ˆâ–ˆ â–ˆâ–„ â–’â–ˆâ–ˆâ–€â–€â–ˆâ–„  â–“â–“â–ˆ  â–‘â–ˆâ–ˆâ–‘  â–’   â–ˆâ–ˆâ–’â–‘â–“â–ˆ â–‘â–ˆâ–ˆ
â–‘â–“â–ˆ  â–€â–ˆâ–“â–‘â–ˆâ–ˆâ–ˆâ–ˆâ–ˆâ–ˆâ–’â–“â–ˆ   â–“â–ˆâ–ˆâ–’â–’ â–“â–ˆâ–ˆâ–ˆâ–€ â–‘â–’â–ˆâ–ˆâ–’ â–ˆâ–„â–‘â–ˆâ–ˆâ–“ â–’â–ˆâ–ˆâ–’â–’â–’â–ˆâ–ˆâ–ˆâ–ˆâ–ˆâ–“ â–’â–ˆâ–ˆâ–ˆâ–ˆâ–ˆâ–ˆâ–’â–’â–‘â–“â–ˆâ–’â–‘â–ˆâ–ˆâ–“
â–‘â–’â–“â–ˆâ–ˆâ–ˆâ–€â–’â–‘ â–’â–‘â–“  â–‘â–’â–’   â–“â–’â–ˆâ–‘â–‘ â–‘â–’ â–’  â–‘â–’ â–’â–’ â–“â–’â–‘ â–’â–“ â–‘â–’â–“â–‘â–‘â–’â–“â–’ â–’ â–’ â–’ â–’â–“â–’ â–’ â–‘ â–’ â–‘â–‘â–’â–‘â–’
â–’â–‘â–’   â–‘ â–‘ â–‘ â–’  â–‘ â–’   â–’â–’ â–‘  â–‘  â–’   â–‘ â–‘â–’ â–’â–‘  â–‘â–’ â–‘ â–’â–‘â–‘â–‘â–’â–‘ â–‘ â–‘ â–‘ â–‘â–’  â–‘ â–‘ â–’ â–‘â–’â–‘ â–‘
 â–‘    â–‘   â–‘ â–‘    â–‘   â–’   â–‘        â–‘ â–‘â–‘ â–‘   â–‘â–‘   â–‘  â–‘â–‘â–‘ â–‘ â–‘ â–‘  â–‘  â–‘   â–‘  â–‘â–‘ â–‘
 â–‘          â–‘  â–‘     â–‘  â–‘â–‘ â–‘      â–‘  â–‘      â–‘        â–‘           â–‘   â–‘  â–‘  â–‘
      â–‘                  â–‘
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
        $this->dir = __DIR__;
        parent::__construct();
    }

    // Create a function that can be called as an api endpoint. For example, this is /api/debug/create
    // Notice that the slugs "debug" and "create" are lower case
    /**
     * @param $controller
     * @param $method
     * @return string
     */
    public function api_create($controller, $method='GET') {

        // Notice that you have the $controller object here as a parameter, which basically gives you everything
        //  including all the modules which is important because you will want modules to interoperate,
        //  especially modules like Users and Database and Logging

        // FYI Whatever you return will JSON encoded
        //return "Hello World!";

        exit("<h1>This part's not done yet :(</h1>
            <img src='/images/yore1.png' style='width:300px;' alt='Yore - A web framework by Blackrush' />
            <img src='/images/underconstruction.png' style='width:300px;' alt='Yore -Under Construction' />
            
            ");

    }


    public function yore_navbar($where = 'top-right') {
        $controller = $this->controller;
        $domain = 'app.' . $controller->data->domain;
        $domain = str_replace('app.app.', 'app.', $domain); // err
        $site = $controller->data->section;
        $page = $controller->data->pagekey;
        $view = $controller->data->view ?? 'null?';
        $theme = $controller->data->theme;

        $navbar = "jetbrains://php-storm/navigate/reference?project=yorr&path=yore/web/themes/$theme/html/navbar.php";
        $header = "jetbrains://php-storm/navigate/reference?project=yorr&path=yore/web/themes/$theme/html/header.php";
        $footer = "jetbrains://php-storm/navigate/reference?project=yorr&path=yore/web/themes/$theme/html/footer.php";
        $css = "jetbrains://php-storm/navigate/reference?project=yorr&path=yore/web/themes/$theme/css/theme.css";
        $js = "jetbrains://php-storm/navigate/reference?project=yorr&path=yore/web/themes/$theme/js/theme.js";

        switch ($where) {
            case 'top-right':

                    $debug =  "<a title=\"Open Theme Navbar\" href='$navbar'><i class=\"bi bi-sign-turn-right-fill theme-navbar-debug-color fadey\"></i></a>&nbsp;";
                    $debug .= "<a title=\"Open Theme Header\" href='$header'><i class=\"bi bi-align-top theme-navbar-debug-color fadey\"></i></a>&nbsp;";
                    $debug .= "<a title=\"Open Theme Footer\" href='$footer'><i class=\"bi bi-align-bottom theme-navbar-debug-color fadey\"></i></a>&nbsp;";
                    $debug .= "<a title=\"Open Theme CSS\" href='$css'><i class=\"bi bi-filetype-css theme-navbar-debug-color fadey\"></i></a>&nbsp;";
                    $debug .= "<a title=\"Open Theme Javascript\" href='$js'><i class=\"bi bi-filetype-js theme-navbar-debug-color fadey\"></i></a>&nbsp;";

                return $debug;
                break;

            case 'top-left':

                $debug_example = <<<EOF
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Debug</a>
                <div class="dropdown-menu" aria-labelledby="dropdown01">
                    <a class="dropdown-item" href="#">Action</a>
                    <a class="dropdown-item" href="#">Another action</a>
                    <a class="dropdown-item" href="#">Something else here</a>
                    <a class="dropdown-item" href="#">Something else too!!</a>
                </div>
            </li>
EOF;
$debug = '';

                //$debug = "<a href='$navbar'><i class=\"bi bi-gear-fill theme-navbar-debug-color\"></i></a>";
                //$debug .= "<a href='$navbar'><i class=\"bi bi-gear-fill theme-navbar-debug-color\"></i></a>";
                return $debug;
                break;
        }
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
        if ($controller->is_debug) { // TODO: Use the Debug module for stuff like this
            $str_data = json_encode($controller->data, JSON_PRETTY_PRINT);
            $domain = $controller->data->domain;
            //$domain = str_replace('app.app.', 'app.', $domain); // err
            $site = $controller->data->section;
            $page = $controller->data->pagekey;
            $view = $controller->data->view ?? 'null?';
            $theme = $controller->data->theme;
            $line = 1;
            $str_session = json_encode($_SESSION, JSON_PRETTY_PRINT);
            $uri = $_SERVER['REQUEST_URI'];

            $file = "jetbrains://php-storm/navigate/reference?project=yorr&path=yore/pages/_domains/$domain/$site/views/$view.html";

            $json = "jetbrains://php-storm/navigate/reference?project=yorr&path=yore/pages/_domains/$domain/$site/$page.json";

            $output .= // make this a template

"<button class='btn btn-danger btn-sm debug-info-button fadey' onclick=\"$('.debug-info').toggle()\">Debug</button>
<a class='btn btn-primary btn-sm fadey' href=\"$file\">Open View</a>
<a class='btn btn-warning btn-sm fadey' href=\"$json\">Open JSON</a>

<br/>
<textarea class='debug-info' style='width:100%; height:500px; display:none;'>
Yore - A web framework by Blackrush
Domain: {$controller->domain}  Site: {$controller->section}   Page Key: {$controller->pagekey}   Arg1: {$controller->arg1}   Arg2: {$controller->arg2}   Arg3: {$controller->arg3}

URI: $uri

Data: $str_data
                
Session: $str_session

Modules:
========
";
            foreach ($controller->modules as $name => $module) {
                $output .= $name . " = " . $module->moduleStatus . "\n";
            }


            $output .= "</textarea>";
        }
    }
}
