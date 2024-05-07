<?php

declare(strict_types=1);

namespace Stuba\Models\Auth;

use Stuba\Models\BaseRequestModel;
use OpenApi\Attributes as OA;
use Respect\Validation\Validator;

#[OA\Schema(title: 'RegisterModel', schema: 'RegisterModel', type: 'object')]
class RegisterRequestModel extends BaseRequestModel
{
    #[OA\Property(title: "username", type: 'string', example: "JanKowalski")]
    public string $username;

    #[OA\Property(title: "password", type: 'string', example: "password")]
    public string $password;

    #[OA\Property(title: "name", type: 'string', example: "Jan")]
    public string $name;

    #[OA\Property(title: "surname", type: 'string', example: "Kowalski")]
    public string $surname;

    public function __construct($requestParams)
    {
        $this->username = $requestParams['username'] ?? '';
        $this->password = $requestParams['password'] ?? '';
        $this->name = $requestParams['name'] ?? '';
        $this->surname = $requestParams['surname'] ?? '';

        $this->validator = Validator::attribute('username', Validator::stringType()->notEmpty())
            ->attribute('password', Validator::stringType()->notEmpty())
            ->attribute('name', Validator::stringType()->notEmpty())
            ->attribute('surname', Validator::stringType()->notEmpty());
    }
}
