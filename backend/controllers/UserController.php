<?php declare(strict_types=1);

namespace Stuba\Controllers;

use OpenApi\Attributes as OA;
use Pecee\SimpleRouter\SimpleRouter;
use Stuba\Db\DbAccess;
use PDO;
use Stuba\Models\User\GetAllUsers\GetAllUsersResponseModel;
use Stuba\Models\User\GetUser\GetUserResponseModel;

#[OA\Tag('User')]
class UserController
{
    private PDO $dbConnection;

    public function __construct()
    {
        $this->dbConnection = (new DbAccess())->getDbConnection();
    }

    #[OA\Get(path: '/api/user', tags: ['User'])]
    #[OA\Response(response: 200, description: "'Get all users", content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'array', items: new OA\Items(ref: '#/components/schemas/GetAllUsersResponseModel'))))]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function getAllUsers()
    {
        $query =
            "SELECT 
                u.id AS id, 
                u.username AS username, 
                u.role AS role
            FROM Users u";
        $statement = $this->dbConnection->prepare($query);

        $statement->execute();
        $response = $statement->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, GetAllUsersResponseModel::class);

        SimpleRouter::response()->json($response)->httpCode(200);
    }

    #[OA\Get(path: '/api/user/{id}', tags: ['User'])]
    #[OA\Parameter(name: "id", in: 'path', required: true, description: "User id", example: 8, schema: new OA\Schema(type: 'int'))]
    #[OA\Response(response: 200, description: 'Get all users', content: new OA\JsonContent(ref: '#/components/schemas/GetUserResponseModel'))]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function getUserById(int $id)
    {
        $userQuery =
            "SELECT 
            u.username AS username, 
            u.name AS name,
            u.surname AS surname,
            u.role AS role
        FROM Users u
        WHERE u.id = :id";

        $stmt = $this->dbConnection->prepare($userQuery);
        $stmt->bindParam(':id', $id);
        $stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, GetUserResponseModel::class);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            SimpleRouter::response()->httpCode(404);
            return;
        } else {
            $response = $stmt->fetch();
            SimpleRouter::response()->json($response)->httpCode(200);
        }
    }

    #[OA\Put(path: '/api/user', tags: ['User'])]
    #[OA\RequestBody(description: 'Create user', required: true, content: new OA\JsonContent(ref: '#/components/schemas/CreateUserRequestModel'))]
    #[OA\Response(response: 200, description: 'User created', content: new OA\JsonContent(ref: '#/components/schemas/CreateUserResponseModel'))]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function createUser()
    {

    }

    #[OA\Post(path: '/api/user/{id}', tags: ['User'])]
    #[OA\Parameter(name: "id", in: 'path', required: true, description: "User id", example: 5, schema: new OA\Schema(type: 'int'))]
    #[OA\RequestBody(description: 'Update user', required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdateUserRequestModel'))]
    #[OA\Response(response: 200, description: 'Update user')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function updateUser()
    {

    }

    #[OA\Delete(path: '/api/user/{id}', tags: ['User'])]
    #[OA\Parameter(name: "code", in: 'path', required: true, description: "User id", example: 4, schema: new OA\Schema(type: 'int'))]
    #[OA\Response(response: 200, description: 'Delete user')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 404, description: 'User not found')]
    public function deleteUserById()
    {

    }
}