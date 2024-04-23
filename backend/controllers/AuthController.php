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
    #[OA\Response(response: 200, description: 'Login user')]
    public function login()
    {
        $model = new LoginModel(SimpleRouter::request()->getInputHandler()->all());

        //TODO: Validovat prihlasovacie udaje voci databaze
        //Ak neexistuje taky uzivatel vyhodit exception

        if (true /*TODO: Ak je prihlasenie uspesne*/) {
            $token = $this->jwtHandler->createAccessToken($model->username);
            setcookie('AccessToken', $token, strtotime('+3 minutes', time()), '/', '', true, true);

            $refreshToken = $this->jwtHandler->createRefreshToken($model->username);
            setcookie('RefreshToken', $refreshToken, strtotime('+1 week', time()), '/', '', true, true);
            SimpleRouter::response()->httpCode(200);
        } else {
            throw new APIException('Invalid credentials', 401);
        }
    }
}
