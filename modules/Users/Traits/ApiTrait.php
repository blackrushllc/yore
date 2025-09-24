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
trait ApiTrait {


    /**
     * @param $method
     * @return bool
     */
    public function api_register($method='POST') {

        $controller = $this->controller;

        $email = $controller->request('email');
        $phone = $controller->request('phone');
        $username = $controller->request('username');
        $password = $controller->request('password');

        if (!$username) $username = $email ?? false;
        if (!$username) $username = $phone ?? false;

        if (!$username or !$password) {
            $this->controller->abort(401, 'Invalid Registration Credentials');
        }

        $name = $_REQUEST['name'] ?? $username;

        // If $username or $name contains * or ? then it's probably a bot
        if (strpbrk($username, '*?') !== false) {
            $controller->flash("Sorry, this registration information is not valid. Please try again.");
            return false;
        }

        if (strpbrk($name, '*?') !== false) {
            $controller->flash("Sorry, this registration information is not valid. Please try again.");
            return false;
        }

        if (strlen($password) < 6) {
            $controller->flash("Sorry, your password must be at least 6 characters. Please try again.");
            return false;
        }

        // Using custom auth table and fields
        if ( (isset($this->settings->auth_table)) && (isset($this->settings->auth_username_field)) && (isset($this->settings->auth_password_field))) {
            $sql = sprintf("SELECT * FROM %s WHERE ? in (%s)",
                $this->settings->auth_table, $this->settings->auth_username_field);
        } else {
            $sql = sprintf("SELECT * FROM users WHERE username = ?");
        }
        $stmt = $this->controller->database->sql($sql, [$username]);

        $this->user = $stmt->fetch();

        if ($this->user) {
            $controller->flash("Sorry, a user with this username already exists. Please try again.");
            return false;
        }

        // Using custom auth table and fields
        if ( (isset($this->settings->auth_table)) && (isset($this->settings->auth_username_field)) && (isset($this->settings->auth_password_field))) {
            $sql = sprintf("INSERT INTO %s SET username = ?, password = ?, name = ?, email = ?, phone = ?",
                $this->settings->auth_table
            );
            $this->controller->database->sql($sql, [$username, $password, $name, $email, $phone], true); // nocatch = true
            //$user_id = $this->controller->database->lastInsertId();
        } else {
            $sql = "INSERT INTO users SET username = ?, password = ?";
            $this->controller->database->sql($sql, [$username, $password], true); // nocatch = true
            //$user_id = $this->controller->database->lastInsertId();
        }

        // Using custom auth table and fields
        if ( (isset($this->settings->auth_table)) && (isset($this->settings->auth_username_field)) && (isset($this->settings->auth_password_field))) {
            $sql = sprintf("SELECT * FROM %s WHERE ? in (%s)",
                $this->settings->auth_table, $this->settings->auth_username_field);
        } else {
            $sql = sprintf("SELECT * FROM users WHERE username = ?");
        }
        $stmt = $this->controller->database->sql($sql, [$username]);

        $this->user = $stmt->fetch();

        if ($this->user) {
            if (isset($this->settings->uniq_username_field)) {
                $_SESSION['username'] = $this->username = $this->user[$this->settings->uniq_username_field];
            } else {
                $_SESSION['username'] = $this->username = $this->user[$this->settings->auth_username_field];
            }

            $_SESSION['role'] = $this->role = $this->user['role'] ?? 'user';

            // TODO What??
            $_SESSION['view'] = $this->role = $this->user['role'] ?? 'user';

            // We might want to be able to store more static info about this user, like a profile array
            $_SESSION['user_id'] = $this->user['id'] ?? null;
            $_SESSION['profile'] = $this->user;

            return true;
        }

        $_SESSION['username'] = $this->username = null;
        $_SESSION['role'] = $this->role = null;
        $controller->flash("Sorry, registration failed. Please try again.");
        return false;

    }

    /**
     * @param $method
     * @return bool
     */
    public function api_login($method='GET') {


        $username = $_REQUEST['username'] ?? $_REQUEST['a'] ?? false;
        $password = $_REQUEST['password'] ?? $_REQUEST['b'] ?? false;

        if (!$username) $username = $_REQUEST['email'] ?? false;
        if (!$username) $username = $_REQUEST['phone'] ?? false;

        if (!$username and !$password) {
            $this->controller->abort(401, 'Invalid Login Credentials');
        }

        // There are no settings for this module so just use hard-coded authentication (dev!)
        if (empty($this->settings)) {
            if ($username == 'admin') {
                if ($password == 'password') {
                    $_SESSION['username'] = $this->username = $username;
                    $_SESSION['role'] = $this->role = 'admin';

                    return true;
                }
            }
            if ($username == 'user') {
                if ($password == 'password') {
                    $_SESSION['username'] = $this->username = $username;
                    $_SESSION['role'] = $this->role = 'user';
                    $_SESSION['user_id'] = 0;
                    $_SESSION['profile'] = false;
                    return true;
                }
            }
            $_SESSION['username'] = $this->username = null;
            $_SESSION['role'] = $this->role = null;
            $_SESSION['user_id'] = null;
            $_SESSION['profile'] = null;
            return false;
        }
        // Settings are used for user login and profile (dev!)
        if ((isset($this->settings->username)) && (isset($this->settings->password))) {
            if ($username == $this->settings->username) {
                if ($password == $this->settings->password) {
                    $_SESSION['username'] = $this->username = $this->settings->username;
                    $_SESSION['role'] = $this->role = 'user';
                    $_SESSION['user_id'] = 0;
                    $_SESSION['profile'] = $this->settings;
                    return true;
                }
            }
        }
        // Settings are used for admin login and profile (dev!)
        if ((isset($this->settings->admin_username)) && (isset($this->settings->admin_password))) {

            if ($username == $this->settings->admin_username) {
                if ($password == $this->settings->admin_password) {
                    $_SESSION['username'] = $this->username = $this->settings->admin_username;
                    $_SESSION['view'] = $this->view = $this->settings->admin_view ?? 'admin';
                    $_SESSION['role'] = $this->role = 'admin';
                    $_SESSION['user_id'] = 0;
                    $_SESSION['profile'] = $this->settings;
                    return true;
                }
            }
        }
        // Settings are used for readonly login and profile (dev!)
        if ((isset($this->settings->readonly_username)) && (isset($this->settings->readonly_password))) {

            if ($username == $this->settings->readonly_username) {
                if ($password == $this->settings->readonly_password) {
                    $_SESSION['username'] = $this->username = $this->settings->admin_username;
                    $_SESSION['view'] = $this->view = $this->settings->admin_view ?? 'readonly';
                    $_SESSION['role'] = $this->role = 'readonly';
                    $_SESSION['user_id'] = 0;
                    $_SESSION['profile'] = $this->settings;

                    return true;
                }
            }
        }

        if ( (isset($this->settings->auth_table)) && (isset($this->settings->auth_username_field)) && (isset($this->settings->auth_password_field))) {

            // TODO: We might also want to allow for fields like disabled, status or deleted_at

            $sql = sprintf("SELECT * FROM %s WHERE ? in (%s) and %s = ? LIMIT 1",
                $this->settings->auth_table, $this->settings->auth_username_field, $this->settings->auth_password_field);

            //dd([$sql, $username, $password]);
            $stmt = $this->controller->database->sql($sql, [$username, $password]);

            $this->user = $stmt->fetch();

            if ($this->user) {
                if (isset($this->settings->uniq_username_field)) {
                    $_SESSION['username'] = $this->username = $this->user[$this->settings->uniq_username_field];
                } else {
                    $_SESSION['username'] = $this->username = $this->user[$this->settings->auth_username_field];
                }
                $_SESSION['role'] = $this->role = $this->user['role'] ?? 'user';
                // TODO What??
                $_SESSION['view'] = $this->role = $this->user['view'] ?? 'user';

                // We might want to be able to store more static info about this user, like a profile array
                $_SESSION['user_id'] = $this->user['id'] ?? null;
                $_SESSION['profile'] = $this->user;

                return true;
            }
        }
        $_SESSION['username'] = $this->username = null;
        $_SESSION['role'] = $this->role = null;
        return false;
    }
}