<?php

namespace Stuba\Models\Auth;

use OpenApi\Attributes as OA;

#[OA\Schema(title: 'LoginModel')]
class LoginModel
{
    public function __construct($requestParams)
    {
        $this->username = $requestParams['username'];
        $this->password = $requestParams['password'];
    }

    #[OA\Property(title: "username", format: 'string')]
    public string $username;

    #[OA\Property(title: "password", format: 'string')]
    public string $password;
}
