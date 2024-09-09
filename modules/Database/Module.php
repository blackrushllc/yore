<?php
namespace Modules\Database;

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

    private $pass = 'Mermaid7!!';
    protected $port = "3306";

    protected $charset = 'utf8mb4';

    protected $pdo = false;

    /**
     *
     */
    public function __construct() {

        // Constructor is optional
        // Constructor in Parent does nothing right now, but all modules should call it anyway
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

    public function sql($sql, $params = []) {
        $this->init();
        if (gettype($params) != 'array') $params = [ $params ];
//
//        var_dump($sql);
//        var_dump($params);
//        exit;

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