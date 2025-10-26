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

Copyright (c) 2026 Erik Lee Olson for Blackrush, LLC

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

/app/Controller.php - This is the main website program. All HTML endpoints are processed by this

*/


 ######   #######  ##    ## ######## ########   #######  ##       ##       ######## ########
##    ## ##     ## ###   ##    ##    ##     ## ##     ## ##       ##       ##       ##     ##
##       ##     ## ####  ##    ##    ##     ## ##     ## ##       ##       ##       ##     ##
##       ##     ## ## ## ##    ##    ########  ##     ## ##       ##       ######   ########
##       ##     ## ##  ####    ##    ##   ##   ##     ## ##       ##       ##       ##   ##
##    ## ##     ## ##   ###    ##    ##    ##  ##     ## ##       ##       ##       ##    ##
 ######   #######  ##    ##    ##    ##     ##  #######  ######## ######## ######## ##     ##

namespace App;

class Controller extends Library {

    // Routable Models helper flag
    public bool $routable_model_handled = false;

    /**
     * @var mixed|string
     */
    public $page, $view_file;

    public $data, $json;

    public $header, $footer, $html, $js = [], $css = [], $output;

    public $env;

    // Indicates that a CustomController provided the HTML for this request
    public $custom_controller_html = false;

    // public vars in parent class $params, $section, $pagekey, $arg1, $arg2, $arg3, $is_debug = true, $is_remote = false, $is_module=false;

    /**
     *
     */
    public function __construct($cli = false) {

        // Get public controller properties https://domain/section/pagekey/arg1/arg2/arg3

        $this->init();

        if ($cli) return;

        if ($this->api) {
            $return = $this->apiProcess(); // TODO: Json Encode this before returning unless we have a reason not to like maybe don't json encode a string or stuff
            // Todo, maybe we don't want to exit with this right here.  Maybe we want to be able to output it like we do with the page in index.php
            exit(json_encode($return, JSON_PRETTY_PRINT));
        }

        // Routable Models: /{Model}/{action}[/{arg1}/{arg2}/{arg3}]
        // Model maps to Section and action maps to Pagekey. Models can read controller args directly; no slug args are passed here.

        // var_dump([$this->section,$this->page,$this->arg1,$this->arg2,$this->arg3]);
        // https://yoreweb.com/ho/ha/this/that/tother
        // array(5) { [0]=> string(2) "ho" [1]=> string(2) "ha" [2]=> string(4) "this" [3]=> string(4) "that" [4]=> string(6) "tother" }

        // Modeltroller top-level route: /{model}/{action}
        // and legacy within-section route: /{section}/{model}/{action}
        if (!$this->is_module && !$this->routable_model_handled) {
            \App\Domain\DomainAutoload::registerModels($this->domain, __DIR__ . '/../pages/_domains/' . $this->domain);
            $studlyDomain = \App\Domain\DomainAutoload::studlyDomain($this->domain);

            // 1) Top-level Model route: domain.com/{model}/{action}
            $modelSlugTop  = (string)$this->section;              // first segment
            $actionTop     = (string)($this->pagekey ?: 'index'); // second segment
            $studlyTop     = $this->studly($modelSlugTop);
            $fqcnTop       = 'Domain\\' . $studlyDomain . '\\Models\\' . $studlyTop;

            if (class_exists($fqcnTop)) {

                $instance = new $fqcnTop();
                $webMethod = 'web_' . $actionTop;
                $apiMethod = 'api_' . $actionTop;
                if (method_exists($instance, $webMethod)) {
                    $args = array_slice($this->params, 2); // extras after /{model}/{action}

                    // dd([$webMethod,$modelSlugTop,$actionTop,$studlyTop,$fqcnTop]);

                    /* dd() output here:

                    array(5) {
                      [0]=>
                      string(9) "web_index"
                      [1]=>
                      string(6) "orders"
                      [2]=>
                      string(5) "index"
                      [3]=>
                      string(6) "Orders"
                      [4]=>
                      string(34) "Domain\AppYorewebCom\Models\Orders"
                    }

                    /var/www/yore/app/Controller.php:118 dd

                    */



                    // Crash Here (no log)
                    $html = call_user_func([$instance, $webMethod], $this, (array)$_REQUEST);

                    //dd([$html,$webMethod,$modelSlugTop,$actionTop,$studlyTop,$fqcnTop]);

                    $this->renderHtmlBody((string)$html);
                    $this->routable_model_handled = true;
                } elseif (method_exists($instance, $apiMethod)) {
                    $args = array_slice($this->params, 2);
                    $out = call_user_func([$instance, $apiMethod], $this, (array)$_REQUEST);
                    $this->emitRaw($out);
                    return;
                }
            } else {
                // 2) Legacy short route within a section: /{section}/{model}/{action}
                $modelSlug  = (string)$this->pagekey;                  // second segment as model
                $actionSlug = (string)($this->arg1 ?: 'index');     // third segment as action
                $studlyModel  = $this->studly($modelSlug);
                $fqcn = 'Domain\\' . $studlyDomain . '\\Models\\' . $studlyModel;
                if (class_exists($fqcn)) {
                    $instance = new $fqcn();
                    $webMethod = 'web_' . $actionSlug;
                    $apiMethod = 'api_' . $actionSlug;
                    if (method_exists($instance, $webMethod)) {
                        $args = array_slice($this->params, 3); // extras after /{section}/{model}/{action}
                        $html = call_user_func([$instance, $webMethod], $this, (array)$_REQUEST);
                        $this->renderHtmlBody((string)$html);
                        $this->routable_model_handled = true;
                    } elseif (method_exists($instance, $apiMethod)) {
                        $args = array_slice($this->params, 3);
                        $out = call_user_func([$instance, $apiMethod], $this, (array)$_REQUEST);
                        $this->emitRaw($out);
                        return;
                    }
                }
            }
        }
        
        if (!$this->is_module && !$this->routable_model_handled) { // URL is not a module route or already handled by routable model

            // Custom Controller dispatch (domain/page-specific Controller.php)
            $handled_by_custom = $this->customControllerDispatch();

            if ($handled_by_custom) {
                // Ensure we have minimal page data for theming when no page JSON is present
                $this->ensurePageDataForCustomController();
            } else {
                // Get the json for this page (from disk or perhaps a data source)
                if (!$this->json()) {
                    $this->abort();
                }

                // Decode the json for this page
                if (!$this->page()) {
                    $this->abort();
                }

                // Get the view for this page with #Vars (any defined Php variables) and Markdown translated
                if (!$this->process()) {
                    $this->abort();
                }
            }
        }

        if ($this->is_module) { // URL is a module route

            $this->moduleProcess();

            // Module pages use the default home layout for the domain
            $this->section='default';
            $this->pagekey='home';

            if (!$this->json()) {
                $this->abort();
            }

            // Decode the json for this page
            if (!$this->page()) {
                $this->abort();
            }
        }

        // Stack the web page together (Header, Navbar, Body, Footer, with CSS & Script refs)
        if (!$this->assemble()) {
            $this->abort();
        }

        // Run this through the Viewer(s)
        // $this->>view() is Library (parent class) and it uses Fred and all Module->yore_output()'s to take a bite
        $this->page = $this->view($this->output, $this->data);

    }


    ########  ########   #######   ######  ########  ######   ######
    ##     ## ##     ## ##     ## ##    ## ##       ##    ## ##    ##
    ##     ## ##     ## ##     ## ##       ##       ##       ##
    ########  ########  ##     ## ##       ######    ######   ######
    ##        ##   ##   ##     ## ##       ##             ##       ##
    ##        ##    ##  ##     ## ##    ## ##       ##    ## ##    ##
    ##        ##     ##  #######   ######  ########  ######   ######

    /**
     * @return void
     */
    public function ApiProcess()
    {
        if (!gettype($this->modules) == 'array') {
            $this->abort(500, "Empty Module Array");
        }

        // https://yobasic.com/api/users/login?username=erik&password=mermaid to LOGIN or provide incomplete creds to LOGOUT

        foreach ($this->modules as $site => $module) {
            $temp[] = strtolower($site . '/' . 'api_' . $this->pagekey);
            // Check if the fred_ method exists in the module /api/hello/test
            if (strtolower($site) == strtolower($this->section)) { // If the module name ($site) is the section property of $this (aka params[0] aka 'hello')
                if (method_exists($module, 'api_' . $this->pagekey)) { // aka params[1] aka 'test'
                    // Call the method and pass $tail as the argument
                    $head = 'api_' . $this->pagekey;
                    $tail = $_SERVER['REQUEST_METHOD']; // TODO: get the request method and pass it here
                    return $module->$head($this, $tail); // TODO: Is passing $this really necessary since we do it when we initialize the module?
                }

            }
        }
        echo "<pre>";
        echo('ERROR: MISSING MODULE OR METHOD: ' . 'web_' . $this->pagekey . ' -  I LOOKED EVERYWHERE :/\n');
        var_dump($temp);
        var_dump($this);
        exit;

    }
    /**
     * @return void
     */
    public function moduleProcess()
    {
        if (!gettype($this->modules) == 'array') {
            $this->abort(500, "Empty Module Array");
        }

        // https://yobasic.com/module/users/login

        foreach ($this->modules as $site => $module) {
            $temp[] = strtolower($site . '/' . 'web_' . $this->pagekey);
            // Check if the fred_ method exists in the module /api/hello/test
            if (strtolower($site) == strtolower($this->section)) { // If the module name ($site) is the section property of $this (aka params[0] aka 'hello')
                if (method_exists($module, 'web_' . $this->pagekey)) { // aka params[1] aka 'test'
                    // Call the method and pass $tail as the argument
                    $head = 'web_' . $this->pagekey;
                    $tail = $_SERVER['REQUEST_METHOD']; // TODO: get the request method and pass it here (get|post)
                    //$var_dump([]);
                    $this->html = $module->$head($tail);
                    return;
                }
            }
        }
        echo "<pre>";
        echo('ERROR: MISSING MODULE OR METHOD: ' . 'web_' . $this->pagekey . ' -  I LOOKED EVERYWHERE :/\n');
        var_dump($temp);
        var_dump($this);
        exit;


    }

    /**
     * Attempt to locate and dispatch a domain/page-specific custom controller.
     * Returns true if a web_* method handled the request (html set), or exits on api_*.
     * Falls back to default processing when false.
     * @return bool
     */
    protected function customControllerDispatch(): bool {
        try {
            $firstSlug = $this->section ?: 'default';
            if (empty($firstSlug)) { $firstSlug = 'default'; }
            $controllerFile = __DIR__ . '/../pages/_domains/' . $this->domain . '/' . $firstSlug . '/Controller.php';
            if (!file_exists($controllerFile)) {
                return false;
            }

            require_once $controllerFile;

            if (!class_exists('\\Controller')) {
                return false;
            }

            $custom = new \Controller();
            if (method_exists($custom, 'yore_controller_init')) {
                // Pass the core controller so custom controllers can use modules/helpers
                $custom->yore_controller_init($this);
            }

            // Determine action from second slug; default to 'index' when missing
            $action = isset($this->params[1]) && $this->params[1] !== '' ? $this->params[1] : 'index';
            // Ensure safe method name
            $action = preg_replace('/[^a-zA-Z0-9_]/', '_', $action);

            $webMethod = 'web_' . $action;
            $apiMethod = 'api_' . $action;
            $args = array_slice($this->params, 2);

            if (method_exists($custom, $webMethod) && is_callable([$custom, $webMethod])) {
                $result = call_user_func_array([$custom, $webMethod], $args);
                $this->html = is_string($result) ? $result : '';
                $this->custom_controller_html = true;
                return true;
            }

            if (method_exists($custom, $apiMethod) && is_callable([$custom, $apiMethod])) {
                $result = call_user_func_array([$custom, $apiMethod], $args);
                if (is_array($result) || is_object($result)) {
                    if (!headers_sent()) {
                        header('Content-Type: application/json');
                    }
                    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                } else {
                    echo $result;
                }
                exit;
            }
        } catch (\Throwable $e) {
            // On any error, allow default behavior to proceed
        }
        return false;
    }

    /**
     * Ensure we have minimal $this->data for theming when a CustomController handled the request.
     */
    protected function ensurePageDataForCustomController(): void {
        if (!is_object($this->data)) {
            $this->data = (object)[];
        }
        if (empty($this->data->domain)) { $this->data->domain = $this->domain; }
        if (empty($this->data->section)) { $this->data->section = $this->section ?: 'default'; }
        if (empty($this->data->pagekey)) { $this->data->pagekey = $this->pagekey ?: 'home'; }
        if (empty($this->data->title)) { $this->data->title = ucfirst($this->data->section); }
        if (empty($this->data->theme)) { $this->data->theme = $this->getDefaultTheme(); }
    }

    // Convert "orders" or "user_profile" or "user-profile" to "Orders" / "UserProfile"
    protected function studly(string $slug): string {
        $slug = trim($slug);
        $slug = str_replace(['-', '.', ' '], '_', $slug);
        $parts = preg_split('/[_]+/', $slug, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $parts = array_map(static fn($p) => ucfirst(preg_replace('/[^a-zA-Z0-9]/', '', $p)), $parts);
        return implode('', $parts);
    }

    // Pipe provided body HTML through theming the same as page controllers
    protected function renderHtmlBody(string $body): bool {
        $this->ensurePageDataForCustomController();
        $this->html = $body;
        $this->custom_controller_html = true;
        return true;
    }

    // Emit raw output (arrays/objects as JSON), then exit
    protected function emitRaw($out): void {
        if (is_array($out) || is_object($out)) {
            if (!headers_sent()) {
                header('Content-Type: application/json');
            }
            echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        } else {
            echo (string)$out;
        }
        exit;
    }

    /**
     * Resolve a default theme name when page JSON is not present.
     */
    protected function getDefaultTheme(): string {
        // Prefer domain settings if provided
        $theme = $this->settings['theme'] ?? null;
        if (!empty($theme) && is_dir(__DIR__ . '/../web/themes/' . $theme)) {
            return $theme;
        }
        // Fallback to first theme directory under /web/themes
        $dirs = glob(__DIR__ . '/../web/themes/*', GLOB_ONLYDIR);
        if (!empty($dirs)) {
            $base = basename($dirs[0]);
            if (!empty($base)) return $base;
        }
        // Last resort
        return 'default';
    }
    
    /**
     * @return true
     */
    public function process() {

        // no no no. Body is only rendered using @body or @data('body')
        # $this->html = $this->data->body;

        // default site is 'default'
        // default view name is 'home' UNLESS $_SESSION['view'] has something
        // https://{domain}/{site=default}/{name=home}


        if (empty($this->section)) {
            $this->section = 'default';
        }

//        // "home" page *view* overrides "home" page json even if it's set in page json. OR it defaults to 'home' if not set
//        if (empty($this->data->view) or ($this->data->page == 'home')) {
//            $this->data->view = $_SESSION['view'] ?? ($this->data->view ?? 'homepage');
//        }

// Only use $_SESSION['view'] when the page JSON has no view set.

        if (empty($this->data->view)) {
            $this->data->view = $_SESSION['view'] ?? 'homepage';
        }

//        dd([ 'page' => $this->data->page, 'view' => $this->data->view, '_SESSION.view' => $_SESSION['view'] ?? null, '_SESSION.role' => $_SESSION['role'] ?? null ]);

        // See if there is a "/pages/site/view/xxx.html"
        $default_view = __DIR__ . '/../pages/' . $this->section . '/views/' . $this->data->view . '.html';

        // See if there is a /pages/_domains/DOMAIN_NAME/section/view/xxx.html
        $domain_view  = __DIR__ . '/../pages/_domains/' . $this->domain . '/' . $this->section . '/views/' . $this->data->view . '.html';

        // See if there is a /pages/_domains/DOMAIN_NAME/section/view/xxx.blade.php
        $domain_blade_view  = __DIR__ . '/../pages/_domains/' . $this->domain . '/' . $this->section . '/views/' . $this->data->view . '.blade.php';


        // Initialize variables for role-based views
        $role_view = null;
        $role_blade_view = null;

        // Check if a user role is set in the session
        if (!empty($_SESSION['role'])) {
            // Check if the page data contains role-based views
            if (!empty($this->data->views)) { // $this->data->views is an array of role => view name
                $roleViews = (array)$this->data->views;

                // If a view is defined for the current user role, set the corresponding view paths
                if (!empty($roleViews[$_SESSION['role']])) {
                    $role_view = __DIR__ . '/../pages/_domains/' . $this->domain . '/' . $this->section . '/views/' . $_SESSION['role'] . '.html';
                    $role_blade_view = __DIR__ . '/../pages/_domains/' . $this->domain . '/' . $this->section . '/views/' . $_SESSION['role'] . '.blade.php';
                } else {
                    if (in_array($_SESSION['role'], $roleViews)) {
                        $role_view = __DIR__ . '/../pages/_domains/' . $this->domain . '/' . $this->section . '/views/' . $_SESSION['role'] . '.html';
                        $role_blade_view = __DIR__ . '/../pages/_domains/' . $this->domain . '/' . $this->section . '/views/' . $_SESSION['role'] . '.blade.php';
                    }
                }
            }
        }

        try {
            if ($role_blade_view && file_exists($role_blade_view)) {

                $role_blade_view = str_replace(__DIR__ . '/../pages/_domains/' . $this->domain . '/' . $this->section . '/views/', '', $role_blade_view);
                $role_blade_view = str_replace('.blade.php', '', $role_blade_view);

                // pages/_domains/app.luxecardclub.com/default/views/admin.blade.php
                $this->html = $this->render($role_blade_view); // This is our alternate blade view based on Role
                $this->view_file = $role_blade_view;

            } elseif ($role_view && file_exists($role_view)) {

                $this->html = file_get_contents($role_view); // This is our alternate html view based on Role
                $this->view_file = $role_view;

            } elseif (file_exists($domain_blade_view)) {

                $domain_blade_view = str_replace(__DIR__ . '/../pages/_domains/' . $this->domain . '/' . $this->section . '/views/', '', $domain_blade_view);
                $domain_blade_view = str_replace('.blade.php', '', $domain_blade_view);

                $this->html = $this->render($domain_blade_view); // This is our whole tire blade content
                $this->view_file = $domain_blade_view;

            } elseif (file_exists($domain_view)) {

                $this->html = file_get_contents($domain_view); // This is our whole tire htm content
                $this->view_file = $domain_view;

            } else {

                if (file_exists($default_view)) {
                    $this->html = file_get_contents($default_view); // Just a template that displays @body()
                    $this->view_file = $default_view;

                } else {
                    $this->abort(404, "View File Missing - $domain_view / $default_view / $role_view / $role_blade_view");
                }
            }
        } catch (\Exception $e) {
            $this->abort(500, "Exception in process(): " . $e->getMessage(), $e->getTraceAsString());
        }

        //dd([ $domain_blade_view, $default_view, $role_blade_view, $this->view_file, $this->html ]); // <<------------------------------ 👁👁

        if (empty($this->html)) $this->html="@body()";

        // The html should be either data->view, data->views[] combined, or a default phat with @body()

        return true;

    }

       ###     ######   ######  ######## ##     ## ########  ##       ########
      ## ##   ##    ## ##    ## ##       ###   ### ##     ## ##       ##
     ##   ##  ##       ##       ##       #### #### ##     ## ##       ##
    ##     ##  ######   ######  ######   ## ### ## ########  ##       ######
    #########       ##       ## ##       ##     ## ##     ## ##       ##
    ##     ## ##    ## ##    ## ##       ##     ## ##     ## ##       ##
    ##     ##  ######   ######  ######## ##     ## ########  ######## ########

    /**
     * @return true
     */
    public function assemble() {

        // include theme js and css (require it)

        $dir = __DIR__ . '/../web/themes/' . $this->data->theme . '/css/*.css';

        foreach (glob($dir) as $filename) {
            $this->css[] = explode('/../web', $filename)[1];
        }

        if (empty($this->css)) {

            $this->abort(500, "Missing Theme Stylesheet");

        }

        $dir = __DIR__ . '/../web/themes/' . $this->data->theme . '/js/*.js';

        foreach (glob($dir) as $filename) {
            $this->js[] = explode('/../web', $filename)[1];
        }

        if (empty($this->js)) {

            $this->abort(500, "Missing Theme Javascript");

        }

        // Include common js and css too

        $dir = __DIR__ . '/../web/css/*.css';

        foreach (glob($dir) as $filename) {
            $this->css[] = explode('/../web', $filename)[1];
        }

        $dir = __DIR__ . '/../web/js/*.js';

        foreach (glob($dir) as $filename) {
            $this->js[] = explode('/../web', $filename)[1];
        }

        ##     ## ########    ###    ########  ######## ########
        ##     ## ##         ## ##   ##     ## ##       ##     ##
        ##     ## ##        ##   ##  ##     ## ##       ##     ##
        ######### ######   ##     ## ##     ## ######   ########
        ##     ## ##       ######### ##     ## ##       ##   ##
        ##     ## ##       ##     ## ##     ## ##       ##    ##
        ##     ## ######## ##     ## ########  ######## ##     ##

        // Themes have to be installed under /web/themes so that their assets are public.
        // _domain trees have view phats (for content section only) and page jsons (which reference the phat file)

        /*

        For instance, here is a page json in the blackrush.us domain for /games/andromeda ( blackrush.us/site/page )

        {
          "domain": "blackrush.us",             | domain part of url
          "site": "games",                      | site part - This is where you have a collection of related pages, same as the folder name its in (See next..)
          "page": "andromeda",                  | page part - This is one page in your site ( (^) it's gonna be the same as the json file name that it's in..
          "title": "Blackrush Entertainment",   |   ... because one day in heaven these page folders and json files will be in a data source and not in disk )
          "theme": "blackrush",                 | this theme MUST be available under /web/themes/(theme name)/
          "desc": "This is the BLACKRUSH games site home page games view",
          "body": "This is the body of the 'blackrush' domain, 'games' site, 'andromeda' page, andromeda view",
          "view": "andromeda",                  | /pages/_domains/blackrush.us/games/views/andromeda.html
          "views": ['admin','user','foo','etc'],| use a view that matches one of these roles, i.e. rather than "andromeda" use /pages/(domain)/(site)/views/(view name).html
          "example_fred_var": "Yo ho ho",       | Variables that can be referenced by Ph@
          "security": false, (not used rn)
          "public": true, (not used rn)
          "markdown": true (not used rn?)
        }

        Theme: (referenced in the page json, or "default" if not)
            /web/themes/(theme name)/
            /web/themes/(theme name)/css/theme.css
            /web/themes/(theme name)/js/theme.js
            /web/themes/(theme name)/images/
            /web/themes/(theme name)/html/header.php
            /web/themes/(theme name)/html/navbar.php
            /web/themes/(theme name)/html/footer.php

        Output stacks as follows:
        [
            Header
            CSS (href's)
            NavBar
            >> >> >> PAGE JSON | PAGE VIEW
            JS (src's)
            Footer
        ] -> then ->
            ---------------> @Fred
            ----------------> Module Filters
            -----------------> BrOwSeR



        */
        $header_file = __DIR__ . '/../web/themes/' . $this->data->theme . '/html/header.php';
        ob_start();
        //$return =
            include $header_file; // Can return a value with return()
        $this->output .= ob_get_clean();

         ######   ######   ######
        ##    ## ##    ## ##    ##
        ##       ##       ##
        ##        ######   ######
        ##             ##       ##
        ##    ## ##    ## ##    ##
         ######   ######   ######

        $this->output .= $this->cssFiles();


        ##    ##    ###    ##     ## ########     ###    ########
        ###   ##   ## ##   ##     ## ##     ##   ## ##   ##     ##
        ####  ##  ##   ##  ##     ## ##     ##  ##   ##  ##     ##
        ## ## ## ##     ## ##     ## ########  ##     ## ########
        ##  #### #########  ##   ##  ##     ## ######### ##   ##
        ##   ### ##     ##   ## ##   ##     ## ##     ## ##    ##
        ##    ## ##     ##    ###    ########  ##     ## ##     ##

        $navbar_file = __DIR__ . '/../web/themes/' . $this->data->theme . '/html/navbar.php';
        ob_start();
        //$return =
        include $navbar_file; // Can return a value with return()
        $this->output .= ob_get_clean();

        // Use the following body (<main></main>) container from a body template in the theme. Use this as well as the
        // NavBar, Header and Footer templates in conjunction with elements from the page.json which will then be
        // configurable using the page editor tool (Todo, this will be a replaceable vendor package as well)

//        $this->output .= "
//        <main role=\"main\" class=\"container\">
//          <div class=\"starter-template\">
//            <h1>Bootstrap starter template</h1>
//            <p class=\"lead\">{$this->html}</p>
//          </div>
//        </main>
//        ";



        ##     ## #### ######## ##      ##         ##   ######## ########  #### ########
        ##     ##  ##  ##       ##  ##  ##        ##    ##       ##     ##  ##     ##
        ##     ##  ##  ##       ##  ##  ##       ##     ##       ##     ##  ##     ##
        ##     ##  ##  ######   ##  ##  ##      ##      ######   ##     ##  ##     ##
         ##   ##   ##  ##       ##  ##  ##     ##       ##       ##     ##  ##     ##
          ## ##    ##  ##       ##  ##  ##    ##        ##       ##     ##  ##     ##
           ###    #### ########  ###  ###    ##         ######## ########  ####    ##


        switch ($this->arg1) {

            case 'edit':
                $_SESSION['fetch'] = '/' . $this->section . '/' . $this->pagekey . '/load';
                $_SESSION['save'] = '/' . $this->section . '/' . $this->pagekey . '/save';
                $editor_file = __DIR__ . '/includes/edit.html';
                $this->output .= file_get_contents($editor_file);
                break;

            case 'load': // Move this to top of Assemble
                echo $this->html;
                exit;
                break;

            case 'save': // Move this to top of Assemble
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
                    $content = $_POST['content'];
                    $filename = $this->view_file;


                    // There is no point in saving a backup of the editor file
                    // unless you roll the backups and/or have a "publish" function
                    // and the ability to revert to the last "published" version.

                    //$backupFilename = $filename . '.' . date('d'); // Get current day of the month
                    //$backupFilename = $filename . '.bak';

                    // Check if the document exists
//                    if (file_exists($filename)) {
//                        // Rename the existing file to file.html.dd
//                        if (!rename($filename, $backupFilename)) {
//                            $this->abort(500, "Error: Could not rename the existing document.");
//                          }
//                    }

                    // Save the new content
                    if (file_put_contents($filename, $content) !== false) {
                        exit('Document saved successfully');
                    } else {
                        $this->abort(500, "Error: Could not save the document.");
                    }
                } else {
                    $this->abort(500, "Error: Invalid request.");
                 }
                $this->abort(500, "Error: This should never happen :/");
                break;
            default:
                $this->output .= $this->html;
        }







              ##    ###    ##     ##    ###     ######   ######  ########  #### ########  ########
              ##   ## ##   ##     ##   ## ##   ##    ## ##    ## ##     ##  ##  ##     ##    ##
              ##  ##   ##  ##     ##  ##   ##  ##       ##       ##     ##  ##  ##     ##    ##
              ## ##     ## ##     ## ##     ##  ######  ##       ########   ##  ########     ##
        ##    ## #########  ##   ##  #########       ## ##       ##   ##    ##  ##           ##
        ##    ## ##     ##   ## ##   ##     ## ##    ## ##    ## ##    ##   ##  ##           ##
         ######  ##     ##    ###    ##     ##  ######   ######  ##     ## #### ##           ##

        $this->output .= $this->jsFiles();

        ########  #######   #######  ######## ######## ########
        ##       ##     ## ##     ##    ##    ##       ##     ##
        ##       ##     ## ##     ##    ##    ##       ##     ##
        ######   ##     ## ##     ##    ##    ######   ########
        ##       ##     ## ##     ##    ##    ##       ##   ##
        ##       ##     ## ##     ##    ##    ##       ##    ##
        ##        #######   #######     ##    ######## ##     ##


        $footer_file = __DIR__ . '/../web/themes/' . $this->data->theme . '/html/footer.php';
        ob_start();
        //$return =
        include $footer_file; // Can return a value with return()
        $this->output .= ob_get_clean();

        return true;

    }

     ######   ######   ######  ######## #### ##       ########  ######
    ##    ## ##    ## ##    ## ##        ##  ##       ##       ##    ##
    ##       ##       ##       ##        ##  ##       ##       ##
    ##        ######   ######  ######    ##  ##       ######    ######
    ##             ##       ## ##        ##  ##       ##             ##
    ##    ## ##    ## ##    ## ##        ##  ##       ##       ##    ##
     ######   ######   ######  ##       #### ######## ########  ######

    /**
     * @return string
     */
    public function cssFiles() {

        $output = '';

        foreach ($this->css as $file) {

            $u = uniqid('v', true);

            $output .= "<link  media=\"all\" rel=\"stylesheet\" href=\"$file?u=$u\" />\n";

        }
        return $output;
    }

          ##  ######  ######## #### ##       ########  ######
          ## ##    ## ##        ##  ##       ##       ##    ##
          ## ##       ##        ##  ##       ##       ##
          ##  ######  ######    ##  ##       ######    ######
    ##    ##       ## ##        ##  ##       ##             ##
    ##    ## ##    ## ##        ##  ##       ##       ##    ##
     ######   ######  ##       #### ######## ########  ######

    /**
     * @return string
     */
    public function jsFiles() {

        $output = '';

        foreach ($this->js as $file) {

            $u = uniqid('v', true);

            $output .= "<script defer=\"defer\"  type=\"application/javascript\" src=\"$file?u=$u\" /></script>\n";

        }
        return $output;
    }

    ########     ###     ######   ########
    ##     ##   ## ##   ##    ##  ##
    ##     ##  ##   ##  ##        ##
    ########  ##     ## ##   #### ######
    ##        ######### ##    ##  ##
    ##        ##     ## ##    ##  ##
    ##        ##     ##  ######   ########

    /**
     * @return mixed|true
     */
    public function page() {
        $this->json = preg_replace('/^\xEF\xBB\xBF/', '', $this->json);
        $this->data = json_decode($this->json);

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return true;
                break;
            case JSON_ERROR_DEPTH:
                $this->error_message = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $this->error_message = 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $this->error_message = 'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $this->error_message = 'Syntax error, malformed JSON - ' . $this->json;
                var_dump($this->data);
                break;
            case JSON_ERROR_UTF8:
                $this->error_message = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $this->error_message = 'Unknown JSON error';
                break;
        }
        // 500 is the default error

        return $this->result(false);

    }



          ##  ######   #######  ##    ##
          ## ##    ## ##     ## ###   ##
          ## ##       ##     ## ####  ##
          ##  ######  ##     ## ## ## ##
    ##    ##       ## ##     ## ##  ####
    ##    ## ##    ## ##     ## ##   ###
     ######   ######   #######  ##    ##


    /**
     * @return bool
     */
    public function json() {

        # If the page exists, return the json data for the page

        # otherwise return a blank template

        // default site is 'default'
        // default page name is 'home'
        // https://{domain}/{site=default}/{page=home}

        $default_page_json = __DIR__ . '/../pages/' . $this->section . '/' . $this->pagekey . '.json';

        $domain_page_json  = __DIR__ . '/../pages/_domains/' . $this->domain . '/' . $this->section . '/' . $this->pagekey . '.json';

        $page_json = false;

        if (file_exists($domain_page_json)) {
            $page_json = file_get_contents($domain_page_json);
        } else {
            if (file_exists($default_page_json)) {

                $page_json = file_get_contents($default_page_json);
            }
        }

        if (!$page_json) {
            $this->error_code = 418;

            return false;

        } else {

            $this->json = $page_json;

        }
        return true;

    }
}
