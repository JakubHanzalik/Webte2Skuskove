<?php

namespace Stuba\Controllers;

use OpenApi\Attributes as OA;
use Pecee\SimpleRouter\SimpleRouter;

use Stuba\Models\Auth\LoginModel;
use Stuba\Exceptions\APIException;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController
{
    #[OA\Post(path: '/api/login')]
    #[OA\Response(response: 200, description: 'Login user')]
    public function login()
    {
        $model = new LoginModel(SimpleRouter::request()->getInputHandler()->all());

        //TODO: Validovat prihlasovacie udaje voci databaze
        //Ak neexistuje taky uzivatel vyhodit exception

        if (true /*TODO: Ak je prihlasenie uspesne*/) {
            $token = $this->issueJwtToken($model->username);
            setcookie('Bearer', $token, time() + 3600, '/', '', true, true);
            SimpleRouter::response()->httpCode(200);
        } else {
            throw new APIException('Invalid credentials', 401);
        }
    }

    private function issueJwtToken(string $username): string
    {
        return "JWT token";
    }
}
