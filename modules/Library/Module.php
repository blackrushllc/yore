<?php
namespace Modules\Library;

# This is the module code for the Hello module 🔥😂🤑 😠🤔🧵👈😍💥

/*

+ Extends Phats with @hello() which returns "Hello World" ( see public function fred_hello($world) )
+ Contains examples of other Ph@ extentions for Views
+ Adds a menu to the Nav Bar
    So my idea is that a Yore site is pretty much on rails as far as certain design things are concerned, like the
    Nav Bar. There is a Nav Bar and it works one way, with dropdowns on the left and Profile/Settings/Special Widget on
    the right. You include the Nav Bar in your view. You can customize the nav bar in your view, but the Modules will
    be adding things to your nav bar too. The Nav Bar only shows if you are logged in. Or something.  I guess we'll see won't we
+ Adds a slide-out tray to the right side of the screen
+ Demonstrates how to tell if the user is logged in and if they have Admin rights



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
     * @param Controller|null $controller
     * @return string
     */

    // Create a function that can be used in a Ph@ view, like @HELLO('World'). Return the string "Hello World" or "Hello " + argument

    /**
     * @param $world
     * @return string
     */
    public function fred_hello($world) {
        return "Hello $world";
    }

    // Optional method to modify output before it gets rendered, after it gets Phatted

    /**
     * @param $controller
     * @param $output
     * @return void
     */
    public function yore_output($controller, &$output) {
        $output = str_replace('Hello', 'Hark!', $output);
    }

    /**
     * @param $which
     * @return string
     */
    public function yore_navbar($which = 'top') {
        // Contribute to the web page's navigation bar
            // This needs to be more like a data structure and not sending back HTML
            // And then we need to be able to contribute stuff like
                // A top level link item
                // A top level _named_ drop down menu (<-- this is important)
                    // With sub menu entries and all that implies
                        // Like separators
                        // And sub sub menus
                        // And attributes like disabled, checked, etc
                // sub menu entries to add to existing other _named_ top menu dropdowns (<-- thats why that was important)


        switch($which) {

            // But for right now were just gonna do this fooligway:
            case 'top':
            case 'top-left':
                return '
                    <!--Hello Module Link -->
                    <li class="nav-item">
                        <a class="nav-link" href="#">Hello!</a>
                    </li>
                    ';
                break;

            case 'top-right':

                return '
                
                     <!-- Some Rando DropDown -->
                    <li class="nav-item dropdown">
                        <span class="nav-link dropdown-toggle" id="dropdown-x" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Tools</span>
                        <div class="dropdown-menu" aria-labelledby="dropdown-x">
                            <a class="dropdown-item" href="#">(M)anage Site</a>
                            <a class="dropdown-item" href="#">(N)ew Site</a>
                            <a class="dropdown-item" href="#">(E)dit Page</a>
                            <a class="dropdown-item" href="/module/users/logout">Logout</a>
                        </div>
                    </li>
            
                ';
                break;
        }
    }

    // Create a function that can be called as an api endpoint. For example, this is /api/hello/test
    // Notice that the slug for this module is /hello/ and not /Hello/
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
        return "I believe that I have finally learned how to Want again";

    }



}