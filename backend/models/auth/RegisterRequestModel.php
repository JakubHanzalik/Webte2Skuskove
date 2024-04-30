<?php declare(strict_types=1);

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

    #[OA\Property(title: "username", type: 'string', example: "JanKowalski")]
    public string $username;

    #[OA\Property(title: "password", type: 'string', example: "password")]
    public string $password;

    #[OA\Property(title: "name", type: 'string', example: "Jan")]
    public string $name;

    #[OA\Property(title: "surname", type: 'string', example: "Kowalski")]
    public string $surname;
}
