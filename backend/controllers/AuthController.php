<?php

namespace Stuba\Controllers;

use OpenApi\Attributes as OA;
use Pecee\SimpleRouter\SimpleRouter;

use Stuba\Handlers\JwtHandler;
use Stuba\Models\Auth\LoginModel;
use Stuba\Exceptions\APIException;

class AuthController
{
    private JwtHandler $jwtHandler;

    public function __construct()
    {
        $this->jwtHandler = new JwtHandler();
    }


    #[OA\Post(path: '/api/login')]
    // TODO: Pridat request body
    #[OA\Response(response: 200, description: 'Login user')]
    public function login()
    {
        $model = new LoginModel(SimpleRouter::request()->getInputHandler()->all());

        //TODO: Validovat prihlasovacie udaje voci databaze
        //Ak neexistuje taky uzivatel vyhodit exception
        $user = $this->validateCredentials($model->username, $model->password);

        if ($user) {
            $token = $this->jwtHandler->createAccessToken($model->username);
            setcookie('AccessToken', $token, strtotime('+3 minutes', time()), '/', '', true, true);

            $refreshToken = $this->jwtHandler->createRefreshToken($model->username);
            setcookie('RefreshToken', $refreshToken, strtotime('+1 week', time()), '/', '', true, true);
            SimpleRouter::response()->httpCode(200);
        } else {
            throw new APIException('Invalid credentials', 401);
        }
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


    private function validateCredentials(string $username, string $password): ?array
    {
        $query = "SELECT * FROM Users WHERE username = :username";
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(":username", $username);
        $statement->execute();
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user; 
        }
        return null;
    }

    private function revokeRefreshToken(string $refreshToken): void
    {
        $query = "DELETE FROM Token WHERE token = :token";
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(":token", $refreshToken);
        $statement->execute();
    }

}
