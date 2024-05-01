<?php declare(strict_types=1);

namespace Stuba\Controllers;

use OpenApi\Attributes as OA;
use Pecee\SimpleRouter\SimpleRouter;
use Stuba\Db\DbAccess;
use PDO;

#[OA\Tag('User')]
class UserController
{
    private PDO $dbConnection;

    public function __construct()
    {
        $this->dbConnection = (new DbAccess())->getDbConnection();
    }

    public function getAllUsers()
    {

    }

    public function getUserById()
    {

    }

    public function createUser()
    {

    }

    public function updateUser()
    {

    }

    public function deleteUserById()
    {

    }
}