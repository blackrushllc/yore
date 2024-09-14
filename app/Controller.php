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

    /**
     * @var mixed|string
     */
    public $page;

    public $data, $json;

    public $header, $footer, $html, $js = [], $css = [], $output;

    public $env;

    // public vars in parent class $params, $site, $name, $arg1, $arg2, $arg3, $is_debug = true, $is_remote = false, $is_module=false;

    /**
     *
     */
    public function __construct($cli = false) {

        // Get public controller properties https://domain/site/name/arg1/arg2/arg3
        $this->init();

        if ($cli) return;

        if ($this->api) {
            $return = $this->apiProcess(); // TODO: Json Encode this before returning unless we have a reason not to like maybe don't json encode a string or stuff
            // Todo, maybe we don't want to exit with this right here.  Maybe we want to be able to output it like we do with the page in index.php
            exit(json_encode($return, JSON_PRETTY_PRINT));
        }

        // var_dump([$this->site,$this->page,$this->arg1,$this->arg2,$this->arg3]);
        // https://yoreweb.com/ho/ha/this/that/tother
        // array(5) { [0]=> string(2) "ho" [1]=> string(2) "ha" [2]=> string(4) "this" [3]=> string(4) "that" [4]=> string(6) "tother" }

        if (!$this->is_module) {

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

        if ($this->is_module) {

            $this->moduleProcess();

            // Module pages use the default home layout for the domain
            $this->site='default';
            $this->name='home';

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
            $temp[] = strtolower($site . '/' . 'api_' . $this->name);
            // Check if the fred_ method exists in the module /api/hello/test
            if (strtolower($site) == strtolower($this->site)) { // If the module name ($site) is the site property of $this (aka params[0] aka 'hello')
                if (method_exists($module, 'api_' . $this->name)) { // aka params[1] aka 'test'
                    // Call the method and pass $tail as the argument
                    $head = 'api_' . $this->name;
                    $tail = $_SERVER['REQUEST_METHOD']; // TODO: get the request method and pass it here
                    return $module->$head($this, $tail); // TODO: Is passing $this really necessary since we do it when we initialize the module?
                }

            }
        }
        echo "<pre>";
        var_dump($temp);
        var_dump($this);
        exit('ERROR: MISSING MODULE OR METHOD. I LOOKED EVERYWHERE :/');

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
            $temp[] = strtolower($site . '/' . 'web_' . $this->name);
            // Check if the fred_ method exists in the module /api/hello/test
            if (strtolower($site) == strtolower($this->site)) { // If the module name ($site) is the site property of $this (aka params[0] aka 'hello')
                if (method_exists($module, 'web_' . $this->name)) { // aka params[1] aka 'test'
                    // Call the method and pass $tail as the argument
                    $head = 'web_' . $this->name;
                    $tail = $_SERVER['REQUEST_METHOD']; // TODO: get the request method and pass it here (get|post)
                    //$var_dump([]);
                    $this->html = $module->$head($tail);
                    return;
                }
            }
        }
        echo "<pre>";
        var_dump($temp);
        var_dump($this);
        exit('ERROR: MISSING MODULE OR METHOD. I LOOKED EVERYWHERE :/');

    }

    /**
     * @return true
     */
    public function process() {

        // no no no. Body is only rendered using @body or @data('body')
        # $this->html = $this->data->body;

        // default site is 'default'
        // default view name is 'home'
        // https://{domain}/{site=default}/{name=home}


        if (empty($this->site)) {
            $this->site = 'default';
        }

        if (empty($this->data->view)) {
            $this->data->view = 'home';
        }

        // See if there is a "/pages/site/view/xxx.html"
        $default_view = '../pages/' . $this->site . '/views/' . $this->data->view . '.html';

        // See if there is a /pages/_domains/DOMAIN_NAME/site/view/xxx.html
        $domain_view  = '../pages/_domains/' . $this->domain . '/' . $this->site . '/views/' . $this->data->view . '.html';

        if (file_exists($domain_view)) {

            $this->html = file_get_contents($domain_view); // This is our whole tire content

        } else {

            $this->html = file_get_contents($default_view); // Just a template that displays @body()

        }

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
          "views": [], (not used rn)            | ^ i.e.      /pages/(domain)/(site)/views/(view name).html
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
            ---------------> PH@
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

        $this->output .= $this->html;

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
                $this->error_message = 'Syntax error, malformed JSON';
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

        $default_page_json = '../pages/' . $this->site . '/' . $this->name . '.json';

        $domain_page_json  = '../pages/_domains/' . $this->domain . '/' . $this->site . '/' . $this->name . '.json';

        if (file_exists($domain_page_json)) {

            $page_json = file_get_contents($domain_page_json);

        } else {

            $page_json = file_get_contents($default_page_json);

        }

        if (!$page_json) {

            $this->error_code = 404;

            return false;

        } else {

            $this->json = $page_json;

        }
        return true;

    }
}
