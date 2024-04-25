<?php

namespace Stuba\Models\Auth;

use OpenApi\Attributes as OA;

#[OA\Schema(title: 'LoginModel', schema: 'LoginModel', type: 'object')]
class LoginRequestModel
{
    public function __construct($requestParams)
    {
        $this->username = $requestParams['username'];
        $this->password = $requestParams['password'];
    }

    #[OA\Property(title: "username", type: 'string', example: "JanKowalski")]
    public string $username;

    #[OA\Property(title: "password", type: 'string', example: "password")]
    public string $password;
}
