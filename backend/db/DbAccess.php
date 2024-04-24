<?php

namespace Stuba\Db;

use PDO;
use PDOException;

use Stuba\Db\DBConfig;

class DbAccess
{
    private ?PDO $dbConnection = null;
    function __construct()
    {

        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=UTF8', DBConfig::host, DBConfig::db);

        try {
            $this->dbConnection = new PDO($dsn, DBConfig::user, DBConfig::password);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getDbConnection(): ?PDO
    {
        return $this->dbConnection;
    }
}
