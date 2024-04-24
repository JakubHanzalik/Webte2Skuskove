<?php

namespace Stuba\Controllers;

use OpenApi\Attributes as OA;
use Pecee\SimpleRouter\SimpleRouter;

use Stuba\Handlers\JwtHandler;
use Stuba\Models\Auth\LoginModel;
use Stuba\Exceptions\APIException;
use Stuba\Db\DbAccess;

use PDO;

class AuthController
{
    private JwtHandler $jwtHandler;
    private PDO $dbConnection;

    public function __construct()
    {
        $this->jwtHandler = new JwtHandler();
        $this->dbConnection = (new DbAccess())->getDbConnection();
    }


    #[OA\Post(path: '/api/login')]
    // TODO: Pridat request body
    #[OA\Response(response: 200, description: 'Login user')]
    public function login()
    {
        $model = new LoginModel(SimpleRouter::request()->getInputHandler()->all());

        $user = $this->validateCredentials($model->username, $model->password);

        $token = $this->jwtHandler->createAccessToken($user['username']);
        setcookie('AccessToken', $token, strtotime('+3 minutes', time()), '/', '', true, true);

        $refreshToken = $this->jwtHandler->createRefreshToken($user['username']);
        setcookie('RefreshToken', $refreshToken, strtotime('+1 week', time()), '/', '', true, true);

        SimpleRouter::response()->httpCode(200);
    }

    #[OA\Post(path: '/api/logout')]
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

        //TODO: Odstranit refresh token z databazy

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
