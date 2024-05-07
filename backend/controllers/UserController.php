<?php

declare(strict_types=1);

namespace Stuba\Controllers;

use OpenApi\Attributes as OA;
use Pecee\SimpleRouter\SimpleRouter;
use Stuba\Db\DbAccess;
use PDO;
use Stuba\Exceptions\APIException;
use Stuba\Handlers\Jwt\JwtHandler;
use Stuba\Handlers\User\GetUserByIdHandler;
use Stuba\Handlers\User\GetUserByUsernameHandler;
use Stuba\Models\User\GetAllUsers\GetAllUsersResponseModel;
use Stuba\Models\User\GetUser\GetUserResponseModel;
use Stuba\Models\User\CreateUser\CreateUserRequestModel;
use Stuba\Models\User\CreateUser\CreateUserResponseModel;
use Stuba\Models\User\UpdateUser\UpdateUserRequestModel;

#[OA\Tag('User')]
class UserController
{
    private PDO $dbConnection;
    private JwtHandler $jwtHandler;
    private GetUserByIdHandler $getUserByIdHandler;
    private GetUserByUsernameHandler $getUserByUsernameHandler;

    public function __construct()
    {
        $this->dbConnection = (new DbAccess())->getDbConnection();
        $this->jwtHandler = new JwtHandler();
        $this->getUserByIdHandler = new GetUserByIdHandler();
        $this->getUserByUsernameHandler = new GetUserByUsernameHandler();
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

        SimpleRouter::response()->httpCode(200);
        SimpleRouter::response()->json($response);
    }

    #[OA\Get(path: '/api/user/{id}', tags: ['User'])]
    #[OA\Parameter(name: "id", in: 'path', required: true, description: "User id", example: 8, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Get all users', content: new OA\JsonContent(ref: '#/components/schemas/GetUserResponseModel'))]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 404, description: 'User not found')]
    public function getUserById(int $id)
    {
        $user = $this->getUserByIdHandler->handle($id);

        if (is_null($user)) {
            throw new APIException('User not found', 404);
        }

        $responseModel = GetUserResponseModel::createFromModel([
            'username' => $user->username,
            'name' => $user->name,
            'surname' => $user->surname,
            'role' => $user->role
        ]);

        SimpleRouter::response()->httpCode(200);
        SimpleRouter::response()->json($responseModel);
    }

    #[OA\Put(path: '/api/user', tags: ['User'])]
    #[OA\RequestBody(description: 'Create user', required: true, content: new OA\JsonContent(ref: '#/components/schemas/CreateUserRequestModel'))]
    #[OA\Response(response: 200, description: 'User created', content: new OA\JsonContent(ref: '#/components/schemas/CreateUserResponseModel'))]
    #[OA\Response(response: 400, description: 'Invalid input')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 409, description: 'User already exists')]
    public function createUser()
    {
        $model = CreateUserRequestModel::createFromModel(SimpleRouter::request()->getInputHandler()->all());

        if (!$model->isValid()) {
            throw new APIException(implode($model->getErrors()), 400);
        }

        $user = $this->getUserByUsernameHandler->handle($model->username);

        if (!is_null($user)) {
            throw new APIException('User already exists', 409);
        }

        $responseModel = null;

        $this->dbConnection->beginTransaction();

        try {
            $query = "INSERT INTO Users (username, password, name, surname, role) VALUES (:username, :password, :name, :surname, :role)";
            $stmt = $this->dbConnection->prepare($query);
            $stmt->bindValue(":username", $model->username);
            $stmt->bindValue(":password", password_hash($model->password, PASSWORD_DEFAULT));
            $stmt->bindValue(":name", $model->name);
            $stmt->bindValue(":surname", $model->surname);
            $stmt->bindValue(":role", $model->role->value, PDO::PARAM_INT);
            $stmt->execute();

            $responseModel = CreateUserResponseModel::createFromModel([
                'id' => (int) $this->dbConnection->lastInsertId()
            ]);

            $this->dbConnection->commit();
        } catch (APIException $e) {
            $this->dbConnection->rollBack();
            throw $e;
        }

        SimpleRouter::response()->httpCode(200);
        SimpleRouter::response()->json($responseModel);
    }

    #[OA\Post(path: '/api/user/{id}', tags: ['User'])]
    #[OA\Parameter(name: "id", in: 'path', required: true, description: "User id", example: 5, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(description: 'Update user', required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdateUserRequestModel'))]
    #[OA\Response(response: 200, description: 'User updated successfully')]
    #[OA\Response(response: 400, description: 'Invalid input')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 404, description: 'User not found')]
    public function updateUser(int $id)
    {
        $inputData = SimpleRouter::request()->getInputHandler()->all();
        $model = UpdateUserRequestModel::createFromModel($inputData);

        if (!$model->isValid()) {
            throw new APIException(implode($model->getErrors()), 400);
        }

        $user = $this->getUserByIdHandler->handle($id);

        if (is_null($user)) {
            throw new APIException('User not found', 404);
        }

        $this->dbConnection->beginTransaction();

        try {
            $updateQuery = "UPDATE Users SET password = :password, name = :name, surname = :surname, role = :role WHERE id = :id";
            $stmt = $this->dbConnection->prepare($updateQuery);
            $stmt->bindValue(":password", password_hash($model->password, PASSWORD_DEFAULT));
            $stmt->bindValue(":name", $model->name);
            $stmt->bindValue(":surname", $model->surname);
            $stmt->bindValue(":role", $model->role->value);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            $this->dbConnection->commit();
        } catch (APIException $e) {
            $this->dbConnection->rollBack();
            throw $e;
        }

        SimpleRouter::response()->httpCode(200);
        SimpleRouter::response()->json(['message' => 'User updated successfully']);
    }

    #[OA\Delete(path: '/api/user/{id}', tags: ['User'])]
    #[OA\Parameter(name: "id", in: 'path', required: true, description: "User id", example: 5, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'User deleted successfully')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 404, description: 'User not found')]
    public function deleteUserById(int $id)
    {
        $user = $this->getUserByIdHandler->handle($id);

        if (is_null($user)) {
            throw new APIException('User not found', 404);
        }

        $this->jwtHandler->deleteRefreshToken($user->username);

        $this->dbConnection->beginTransaction();

        try {
            $deleteQuery = "DELETE FROM Users WHERE id = :id";
            $stmt = $this->dbConnection->prepare($deleteQuery);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->dbConnection->commit();
        } catch (APIException $e) {
            $this->dbConnection->rollBack();
            throw $e;
        }
        SimpleRouter::response()->httpCode(200);
        SimpleRouter::response()->json(['message' => 'User deleted successfully']);
    }
}
