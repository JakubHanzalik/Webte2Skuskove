<?php declare(strict_types=1);

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

        SimpleRouter::response()->json($response)->httpCode(200);
    }

    #[OA\Get(path: '/api/user/{id}', tags: ['User'])]
    #[OA\Parameter(name: "id", in: 'path', required: true, description: "User id", example: 8, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Get all users', content: new OA\JsonContent(ref: '#/components/schemas/GetUserResponseModel'))]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function getUserById(int $id)
    {
        $user = $this->getUserByIdHandler->handle($id);

        if (is_null($user)) {
            SimpleRouter::response()->httpCode(404);
            return;
        }

        $responseModel = GetUserResponseModel::createFromModel([
            'username' => $user->username,
            'name' => $user->name,
            'surname' => $user->surname,
            'role' => $user->role
        ]);

        SimpleRouter::response()->json($responseModel)->httpCode(200);
    }

    #[OA\Put(path: '/api/user', tags: ['User'])]
    #[OA\RequestBody(description: 'Create user', required: true, content: new OA\JsonContent(ref: '#/components/schemas/CreateUserRequestModel'))]
    #[OA\Response(response: 200, description: 'User created', content: new OA\JsonContent(ref: '#/components/schemas/CreateUserResponseModel'))]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function createUser()
    {
        $inputData = SimpleRouter::request()->getInputHandler()->all();
        $model = CreateUserRequestModel::createFromModel($inputData);

        $user = $this->getUserByUsernameHandler->handle($model->username);

        if (!is_null($user)) {
            throw new APIException('User already exists', 409);
        }

        // Insert the new user into the database
        $query = "INSERT INTO Users (username, password, name, surname, role) VALUES (:username, :password, :name, :surname, :role)";
        $stmt = $this->dbConnection->prepare($query);
        $stmt->bindValue(":username", $model->username);
        $stmt->bindValue(":password", password_hash($model->password, PASSWORD_DEFAULT));
        $stmt->bindValue(":name", $model->name);
        $stmt->bindValue(":surname", $model->surname);
        $stmt->bindValue(":role", $model->role->value, PDO::PARAM_INT);
        $stmt->execute();

        $userId = $this->dbConnection->lastInsertId();
        $userData = ['id' => (int) $userId];  // Prepare user data as an array
        $responseModel = CreateUserResponseModel::createFromModel($userData);

        SimpleRouter::response()->json($responseModel)->httpCode(200);

    }

    #[OA\Post(path: '/api/user/{id}', tags: ['User'])]
    #[OA\Parameter(name: "id", in: 'path', required: true, description: "User id", example: 5, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(description: 'Update user', required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdateUserRequestModel'))]
    #[OA\Response(response: 200, description: 'User updated successfully')]
    #[OA\Response(response: 304, description: 'No changes made to the user')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function updateUser(int $id)
    {
        $inputData = SimpleRouter::request()->getInputHandler()->all();
        $model = UpdateUserRequestModel::createFromModel($inputData);

        $user = $this->getUserByIdHandler->handle($id);

        if (is_null($user)) {
            SimpleRouter::response()->json(['message' => 'User not found'])->httpCode(404);
            return;
        }

        $updateQuery = "UPDATE Users SET username = :username, password = :password, name = :name, surname = :surname, role = :role WHERE id = :id";
        $stmt = $this->dbConnection->prepare($updateQuery);
        $stmt->bindValue(":username", $model->username);
        $stmt->bindValue(":password", password_hash($model->password, PASSWORD_DEFAULT));
        $stmt->bindValue(":name", $model->name);
        $stmt->bindValue(":surname", $model->surname);
        $stmt->bindValue(":role", $model->role->value);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            SimpleRouter::response()->httpCode(200);
        } else {
            throw new APIException('No changes made to the user', 304);
        }

    }

    #[OA\Delete(path: '/api/user/{id}', tags: ['User'])]
    #[OA\Parameter(name: "id", in: 'path', required: true, description: "User id", example: 5, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Delete user')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 404, description: 'User not found')]
    public function deleteUserById(int $id)
    {
        $user = $this->getUserByIdHandler->handle($id);

        if (is_null($user)) {
            SimpleRouter::response()->json(['message' => 'User not found'])->httpCode(404);
            return;
        }

        $this->jwtHandler->deleteRefreshToken($user->username);

        $deleteQuery = "DELETE FROM Users WHERE id = :id";
        $stmt = $this->dbConnection->prepare($deleteQuery);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            SimpleRouter::response()->json(['message' => 'User deleted successfully'])->httpCode(200);
        } else {
            throw new APIException('Failed to delete user', 500);
        }

    }
}