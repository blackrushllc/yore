<?php
namespace Modules\Database;

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

# This is the module code for the Chsapp module 🔥😂🤑 😠🤔🧵👈😍💥

use App\Controller;
use App\Modules;

/**
 *
 */
class Module extends Modules {

    protected $host = 'localhost';
    protected $db   = 'yore';

    protected $user = 'heidi';

    private $pass = 'xxxxxxxx';
    protected $port = "3306";

    protected $charset = 'utf8mb4';

    protected $pdo = false;

    public $row, $rows;

    /**
     *
     */
    public function __construct() {

        // Constructor is optional
        // Constructor in Parent does nothing right now, but all modules should call it anyway
        $this->dir = __DIR__;
        parent::__construct();
    }

    function init() {
        if ($this->pdo) {

            return;

        }
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $dsn = "mysql:host=$this->host;dbname=$this->db;charset=$this->charset;port=$this->port";

        // TODO: Only connect when database is first used

        try {
            $this->pdo = new \PDO($dsn, $this->user, $this->pass, $options);
        } catch (\PDOException $e) {
            //throw new \PDOException($e->getMessage(), (int)$e->getCode());
            $this->controller->abort(500, $e->getMessage());
        }

    }

    public function sql($sql, $params = [], $nocatch=false) {
        $this->init();
        if (gettype($params) != 'array') $params = [ $params ];
//
//        var_dump($sql);
//        var_dump($params);
//        exit;

        $params = array_values($params);

        if ($nocatch) {
            $stmt = $this->pdo->prepare($sql);
            $ret = $stmt->execute($params);
            if ($ret) {
                return $stmt;
            } else {
                return $ret; // false
            }
        } else {
            try {

                $stmt = $this->pdo->prepare($sql);
                $ret = $stmt->execute($params);
                if ($ret) {
                    return $stmt;
                } else {
                    return $ret; // false
                }

            } catch (\PDOException $e) {
                $this->controller->abort(500, $e->getMessage());
            }
        }


    }

    public function lastInsertId() {
        $lastId = $this->pdo->lastInsertId();
    }

    // This should NOT be here, this should be in the CHS module because it is specific to that module

    function uploadFileToDatabase($file, $code, $hospital_name) {
        // MySQL database connection
        $this->init();
        // Extract file details
        $fileName = basename($file['name']);
        $fileSize = $file['size'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $fileData = file_get_contents($file['tmp_name']);  // Read binary data of file

        // Prepare an SQL statement
        $stmt = $this->pdo->prepare("INSERT INTO chs_uploaded_files (file_name, file_extension, file_size, file_data, code, hospital_name) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$fileName, $fileExtension, $fileSize, $fileData, $code, $hospital_name]);


    }


    function retrieveFileFromDatabase($fileId, $code, $directory) {

        $this->init();

        try {
            // Prepare a SQL statement to fetch the file by id
            $stmt = $this->pdo->prepare("SELECT file_name, file_extension, file_data FROM chs_uploaded_files WHERE id = ? AND code = ?");
            $ret = $stmt->execute([ $fileId, $code ]);

            // Fetch the file record
            $file = $stmt->fetchObject();

            if ($file) {
                // Ensure the directory exists
                if (!is_dir($directory)) {
                    mkdir($directory, 0777, true);  // Create directory if it doesn't exist
                }

                // Define the full path to save the file
                $filePath = $directory . '/' . $file->file_name;

                // Write binary data to a file
                if (file_put_contents($filePath, $file->file_data)) {
                    return $filePath;  // Success
                } else {
                    $this->controller->abort(500, "Failed to write file.");
                    return false;  // Failure
                }
            } else {
                $this->controller->abort(500, "No file found with your code and ID: " . $fileId);
                return false;  // Failure
            }
        } catch (PDOException $e) {
            $this->controller->abort(500, "Error retrieving file: " . $e->getMessage() );
            return false;  // Failure
        }
    }
    // This should NOT be here, this should be in the ARC module because it is specific to that module

    function uploadFHHCFileToDatabase($file, $reference, $category) {
        // MySQL database connection
        $this->init();
        // Extract file details
        $fileName = basename($file['name']);
        $fileSize = $file['size'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $fileData = file_get_contents($file['tmp_name']);  // Read binary data of file

        // Prepare an SQL statement
        $stmt = $this->pdo->prepare("INSERT INTO fhhc_uploaded_files (file_name, file_extension, file_size, file_data, reference, category_name) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$fileName, $fileExtension, $fileSize, $fileData, $reference, $category]);


    }

    // This should NOT be here, this should be in the App module because it is specific to that module

    function uploadAppFileToDatabase($file, $reference, $category) {
        // MySQL database connection
        $this->init();
        // Extract file details
        $fileName = basename($file['name']);
        $fileSize = $file['size'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $fileData = file_get_contents($file['tmp_name']);  // Read binary data of file

        // Prepare an SQL statement
        $stmt = $this->pdo->prepare("INSERT INTO app_yw_uploaded_files (file_name, file_extension, file_size, file_data, reference, category_name) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$fileName, $fileExtension, $fileSize, $fileData, $reference, $category]);

    }

    // This should NOT be here, this should be in the App module because it is specific to that module

    function uploadLCCFileToDatabase($file, $reference, $category) {
        // MySQL database connection
        $this->init();
        // Extract file details
        $fileName = basename($file['name']);
        $fileSize = $file['size'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $fileData = file_get_contents($file['tmp_name']);  // Read binary data of file

        // Prepare an SQL statement
        $stmt = $this->pdo->prepare("INSERT INTO lcc_uploaded_files (file_name, file_extension, file_size, file_data, reference, category_name) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$fileName, $fileExtension, $fileSize, $fileData, $reference, $category]);

    }

    // This should NOT be here, this should be in the ARC module because it is specific to that module

    function uploadARCFileToDatabase($file, $reference, $category) {
        // MySQL database connection
        $this->init();
        // Extract file details
        $fileName = basename($file['name']);
        $fileSize = $file['size'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $fileData = file_get_contents($file['tmp_name']);  // Read binary data of file

        // Prepare an SQL statement
        $stmt = $this->pdo->prepare("INSERT INTO arc_uploaded_files (file_name, file_extension, file_size, file_data, reference, category_name) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$fileName, $fileExtension, $fileSize, $fileData, $reference, $category]);


    }

    function retrieveFHHCFileFromDatabase($fileId, $directory) {

        $this->init();

        try {
            // Prepare a SQL statement to fetch the file by id
            $stmt = $this->pdo->prepare("SELECT file_name, file_extension, file_data FROM fhhc_uploaded_files WHERE id = ?");
            $ret = $stmt->execute([ $fileId ]);

            // Fetch the file record
            $file = $stmt->fetchObject();

            if ($file) {
                // Ensure the directory exists
                if (!is_dir($directory)) {
                    mkdir($directory, 0777, true);  // Create directory if it doesn't exist
                }

                // Define the full path to save the file
                $filePath = $directory . '/' . $file->file_name;

                // Write binary data to a file
                if (file_put_contents($filePath, $file->file_data)) {
                    return $filePath;  // Success
                } else {
                    $this->controller->abort(500, "Failed to write file.");
                    return false;  // Failure
                }
            } else {
                $this->controller->abort(500, "No file found with ID: " . $fileId);
                return false;  // Failure
            }
        } catch (PDOException $e) {
            $this->controller->abort(500, "Error retrieving file: " . $e->getMessage() );
            return false;  // Failure
        }
    }
    function retrieveLCCFileFromDatabase($fileId, $directory) {

        $this->init();

        try {
            // Prepare a SQL statement to fetch the file by id
            $stmt = $this->pdo->prepare("SELECT file_name, file_extension, file_data FROM lcc_uploaded_files WHERE id = ?");
            $ret = $stmt->execute([ $fileId ]);

            // Fetch the file record
            $file = $stmt->fetchObject();

            if ($file) {
                // Ensure the directory exists
                if (!is_dir($directory)) {
                    mkdir($directory, 0777, true);  // Create directory if it doesn't exist
                }

                // Define the full path to save the file
                $filePath = $directory . '/' . $file->file_name;

                // Write binary data to a file
                if (file_put_contents($filePath, $file->file_data)) {
                    return $filePath;  // Success
                } else {
                    $this->controller->abort(500, "Failed to write file.");
                    return false;  // Failure
                }
            } else {
                $this->controller->abort(500, "No file found with ID: " . $fileId);
                return false;  // Failure
            }
        } catch (PDOException $e) {
            $this->controller->abort(500, "Error retrieving file: " . $e->getMessage() );
            return false;  // Failure
        }
    }


    function fred_fetch($sql) {
        $stmt = $this->sql($sql);
        $this->row = $this->rows = $stmt->fetch();
        //dd($this->row);
        return $this->row ? count($this->row) : 0; // Number of fields in row or 0 if no row
    }

    function fred_update($sql) {
        $this->sql($sql);
        return 1; // Number of fields in row or 0 if no row
    }

    function fred_field($name) {
        if (empty($this->row)) return '';
        if ($this->row[$name] == '1999-12-31')  return '';
            else
        return $this->row[$name] ?? '';
    }

    function fred_browse($table) {
        return $this->fred_select('SELECT * FROM ' . $table);
    }

    function fred_select($sql) {
        $stmt = $this->sql($sql);

        $rows = $stmt->fetchAll();

        $output = "";

        if (count($rows) == 0) {
            $output = 'No Rows';
        }

        $output .= $this->arrayToHtmlTable($rows);

        $output .= "<script>
        
        $(document).ready(function() {
            $('#myTable').DataTable();
        });
        
        </script>";

        return $output;

    }

    function fred_options($sql, $selected=null) {

        if (is_array($sql)) {
            $selected = $sql[1];
            $sql = $sql[0];
        }

        $stmt = $this->sql($sql);

        $rows = $stmt->fetchAll();

        $html = "";

        if (count($rows) == 0) {
            $html = "<!--No Rows for $sql -->";
        }

        // Loop through each row
        $ctr = 0;
        foreach ($rows as $row) {
            $id = $row['id'] ?? $ctr;
            $name = $row['name'] ?? "No Name Option $ctr";
            if ($id === $selected) {
                $html .= "<option selected class='option_$id' value='$id'>$name</option>";
            } else {
                $html .= "<option class='option_$id' value='$id'>$name</option>";
            }
            $ctr++;
        }
        return $html;
    }

    function snakeToTitle($string) {
        // Replace underscores with spaces
        $string = str_replace('_', ' ', $string);
        // Capitalize the first letter of each word
        return ucwords($string);
    }

    function arrayToHtmlTable($data, $headers = false) {
        if (empty($data)) {
            return "<p>No data available</p>";
        }
//dd($data);
        // Start table and table headers
        $html = "\n<table id='myTable' border='0' cellpadding='2' cellspacing='0'>";
        $html .= "<thead>\n<tr>";

        // Get the headers from the keys of the first row
        $ctr = 0;
        foreach (array_keys(reset($data)) as $header) {
            if ($headers === true) $header = $this->snakeToTitle($header);
            if (gettype($headers) == 'array') {
                $header = str_replace(array_keys($headers), array_values($headers), $header);
            }
            $html .= "<th class='th_$ctr'>" . htmlspecialchars($header) . "</th>";
            $ctr++;
        }
        $html .= "</tr>\n</thead><tbody>\n";

        // Loop through each row

        foreach ($data as $row) {
            $ctr = 0;
            $html .= "<tr>";
            foreach ($row as $cell) {
                $html .= "<td class='td_$ctr'>" . htmlspecialchars($cell ?? '') . "</td>";
                $ctr++;
            }
            $html .= "</tr>\n";
        }

        $html .= "</tbody></table>";
        return $html;
    }

    public function fixDate($d) {
        // Check if it's already in yyyy-mm-dd format and valid
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
            $date = \DateTime::createFromFormat('Y-m-d', $d);
            if ($date && $date->format('Y-m-d') === $d) {
                return $d;
            }
        }

        // Check if it's in mm/dd/yyyy format
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $d)) {
            $date = \DateTime::createFromFormat('m/d/Y', $d);
            if ($date) {
                return $date->format('Y-m-d');
            }
        }

        // Otherwise, return fallback
        return '1999-12-31';
    }



}