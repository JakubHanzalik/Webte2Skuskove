<?php

namespace Stuba\Controllers;

use OpenApi\Attributes as OA;
use Pecee\SimpleRouter\SimpleRouter;

use Stuba\Handlers\JwtHandler;
use Stuba\Exceptions\APIException;
use Stuba\Db\DbAccess;

use Stuba\Models\Auth\LoginRequestModel;
use Stuba\Models\Auth\RegisterRequestModel;
use Stuba\Models\Auth\LoggedUserResponseModel;

use PDO;

#[OA\Tag('Auth')]
class AuthController
{
    private JwtHandler $jwtHandler;
    private PDO $dbConnection;

    public function __construct()
    {
        $this->jwtHandler = new JwtHandler();
        $this->dbConnection = (new DbAccess())->getDbConnection();
    }

    #[OA\Post(path: '/api/register', tags: ['Auth'])]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/RegisterModel'))]
    #[OA\Response(response: 200, description: 'Register user')]
    #[OA\Response(response: 409, description: 'User already exists')]

    public function register()
    {
        $model = new RegisterRequestModel(SimpleRouter::request()->getInputHandler()->all());

        ///TODO: Ulozit uzivatela do databazy a skontrolovat ci uz neexistuje
        /// ak existuje vyhodit APIException('User already exists', 409)

        $token = $this->jwtHandler->createAccessToken($model->username);
        setcookie('AccessToken', $token, strtotime('+3 minutes', time()), '/', '', true, true);

        $refreshToken = $this->jwtHandler->createRefreshToken($model->username);
        setcookie('RefreshToken', $refreshToken, strtotime('+1 week', time()), '/', '', true, true);

        SimpleRouter::response()->httpCode(200);
    }


    #[OA\Post(path: '/api/login', tags: ['Auth'])]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/LoginModel'))]
    #[OA\Response(response: 200, description: 'Login user')]
    #[OA\Response(response: 401, description: 'Invalid credentials')]
    public function login()
    {
        $model = new LoginRequestModel(SimpleRouter::request()->getInputHandler()->all());

        $user = $this->validateCredentials($model->username, $model->password);

        $token = $this->jwtHandler->createAccessToken($user['username']);
        setcookie('AccessToken', $token, strtotime('+3 minutes', time()), '/', '', true, true);

        $refreshToken = $this->jwtHandler->createRefreshToken($user['username']);
        setcookie('RefreshToken', $refreshToken, strtotime('+1 week', time()), '/', '', true, true);

        SimpleRouter::response()->httpCode(200);
    }

    #[OA\Get(path: '/api/login', tags: ['Auth'])]
    #[OA\Response(response: 200, description: 'Get logged user', content: new OA\JsonContent(ref: '#/components/schemas/LoggedUserModel'))]
    #[OA\Response(response: 401, description: 'Unauthorized')]

    public function getLoggedUser()
    {
        $accessToken = $_COOKIE["AccessToken"];
        $decoded = $this->jwtHandler->decodeAccessToken($accessToken);

        //TODO: Vratit uzivatela z databazy podla $decoded['sub'] co je username

        //Toto je len mock
        $user = new LoggedUserResponseModel([
            'id' => 1,
            'username' => 'test',
            'name' => 'Test',
            'surname' => 'Testovic'
        ]);

        SimpleRouter::response()->json($user)->httpCode(200);
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
    }

    /**
     * Overi ci uzivatel existuje v databaze a heslo je spravne
     * @param string $username
     * @param string $password
     * @return array Vrati uzivatela ak existuje
     * @throws APIException Ak uzivatel neexistuje alebo heslo je nespravne
     */
    private function validateCredentials(string $username, string $password): array
    {
        $query = "SELECT * FROM Users WHERE username = :username";
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(":username", $username);
        $statement->execute();
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        } else {
            throw new APIException('Invalid credentials', 401);
        }
    }

    private function revokeRefreshToken(string $refreshToken): void
    {
        $query = "DELETE FROM Token WHERE token = :token";
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(":token", $refreshToken);
        $statement->execute();
    }
}
