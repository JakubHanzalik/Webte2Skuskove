<?php

namespace Stuba\Models\Auth;

use OpenApi\Attributes as OA;

#[OA\Schema(title: 'RegisterModel', schema: 'RegisterModel', type: 'object')]
class RegisterRequestModel
{
    public function __construct($requestParams)
    {
        $this->username = $requestParams['username'];
        $this->password = $requestParams['password'];
        $this->name = $requestParams['name'];
        $this->surname = $requestParams['surname'];
    }

    #[OA\Property(title: "username", type: 'string')]
    public string $username;

    #[OA\Property(title: "password", type: 'string')]
    public string $password;

    #[OA\Property(title: "name", type: 'string')]
    public string $name;

    #[OA\Property(title: "surname", type: 'string')]
    public string $surname;
}
