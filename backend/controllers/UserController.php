<?php declare(strict_types=1);

namespace Stuba\Controllers;

use OpenApi\Attributes as OA;
use Pecee\SimpleRouter\SimpleRouter;
use Stuba\Db\DbAccess;
use PDO;
use Stuba\Exceptions\APIException;
use Stuba\Models\User\GetAllUsers\GetAllUsersResponseModel;
use Stuba\Models\User\GetUser\GetUserResponseModel;
use Stuba\Models\User\CreateUser\CreateUserRequestModel;
use Stuba\Models\User\CreateUser\CreateUserResponseModel;
use Stuba\Models\User\UpdateUser\UpdateUserRequestModel;

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
        $inputData = SimpleRouter::request()->getInputHandler()->all();
        $model = CreateUserRequestModel::createFromModel($inputData);

        // Check if the user already exists
        $query = "SELECT id FROM Users WHERE username = :username";
        $stmt = $this->dbConnection->prepare($query);
        $stmt->bindParam(":username", $model->username);
        $stmt->execute();

        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            throw new APIException('User already exists', 409);
        }

        // Insert the new user into the database
        $query = "INSERT INTO Users (username, password, name, surname, role) VALUES (:username, :password, :name, :surname, :role)";
        $stmt = $this->dbConnection->prepare($query);
        $stmt->bindValue(":username", $model->username);
        $stmt->bindValue(":password", password_hash($model->password, PASSWORD_DEFAULT));
        $stmt->bindValue(":name", $model->name);
        $stmt->bindValue(":surname", $model->surname);
        $stmt->bindValue(":role", $model->role->value);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            SimpleRouter::response()->json(['error' => 'Failed to create user'])->httpCode(500);
            return;

        }
        $userId = $this->dbConnection->lastInsertId();
        $userData = ['id' => (int)$userId];  // Prepare user data as an array
        $responseModel = CreateUserResponseModel::createFromModel($userData);

        SimpleRouter::response()->json($responseModel)->httpCode(200);

    }

    #[OA\Post(path: '/api/user/{id}', tags: ['User'])]
    #[OA\Parameter(name: "id", in: 'path', required: true, description: "User id", example: 5, schema: new OA\Schema(type: 'int'))]
    #[OA\RequestBody(description: 'Update user', required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdateUserRequestModel'))]
    #[OA\Response(response: 200, description: 'Update user')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function updateUser(int $id)
    {
        $inputData = SimpleRouter::request()->getInputHandler()->all();
        $model = UpdateUserRequestModel::createFromModel($inputData);

        // Check if the user exists
        $selectQuery = "SELECT id FROM Users WHERE id = :id";
        $stmt = $this->dbConnection->prepare($selectQuery);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            throw new APIException('User not found', 404);
        }

        // Update the user in the database
        $updateQuery = "UPDATE Users SET username = :username, password = :password, name = :name, surname = :surname, role = :role WHERE id = :id";
        $stmt = $this->dbConnection->prepare($updateQuery);
        $stmt->bindValue(":username", $model->username);
        $stmt->bindValue(":password", password_hash($model->password, PASSWORD_DEFAULT));
        $stmt->bindValue(":name", $model->name);
        $stmt->bindValue(":surname", $model->surname);
        $stmt->bindValue(":role", $model->role->value);
        $stmt->bindValue(":id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            SimpleRouter::response()->json(['message' => 'User updated successfully'])->httpCode(200);
        } else {
            SimpleRouter::response()->json(['error' => 'No changes made to the user'])->httpCode(304); // Not Modified
        }

    }

    #[OA\Delete(path: '/api/user/{id}', tags: ['User'])]
    #[OA\Parameter(name: "id", in: 'path', required: true, description: "User id", example: 5, schema: new OA\Schema(type: 'int'))]
    #[OA\Response(response: 200, description: 'Delete user')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 404, description: 'User not found')]
    public function deleteUserById(int $id)
    {
        $selectQuery = "SELECT id FROM Users WHERE id = :id";
        $stmt = $this->dbConnection->prepare($selectQuery);
        $stmt->bindValue(":id", $id);
        $stmt->execute();

         if ($stmt->rowCount() === 0) {
            SimpleRouter::response()->json(['error' => 'User not found'])->httpCode(404);
            return;
        }

        // Delete the user from the database
        $deleteQuery = "DELETE FROM Users WHERE id = :id";
        $stmt = $this->dbConnection->prepare($deleteQuery);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            SimpleRouter::response()->json(['message' => 'User deleted successfully'])->httpCode(200);
        } else {
            SimpleRouter::response()->json(['error' => 'Failed to delete user'])->httpCode(500);
        } 

    }
}