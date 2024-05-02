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

    #[OA\Get(path: '/api/user', tags: ['User'])]
    #[OA\Response(response: 200, description: "'Get all users", content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'array', items: new OA\Items(ref: '#/components/schemas/GetAllUsersResponseModel'))))]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function getAllUsers()
    {

    }

    #[OA\Get(path: '/api/user/{id}', tags: ['User'])]
    #[OA\Parameter(name: "id", in: 'path', required: true, description: "User id", example: 8, schema: new OA\Schema(type: 'int'))]
    #[OA\Response(response: 200, description: 'Get all users', content: new OA\JsonContent(ref: '#/components/schemas/GetUserResponseModel'))]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function getUserById()
    {

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