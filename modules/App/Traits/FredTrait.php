<?php

namespace Modules\App\Traits;

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

USA!! ♥️🤍💙
*/

/**
 *
 */
trait FredTrait
{

    public function fred_app_admin_view($site)
    {
        $role = $_SESSION['role'] ?? false;
        $user_id = $_SESSION['user_id'] ?? false;

        if (!$role or !$user_id) {
            $this->controller->abort(401, 'Invalid Login Credentials');
        }

        // THIS IS FOR DATABASE TABLES

        switch ($site) {
            case 'this_thing':
                $table = ' app_yw_that_thing';
                break;
            case 'forms':
                $table = ' app_yw_something_forms';
                break;
            default:
                $table = ' app_yw_' . $site; // i.e.  app_yw_something_something

        }

        // This applies to any view query
        if ($this->controller->name != 'deleted') {
            $where = 'WHERE deleted_at IS NULL ';
        } else {
            $where = 'WHERE deleted_at IS NOT NULL ';
        }

        if (($_SESSION['role'] == 'admin') or ($_SESSION['role'] == 'readonly') or ($_SESSION['role'] == 'user')) {

            // Admin or RO sees all
            switch ($table) {

                case 'app_something_something': // Example of a complex join

                    if ($this->controller->name != 'deleted') {
                        $where = 'WHERE ss.deleted_at IS NULL ';
                    } else {
                        $where = 'WHERE ss.deleted_at IS NOT NULL ';
                    }

                    $sql = "SELECT 
                    ss.id, u.`name`,  ss.`form_name`, ss.slug, ss.submission_id, ss.form_id, ss.post_data, s.id as required_form_id, u.`role` 
					FROM app_something_something ss
                    JOIN app_yw_users u on u.id = jf.user_id
                    $where
                    AND ss.disabled=0
                    AND ss.`role`=u.`role`
                    ORDER BY ss.id DESC
                    ";

                    break;
                default:

                    $sql = "SELECT * FROM $table 
                    $where
                    ORDER BY id DESC";
            }

        } else { // If we are some other role
            switch ($table) {
                case 'app_yw_something_something':

                    // Filter by role
                    $where .= "AND role='$role' ";
                    $sql = "SELECT * FROM $table 
                    $where
                    ORDER BY form_name DESC";
                    break;

                default:
                    // Don't allow Trash
                    $sql = "SELECT * FROM $table 
                    WHERE deleted_at IS NULL
                    ORDER BY id DESC";
            }

        }

        $stmt = $this->controller->database->sql($sql);
        $rows = $stmt->fetchAll();

        // Here is a DUMB way to improve the headers that are shown

        $headers_gway = [
            'category_name' => 'Category',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'setting_name' => 'Setting',
            'username' => 'User Name',
            'email' => 'Email',
            'city' => 'City',
            'role' => 'Role',
            'county' => 'County',
            'language' => 'Language',
            'description' => 'Description',
            'disabled' => 'Disabled',
            'enabled' => 'Enabled',
            'created_at' => 'Created',
            'updated_at' => 'Updated',
            'deleted_at' => 'Deleted',
            '_id' => ' ID',
            'id' => 'Actions',
            'state' => 'St.',
            'zip' => 'Zip',
            'phone' => 'Phone',
            'address' => 'Address',
            'addr2' => 'Unit',
            'etc.' => 'etc.',
            'etc..' => 'etc..',
        ];

        if (count($rows) == 0) {
            return 'No Rows';
        } else {
            return $this->controller->database->arrayToHtmlTable($rows, $headers_gway);
        }
    }


    public function fred_app_delete($id)
    {
        // TODO admin this
        $site = $this->controller->site;

        switch ($site) {

            case 'something':
                $table = 'fhhc_something_something';
                break;
            default:
                $table = 'app_yw_' . $site;

        }
        try {
            if ($this->controller->arg2 == 'delete%20forever') {
                $this->controller->database->sql("DELETE FROM $table where ID=?", $id, true); // nocatch
                return "$table $id has been permanently deleted!";
            } else {
                $this->controller->database->sql("UPDATE $table SET deleted_at=NOW() where ID=?", $id, true);
                return "$table $id has been marked as deleted!";
            }
        } catch (\Exception $e) {
            return "could not delete $id from $table! " . $e->getMessage();
        }
    }
    public function fred_app_uploads()
    {
        $id = $this->controller->arg1;

        $user_id = $_SESSION['user_id'];

        if (!$id or !$user_id) {
            $this->controller->abort(500, 'Invalid ID');
        }

        //$id=34;
        $sql = "
            SELECT * FROM app_yw_uploaded_files
            WHERE reference = ?
            ORDER BY id DESC
            ";

        $stmt = $this->controller->database->sql($sql, [$id]);
        $rows = $stmt->fetchAll();
        $html = "";

        if (empty($rows)) {
            return "<h4><small><small>No additional files have been uploaded for this client</small></small></h4>";
        }

        foreach ($rows as $row) {

            $view_url =
                "#"; // TODO

            $html .= '<li><a href="/api/app/file/' . $row['id'] . '">' . $row['file_name'] . '</a></li>';

        }

        $output = "Uploaded files:<ul style='width:450px;overflow:hidden;'>";

        if (!empty($html)) {
            $output .= $html;
        }

        $output .= "</ul>";

        return $output;

    }

}