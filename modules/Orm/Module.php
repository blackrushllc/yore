<?php
namespace Modules\Orm;

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

# Minimal Orm module (subset of Database)

use App\Modules;
use Modules\Orm\Core\DB;
use Modules\Orm\Drivers\PdoMySqlDriver;
use Modules\Orm\Drivers\PdoSqliteDriver;

class Module extends Modules {

    // Get these values from env.json in the current domain folder or from the global env.json in the root folder if not found in the domain folder
    protected $host = 'localhost';
    protected $database = 'yore';
    protected $username = 'username';
    private $password = 'password';
    protected $port = "3306";
    protected $charset = 'utf8mb4';
    protected $pdo = false;

    public $row, $rows;

    public function __construct() {
        // Keep behavior consistent with other modules
        $this->dir = __DIR__;
        parent::__construct();
    }

    public function init() {
        if ($this->pdo) {
            return;
        }
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];

        // Try to load module config (modules/Orm/config/orm.php)
        $configPath = __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'orm.php';
        if (file_exists($configPath)) {
            $cfg = include $configPath;
            if (is_array($cfg) && isset($cfg['connections']) && is_array($cfg['connections'])) {
                $default = $cfg['default'] ?? 'default';
                foreach ($cfg['connections'] as $name => $c) {
                    $driverName = strtolower((string)($c['driver'] ?? 'mysql'));
                    $dsn  = (string)($c['dsn'] ?? '');
                    $user = $c['user'] ?? null;
                    $pass = $c['pass'] ?? null;
                    $opts = is_array($c['options'] ?? null) ? $c['options'] : [];
                    try {
                        $pdo = new \PDO($dsn, $user, $pass, $opts + $options);
                        if ($driverName === 'sqlite') {
                            $drv = new PdoSqliteDriver($pdo);
                        } else {
                            $drv = new PdoMySqlDriver($pdo);
                        }
                        DB::register((string)$name, $drv, $name === $default);
                        if ($name === $default) {
                            $this->pdo = $pdo; // keep for legacy methods below
                        }
                    } catch (\PDOException $e) {
                        // continue to fallback if needed
                        $this->controller->log('ORM init connection failed: ' . $e->getMessage());
                    }
                }
                if ($this->pdo) {
                    return; // registered via config
                }
            }
        }

        // Fallback: Read per-tenant DB config from controller settings (if present)
        $this->host = $this->controller->settings['database_module_host'] ?? $this->host;
        $this->database = $this->controller->settings['database_module_database'] ?? $this->database;
        $this->port = $this->controller->settings['database_module_port'] ?? $this->port;
        $this->charset = $this->controller->settings['database_module_charset'] ?? $this->charset;
        $this->username = $this->controller->settings['database_module_username'] ?? $this->username;
        $this->password = $this->controller->settings['database_module_password'] ?? $this->password;

        $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset};port={$this->port}";
        try {
            $this->pdo = new \PDO($dsn, $this->username, $this->password, $options);
            // Register default connection for ORM
            $drv = new PdoMySqlDriver($this->pdo);
            DB::register('default', $drv, true);
        } catch (\PDOException $e) {
            $this->controller->abort(500, $e->getMessage());
        }
    }

    public function sql($sql, $params = [], $nocatch = false) {
        $this->init();
        if (gettype($params) !== 'array') {
            $params = [$params];
        }
        $params = array_values($params);

        if ($nocatch) {
            $stmt = $this->pdo->prepare($sql);
            $ret = $stmt->execute($params);
            return $ret ? $stmt : $ret; // false on failure
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            $ret = $stmt->execute($params);
            return $ret ? $stmt : $ret; // false on failure
        } catch (\PDOException $e) {
            $this->controller->abort(500, $e->getMessage());
        }
    }

    public function lastInsertId() {
        if ($this->pdo) {
            $lastId = $this->pdo->lastInsertId();
        }
    }
}
