<?php
namespace App;

# This is the Parent class for yore Modules 🔥😂🤑 😠🤔🧵👈😍💥

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


    The constructor currently does nothing but it's a good idea for all modules to call it anyway, for future use

    yore_module_init() is called to pass whe $controller object to every module AFTER all module classes are instantiated.
    This is cool because $controller contains everything so you an interoperate with other modules and do all kinds of
    stuff with it

    It might be a good idea to have prerequisite & compatability checks in yore_module_init() to loudly or quietly stop
    the module if there is a reason that it should not be running. This could be useful for a status page that shows
    all of the modules with messages about their status or what ain't quite right with them.

 */

use App\Controller;

/**
 *
 */
class Modules {

    /**
     * @var $controller
     */
    protected $controller;

    /**
     * @var $moduleStatus
     */
    public $moduleStatus;

    /**
     * @var $myName - Controller passes the name it knows this module by (i.e. "Hello")
     */
    public $myName;

    /**
     * @var $settings - Module settings from JSON file which can be over-ridden by domain just like views
     */
    public $settings;


    public $dir = __DIR__;

    /**
     *
     */
    public function __construct() {

    }

    /**
     * @param Controller|null $controller
     * @return string
     */
    public function yore_module_init(/* Controller */ $controller = null, $myName = null) {
        $this->controller = $controller;
        $this->moduleStatus = "Running";
        $this->myName = $myName;

        // Get module settings if they exist
        if ($settingsFIlePath = $this->settingsFilePath()) {
            try {
                $this->settings = json_decode(file_get_contents($settingsFIlePath));
            } catch (\Exception $e) {
                $this->settings = $e;
            }
        }
    }

    public function yore_module_post_init() {

    }

    public function viewFilePath($filename) { // i.e. login.html
        // Return the filespec of $filename for the current module view OR override view in pages dir
        $currentDirectory = $this->dir;
        $viewFilePath = $currentDirectory . '/views/' . $filename;
        $rootDirectory = $_SERVER['DOCUMENT_ROOT'];
        $pageFilePath = $rootDirectory . '/../pages/_domains/' . $this->controller->domain . '/modules/' . $this->myName . '/views/' . $filename;

        if (file_exists($pageFilePath)) $viewFilePath = $pageFilePath;

        if (!file_exists($viewFilePath)) {
            // i.e. /var/www/yore/pages/_domains/app.accidentresourcecenter.com/modules/Users/views/login.html
            $this->controller->abort(500, "Missing VIEW: $viewFilePath");
        }

        //dd($pageFilePath);
        return $viewFilePath;

    }

    public function settingsFilePath($filename = 'settings.json') { // settings.json


        // Return the filespec of $filename for the current module settings OR override settings in pages module dir
        $currentDirectory = $this->dir;
        $settingsFilePath= $currentDirectory . '/' . $filename;
        $rootDirectory = $_SERVER['DOCUMENT_ROOT'];
        if (empty($rootDirectory)) $rootDirectory = getcwd();
        $pageFilePath = $rootDirectory . '/../pages/_domains/' . $this->controller->domain . '/modules/' . $this->myName . '/' . $filename;

        if (file_exists($pageFilePath)) {
            $settingsFilePath = $pageFilePath;
        }

        if (!file_exists($settingsFilePath)) {
            // i.e. /var/www/yore/pages/_domains/example.com/modules/Users/settings.json
            return false; // It's okay not to have settings
        }

        return $settingsFilePath;

    }

}