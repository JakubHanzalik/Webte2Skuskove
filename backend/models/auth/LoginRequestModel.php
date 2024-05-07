<?php

declare(strict_types=1);

namespace Stuba\Models\Auth;

use Stuba\Models\BaseRequestModel;
use OpenApi\Attributes as OA;
use Respect\Validation\Validator;

#[OA\Schema(title: 'LoginModel', schema: 'LoginModel', type: 'object')]
class LoginRequestModel extends BaseRequestModel
{
    #[OA\Property(title: "username", type: 'string', example: "JanKowalski")]
    public string $username;

    #[OA\Property(title: "password", type: 'string', example: "password")]
    public string $password;
    public function __construct($requestParams)
    {
        $this->username = $requestParams['username'] ?? '';
        $this->password = $requestParams['password'] ?? '';

        $this->validator = Validator::attribute('username', Validator::stringType()->notEmpty())
            ->attribute('password', Validator::stringType()->notEmpty()->length(3, 255));
    }
}
