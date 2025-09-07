<?php

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

// Function to recursively scan the directory and build the array
function scanDirectory($dir) {
    $result = [];

    // Get all files and directories
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $dir . DIRECTORY_SEPARATOR . $item;

        if (is_dir($path)) {
            // Recursively scan the directory
            $result[$item] = scanDirectory($path);
            if (empty($result[$item])) {
                // If it's an empty directory, just mark it as such
                $result[$item] = [];
            }
        } elseif (is_file($path) && (pathinfo($path, PATHINFO_EXTENSION) === 'json' || pathinfo($path, PATHINFO_EXTENSION) === 'html')) {
            // Read the file contents and store in the array
            $result[$item] = file_get_contents($path);
        }
    }

    return $result;
}

// Scan the target directory
$targetDir = __DIR__ . '/../pages/_domains/';
$directoryTree = scanDirectory($targetDir);

// Save the result to a JSON file
file_put_contents(__DIR__ . '/sitedata.json', json_encode($directoryTree, JSON_PRETTY_PRINT));

echo "\nDirectory scan completed. Data saved to sitedata.json.\n";


echo "\nUploading...\n";

// Database credentials
$host = 'localhost';
$dbname = 'yore';
$user = 'heidi';
$pass = 'Mermaid7!!';

try {
    // Connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare the SQL statement with placeholders
    $stmt = $pdo->prepare("
        INSERT INTO sitedata (domain, site, page, data, content)
        VALUES (:domain, :site, :page, :data, :content)
        ON DUPLICATE KEY UPDATE content = VALUES(content), data = VALUES(data)
    ");


    // Recursively loop through the array
    function processArray($domain, $site, $page, $data, $content, $stmt)
    { echo "===== INSERT\n";
        $stmt->execute([
            ':domain' => $domain,
            ':site' => $site,
            ':page' => $page,
            ':data' => $data,
            ':content' => $content
        ]);
    }

    // Traverse through the array and insert data into the table
    foreach ($directoryTree as $domain => $sites) { echo "=$domain\n";
        if (is_array($sites)) {
            foreach ($sites as $site => $pages) { echo "==$site\n";
                if (is_array($pages)) {
                    foreach ($pages as $page => $datas) { echo "===$page\n";
                        if (is_array($datas)) {
                            foreach ($datas as $data => $content) { echo "==== CONTENT\n";
                                    processArray($domain, $site, $page, $data, $content, $stmt);
                            }
                        } else {
                            // Handle the case where the array goes only 3 levels deep
                            processArray($domain, $site, $page, null, $datas, $stmt);
                        }
                    }
                } else {
                    // Handle the case where the array goes only 2 levels deep
                    processArray($domain, $site, '-', null, $pages, $stmt);
                }
            }
        } else {
            // Handle the case where the array goes only 1 level deep
            processArray($domain, '-', '-', null, $sites, $stmt);
        }
    }

    echo "Data successfully upserted into the database.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}


echo "\nDone\n";