<?php declare(strict_types=1);

namespace Stuba\Handlers\User;

use PDO;
use Stuba\Db\DbAccess;
use Stuba\Handlers\User;

class GetUserByUsernameHandler
{
    private PDO $dbConnection;

    public function __construct()
    {
        $this->dbConnection = (new DbAccess())->getDbConnection();
    }

    public function handle(string $username): UserModel|null
    {
        $query = "SELECT * FROM Users WHERE username = :username";
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(":username", $username);
        $statement->setFetchMode(PDO::FETCH_CLASS, UserModel::class);
        $statement->execute();
        if ($statement->rowCount() > 0) {
            return $statement->fetch();
        } else {
            return null;
        }
    }
}