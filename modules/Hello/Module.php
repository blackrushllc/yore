<?php
namespace Modules\Hello;

# This is the module code for the essential Yore library 🔥😂🤑 😠🤔🧵👈😍💥

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
        // Constructor in Parent does nothing right now, but all modules should call it anyway
        parent::__construct();
    }

    /**
     * @param Controller|null $controller
     * @return string
     */

    // Create a function that can be used in a Ph@ view, like @HELLO('World'). Return the string "Hello World" or "Hello " + argument

    /**
     * @param $world
     * @return string
     */
    public function fred_data($element) {
        return $this->controller->$element;
    }

    // Create a function that can be called as an api endpoint. For example, this is /api/library/test
    // Notice that the slug "library" is lower case
    /**
     * @param $controller
     * @param $method
     * @return string
     */
    public function api_test($controller, $method='GET') {

        // Notice that you have the $controller object here as a parameter, which basically gives you everything
        //  including all the modules which is important because you will want modules to interoperate,
        //  especially modules like Users and Database and Logging

        // FYI Whatever you return will JSON encoded
        return "Yore fredly naberhood libary";

    }

    /**
     * @param $value
     * @return void
     */
    function yore_module_post($value) {
        // This method is called if a form is being posted, with whatever we wanted passed to us from the form in $value
        // Right after this module has been yore_module_init()'d, this method is called if a form is being
        // posted that referenced this module with @modulename_post('value') in the view, which would translate
        // to "<input type='hidden' name='post_modulename_post' value='value'>", which is meant to trigger this
        // method in this module when the form is posted.  We can do whatever we want to with that, or do nothing
    }


}