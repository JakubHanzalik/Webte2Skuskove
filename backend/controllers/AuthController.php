<?php

namespace Stuba\Controllers;

use OpenApi\Attributes as OA;
use Pecee\SimpleRouter\SimpleRouter;

use Stuba\Handlers\Jwt\JwtHandler;
use Stuba\Exceptions\APIException;
use Stuba\Db\DbAccess;

use Stuba\Handlers\User\GetUserByUsernameHandler;
use Stuba\Models\Auth\LoginRequestModel;
use Stuba\Models\Auth\RegisterRequestModel;
use Stuba\Models\Auth\LoggedUserResponseModel;
use Stuba\Models\Auth\ChangePassordRequestModel;

use PDO;
use Stuba\Db\Models\User\EUserRole;

#[OA\Tag('Auth')]
class AuthController
{
    private JwtHandler $jwtHandler;
    private PDO $dbConnection;
    private GetUserByUsernameHandler $getUserByUsernameHandler;

    public function __construct()
    {
        $this->jwtHandler = new JwtHandler();
        $this->dbConnection = (new DbAccess())->getDbConnection();
        $this->getUserByUsernameHandler = new GetUserByUsernameHandler();
    }

    #[OA\Post(path: '/api/register', tags: ['Auth'])]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/RegisterModel'))]
    #[OA\Response(response: 200, description: 'Register user')]
    #[OA\Response(response: 400, description: 'Invalid input')]
    #[OA\Response(response: 409, description: 'User already exists')]

    public function register()
    {
        $model = new RegisterRequestModel(SimpleRouter::request()->getInputHandler()->all());

        if (!$model->isValid()) {
            throw new APIException('Invalid input', 400);
        }
        $user = $this->getUserByUsernameHandler->handle($model->username);

        if (!is_null($user)) {
            throw new APIException('User already exists', 409);
        }

        $hashedPassword = password_hash($model->password, PASSWORD_DEFAULT);

        $query = "INSERT INTO Users (username, password, name, surname, role) VALUES (:username, :password, :name,:surname, :role)";
        $statement = $this->dbConnection->prepare($query);
        $statement->bindValue(":username", $model->username);
        $statement->bindValue(":password", $hashedPassword);
        $statement->bindValue(":name", $model->name);
        $statement->bindValue(":surname", $model->surname);
        $statement->bindValue(":role", EUserRole::USER->value, PDO::PARAM_INT);
        $statement->execute();

        $token = $this->jwtHandler->createAccessToken($model->username, EUserRole::USER);
        setcookie('AccessToken', $token, strtotime('+3 minutes', time()), '/', '', true, true);

        $refreshToken = $this->jwtHandler->createRefreshToken($model->username);
        setcookie('RefreshToken', $refreshToken, strtotime('+1 week', time()), '/', '', true, true);

        SimpleRouter::response()->httpCode(200);
        SimpleRouter::response()->json(['message' => 'User registered']);
    }


    #[OA\Post(path: '/api/login', tags: ['Auth'])]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/LoginModel'))]
    #[OA\Response(response: 200, description: 'Login user')]
    #[OA\Response(response: 400, description: 'Invalid input')]
    #[OA\Response(response: 401, description: 'Invalid credentials')]
    public function login()
    {
        $model = new LoginRequestModel(SimpleRouter::request()->getInputHandler()->all());

        if (!$model->isValid()) {
            throw new APIException(implode($model->getErrors()), 400);
        }

        if (!$this->validateCredentials($model->username, $model->password)) {
            throw new APIException('Invalid credentials', 401);
        }

        $user = $this->getUserByUsernameHandler->handle($model->username);

        $token = $this->jwtHandler->createAccessToken($user->username, $user->role);
        setcookie('AccessToken', $token, strtotime('+3 minutes', time()), '/', '', true, true);

        $refreshToken = $this->jwtHandler->createRefreshToken($user->username);
        setcookie('RefreshToken', $refreshToken, strtotime('+1 week', time()), '/', '', true, true);

        SimpleRouter::response()->httpCode(200);
        SimpleRouter::response()->json(['message' => 'Logged in']);
    }

    #[OA\Get(path: '/api/login', tags: ['Auth'])]
    #[OA\Response(response: 200, description: 'Get logged user', content: new OA\JsonContent(ref: '#/components/schemas/LoggedUserModel'))]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Response(response: 404, description: 'User not found')]

    public function getLoggedUser()
    {
        $accessToken = $_COOKIE["AccessToken"];
        $decoded = $this->jwtHandler->decodeAccessToken($accessToken);

        $user = $this->getUserByUsernameHandler->handle($decoded["sub"]);

        if (is_null($user)) {
            throw new APIException('User not found', 404);
        }

        $userModel = new LoggedUserResponseModel([
            "username" => $user->username,
            "name" => $user->name,
            "surname" => $user->surname,
            "role" => $user->role
        ]);

        SimpleRouter::response()->httpCode(200);
        SimpleRouter::response()->json($userModel);
    }

    #[OA\Post(path: '/api/logout', tags: ['Auth'])]
    #[OA\Response(response: 200, description: 'Logout user')]
    public function logout()
    {
        if (isset($_COOKIE["RefreshToken"])) {
            $this->revokeRefreshToken($_COOKIE["RefreshToken"]);
            unset($_COOKIE["RefreshToken"]);
        }
        if (isset($_COOKIE["AccessToken"])) {
            unset($_COOKIE["AccessToken"]);
        }
        setcookie('AccessToken', '', time() - 3600, '/', '', true, true);
        setcookie('RefreshToken', '', time() - 3600, '/', '', true, true);

        SimpleRouter::response()->httpCode(200);
        SimpleRouter::response()->json(['message' => 'Logged out']);
    }

    #[OA\Post(path: '/api/change-password', tags: ['Auth'], description: 'Change current user password')]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ChangePassordRequestModel'))]
    #[OA\Response(response: 200, description: 'Password changed')]
    #[OA\Response(response: 401, description: 'Invalid credentials')]
    #[OA\Response(response: 400, description: 'Invalid input')]
    #[OA\Response(response: 500, description: 'Failed to update password')]
    public function changePassword()
    {
        $accessToken = $_COOKIE["AccessToken"] ?? null;

        $decoded = $this->jwtHandler->decodeAccessToken($accessToken);
        $username = $decoded['sub'];

        $changePasswordModel = new ChangePassordRequestModel(SimpleRouter::request()->getInputHandler()->all());

        if (!$changePasswordModel->isValid()) {
            throw new APIException(implode($changePasswordModel->getErrors()), 400);
        }
        if (!$changePasswordModel->password) {
            throw new APIException('New password is required', 400);
        }
        $hashedPassword = password_hash($changePasswordModel->password, PASSWORD_DEFAULT);
        $updateQuery = "UPDATE Users SET password = :password WHERE username = :username";
        $stmt = $this->dbConnection->prepare($updateQuery);
        $stmt->bindValue(":password", $hashedPassword);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            throw new APIException('Failed to update password', 500);
        }

        SimpleRouter::response()->httpCode(200);
        SimpleRouter::response()->json(['message' => 'Password changed']);
    }

    /**
     * Overi ci uzivatel existuje v databaze a heslo je spravne
     * @param string $username
     * @param string $password
     * @return bool Vrati true ak uzivatel existuje a heslo je spravne
     */
    private function validateCredentials(string $username, string $password): bool
    {
        $user = $this->getUserByUsernameHandler->handle($username);

        return $user && password_verify($password, $user->password);
    }

    private function revokeRefreshToken(string $refreshToken): void
    {
        $query = "DELETE FROM Token WHERE token = :token";
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(":token", $refreshToken);
        $statement->execute();
    }
}
