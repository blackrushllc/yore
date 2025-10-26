<?php
namespace Modules\Users\Traits;

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


*/

/**
 *
 */
trait WebTrait {

    /**
     * @param $method
     * @return string
     */
    public function web_register($method = 'GET')
    {
        if ($method == 'GET') {

            $viewFilePath = $this->viewFilePath('register.html');

            // Example paths:
            //    /var/www/yore/modules/Users/Traits/views/register.html
            //    /var/www/yore/modules/Users/views/register.html
            //    /var/www/yore/pages/_domains/app.yoreweb.com/modules/Users/views/register.html

            // Read the contents of the file
            $content = file_get_contents($viewFilePath);

            // this needs to be in the view but..

            if ($this->controller->arg1 == 'nope') {

                $content .= "<h1>Invalid Registration - Please try again</h1>";

            }

            return $content;
        } else {

            if ($this->api_register()) {

                header('Location: /');
                return "Registration Successful...";

            } else {
                header('Location: /module/users/register/nope');
                return "Registration Failed...";
            }
        }
    }


    /**
     * @param $method
     * @return string
     */
    public function web_login($method = 'GET')
    {
        if ($method == 'GET') {

            $viewFilePath = $this->viewFilePath('login.html');

            //    /var/www/yore/modules/Users/Traits/views/login.html
            //    /var/www/yore/modules/Users/views/login.html
            //    /var/www/yore/pages/_domains/app.accidentresourcecenter.com/modules/Users/views/login.html

            // Read the contents of the file
            $content = file_get_contents($viewFilePath);

            // this needs to be in the view but..

            if ($this->controller->arg1 == 'nope') {

                $content .= "<h1>Invalid Login - Please try again</h1>";

            }

            return $content;
        } else {

            // How can we use settings to override the login?
            if ($this->api_login()) {

                header('Location: /');
                return "Login Successful...";

            } else {
                header('Location: /module/users/login/nope');
                return "Login Failed...";
            }
        }
    }

    public function web_auto_login($method = 'GET')
    {
        if ($this->api_login()) {

            header('Location: /');
            return "Login Successful...";

        } else {
            header('Location: /module/users/login/nope');
            return "Login Failed...";
        }

    }

    /**
     * @param $method
     * @return string
     */
    public function web_logout($method = 'GET')
    {
        unset($_SESSION['username']);
        unset($_SESSION['role']);
        unset($_SESSION['view']);
        unset($_SESSION['cna_id']);
        unset($_SESSION['hha_id']);
        unset($_SESSION['patient_id']);
        unset($_SESSION['patient']);
        unset($_SESSION['profile']);
        unset($_SESSION['cna']);
        unset($_SESSION['hha']);
        unset($_SESSION['xxx']);


        header('Location: /');
        return "Logout Successful...";


    }

}