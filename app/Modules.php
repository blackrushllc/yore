<?php
namespace App;

# This is the Parent class for yore Modules 🔥😂🤑 😠🤔🧵👈😍💥

/*

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
     *
     */
    public function __construct() {
        // Constructor in Parent does nothing right now, but all modules should call it anyway
    }

    /**
     * @param Controller|null $controller
     * @return string
     */
    public function yore_module_init(Controller $controller = null, $myName = null) {
        $this->controller = $controller;
        $this->moduleStatus = "Running";
        $this->myName = $myName;
    }

}