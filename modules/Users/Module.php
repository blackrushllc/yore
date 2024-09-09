<?php
namespace Modules\Users;

# This is the module code for the Users module

/*



 */

use App\Controller;
use App\Modules;

class Module extends Modules {

    public $username, $role;

    public function __construct() {
        // Constructor is optional
        $this->username = $_SESSION['username'] ?? null;
        $this->role = $_SESSION['role'] ?? null;

        // Constructor in Parent does nothing right now, but all modules should call it anyway
        parent::__construct();

    }

    /**
     * @param $pod
     * @return string
     */
    public function yore_user_navbar($pod = 'top') {
        // Get a NavBar icon from the Users module, if it exists, if it wants to give us one
        if ($this->username) {
            return '
            
            <a style="color:#f60" href=https://olson.host"><i class="bi bi-chat-left-text theme-navbar-color"></i></a>
            &nbsp;
            <a style="color:#e70" href="/module/users/logout"><i class="bi bi-twitch theme-navbar-color"></i></a>
            &nbsp;
            <a style="color:#d80" href="https://join.slack.com/t/blackrushworkspace/shared_invite/zt-1yx3frggj-WP1on~N4wubHc2gQfQudLQ" target="_blank"><i class="bi bi-slack theme-navbar-color"></i></a>
            &nbsp;
            <a style="color:#c90" href="https://github.com/olsonhost" target="_blank"><i class="bi bi-github theme-navbar-color"></i></a>
            &nbsp;
            <a style="color:#bA0" href="https://discord.gg/EcEWvnyp" target="_blank"><i class="bi bi-discord theme-navbar-color"></i></a>
            
            
            
            ';
        } else {
            return '<a style="color:#e70" href="/module/users/login"><i class="bi bi-twitch theme-navbar-color"></i></a>';
        }
    }

    // Create a function that can be used in a Ph@ view, like @HELLO('World'). Return the string "Hello World" or "Hello " + argument

    /**
     * @param $dummy
     * @return mixed|null
     */
    public function fred_username($dummy=null) {
        return $this->username;
    }

    /**
     * @param $dummy
     * @return int
     */
    public function fred_isadmin($dummy=null) {
        if ($this->role == 'admin')
            return 1;
        else
            return 0;
    }

    /**
     * @param $method
     * @return bool
     */
    public function api_login($method='GET') {

        $username = $_REQUEST['username'] ?? false;
        $password = $_REQUEST['password'] ?? false;

        if (!$username and !$password) {
            $this->controller->abort(401, 'Invalid Login Credentials');
        }

        if ($username == 'admin') {

            if ($password == 'mermaid') {

                $_SESSION['username']   = $this->username   = $username;
                $_SESSION['role']       = $this->role       = 'admin';

                return true;

            }

        }

        if ($username == 'erik') {

            if ($password == 'mermaid') {

                $_SESSION['username']   = $this->username   = $username;
                $_SESSION['role']       = $this->role       = 'user';

                return true;

            }

        }

        $_SESSION['username']   = $this->username   = null;
        $_SESSION['role']       = $this->role       = null;

        return false;

    }

    /**
     * @param $method
     * @return string
     */
    public function web_login($method = 'GET')
    {
        if ($method == 'GET') {
            // Get the directory of the current file (Module.php)
            $currentDirectory = __DIR__;

            // Build the relative path to the login.php file
            $viewFilePath = $currentDirectory . '/views/login.html';

            // Read the contents of the file
            $content = file_get_contents($viewFilePath);

            // this needs to be in the view but..

            if ($this->controller->arg1 == 'nope') {

                $content .= "<h1>NOPE!</h1>";

            }

            return $content;
        } else {
            if ($this->api_login()) {

                header('Location: /');
                return "Login Successful...";

            } else {
                header('Location: /module/users/login/nope');
                return "Login Successful...";
            }
        }
    }

    /**
     * @param $method
     * @return string
     */
    public function web_logout($method = 'GET')
    {

        $_SESSION['username']   = $this->username   = null;
        $_SESSION['role']       = $this->role       = null;
        header('Location: /');
        return "Logout Successful...";


    }

}