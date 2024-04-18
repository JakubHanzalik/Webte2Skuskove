<?php
class DbAccess
{
    private ?PDO $dbConnection = null;
    function __construct()
    {
        require 'config.php';

        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=UTF8', host, db);

        try {
            $this->dbConnection = new PDO($dsn, user, password);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getDbConnection()
    {
        return $this->dbConnection;
    }
}
