<?php
namespace Modules\Admin\Traits;

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
     *
     * if $method == GET then return the view /views/login.html
     * if $method != GET then log in the user and, in this case, we just say Login Successful
     * Web Methods should always return a view, or the content a view would have provided
     */
    public function web_login($method = 'GET')
    {
        if ($method == 'GET') {
            // Get the directory of the current file (Module.php)
            $currentDirectory = __DIR__;

            // Build the relative path to the login.php file
            $viewFilePath = $currentDirectory . '/../views/login.html';

            // Read the contents of the file
            $content = file_get_contents($viewFilePath);

            // this needs to be in the view but..

            if ($this->controller->arg1 == 'nope') {

                $content .= "<h1>Invalid Login - Please try again</h1>";

            }

            return $content;
        } else {
            if ($this->api_login()) {

                header('Location: /module/admin/dashboard');
                return "Login Successful...";

            } else {
                header('Location: /module/admin/login/nope');
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
        header('Location: /module/admin/login');
        return "Logout Successful...";


    }

    /**
     * @param $method
     * @return string
     *
     */
    public function web_dashboard($method = 'GET')
    {
        if ($method == 'GET') {
            // Get the directory of the current file (Module.php)
            $currentDirectory = __DIR__;

            // Build the relative path to the login.php file
            $viewFilePath = $currentDirectory . '/../views/dashboard.html';

            // Read the contents of the file
            //$content = file_get_contents($viewFilePath);

// Open the domain's directory

            $home = $_SERVER['DOCUMENT_ROOT'] . '/../pages/' . '_domains/' . $this->controller->domain;
            $path = 'module/admin/dashboard';
            $curdir = $this->controller->domain;

            if ($this->controller->arg1) {
                $home .= '/' . $this->controller->arg1;
                $path .= '/' . $this->controller->arg1;
                $curdir .= '/' . $this->controller->arg1;
            }

            if ($this->controller->arg2) {
                $home .= '/' . $this->controller->arg2;
                $path .= '/' . $this->controller->arg2;
                $curdir .= '/' . $this->controller->arg2;
            }

            if ($this->controller->arg3) {
                $home .= '/' . $this->controller->arg3;
                $path .= '/' . $this->controller->arg3;
                $curdir .= '/' . $this->controller->arg3;
            }

            $files = scandir($home);

//            $content = "<h1>" . $home . "</h1>";
//            $content .= "<h1>" . $path . "</h1>";


            $content = "<h1>" . $curdir . "</h1>";

            $content .= "<span style='font-size:60%;'>TODO: Edit Theme | Switch Domains | Create Domain | Manage Accounts | Reports</span>";

// Start the unordered list
            $content .= "<table>
                <tr>
                    <th>Action</th>
                    <th>File/Dir</th>
                    <th>File Type</th>
                    <th>Site</th>
                    <th>Page</th>
                    <th>Theme</th>
                    <th>HTML File</th>


                </tr>";

            $lastSlashPosition = strrpos($path, '/');

// Truncate the string up to the last "/"
            $trimmedPath = substr($path, 0, $lastSlashPosition);

            $directories = '';

            if ($trimmedPath != 'module/admin') {
                $directories = "<tr></tr><td></td><td><strong>📁 <a href='/$trimmedPath'>..</a>/</strong></td><td>Parent Dir</td><td></td><td></td><td></td><td></td></tr>";
            }

            $filenames = '';

            // ✏️👁️📋🗑️💾🔄⚙️⬆️⬇️➕➖🔍ℹ️🔗🔒🔓❓⭐🖨️🔍➕🔍➖

            $icons = [
                'html' => '🌐',
                'css' => '🎨',
                'js' => '📜',
                'json' => '⚙️',
                'mp3' => '🎵',
                'mp4' => '🎥',
                'png' => '🖼️',
                'pdf' => '📕',
                'zip' => '📦',
                'py' => '🐍',
                'php' => '🐘',
                'md' => '✍️',
                'txt' => '📃',
                'wav' => '🎵',
                'jpg' => '🖼️',
                'jpeg' => '🖼️',
                'webp' => '🖼️',
                'gif' => '🖼️',
                'xls' => '📊',
                'xlsx' => '📊',
            ];

// Loop through each item in the directory
            foreach ($files as $file) {
                // Skip the current and parent directory entries
                if ($file === '.' || $file === '..') continue;
                $filespec = $home . '/' . $file;

                $ext = pathinfo($filespec, PATHINFO_EXTENSION);

                // Check if the item is a directory or a file
                if (is_dir($home . '/' . $file)) {
                    $directories .= "<tr><td></td><td><strong>📁 <a href='/$path/$file'>$file</a>/</strong></td><td>Sub Directory</td><td></td><td></td><td></td><td></td></tr>";
                } else {

                    $icon = $icons[strtolower($ext)] ?? '📄';

                    switch ($ext) {
                        case 'json':
                            $json = file_get_contents($filespec);

                            $edit = $view = $del = '';

                            try {

                                $data = json_decode($json);

                                if (isset($data->site) and isset($data->page)) {

                                    $site = $data->site;
                                    if ($site == 'default') $site = '';
                                    $page = $data->page;
                                    if ($page == 'home') $page = '';
                                    if (!empty($site) and !empty($page)) {
                                        $edit = "<a title='Edit HTML file' style='text-decoration: none;' href='/{$site}/{$page}/edit' target='_blank'>✏️</a>";
                                        $view = "<a title='View web page' style='text-decoration: none;' href='/{$site}/{$page}' target='_blank'>👁️</a>";
                                        $del = "<a disabled title='Delete web page (disabled)' style='text-decoration: none;' >🗑️</a>";
                                    }

                                }

                            } catch (\Exception $e) {

                            }

                            $filenames .= "<tr>
                                <td>$edit&nbsp;&nbsp;$view&nbsp;&nbsp;$del</td>
                                <td>$icon $file</td>";


                                if (!empty($data->site) and !empty($data->page)) {
                                    $filenames .= "
                                        <td>Web Page</td>
                                        <td>{$data->site}</td>
                                        <td>{$data->page}</td>
                                        <td>{$data->theme}</td>
                                        <td>{$data->view}</td>";
                                } else {
                                    $filenames .= "
                                        <td>Config File</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>";
                                }



                            $filenames .= "</tr>";


                            break;
                        default:
                            $filenames .= "<tr><td></td><td>$icon $file</td><td>Disk File</td><td></td><td></td><td></td><td></td></tr>";
                    }





                }
            }

            $content .= $directories . $filenames;

// Close the unordered list
            $content .= "</table>";

            return $content;
        }
    }
}



