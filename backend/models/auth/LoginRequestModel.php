<?php

declare(strict_types=1);

namespace Stuba\Models\Auth;

use OpenApi\Attributes as OA;
use Respect\Validation\Validator;
use Stuba\Exceptions\APIException;
use Respect\Validation\Exceptions\NestedValidationException;

#[OA\Schema(title: 'LoginModel', schema: 'LoginModel', type: 'object')]
class LoginRequestModel
{
    #[OA\Property(title: "username", type: 'string', example: "JanKowalski")]
    public string $username;

    #[OA\Property(title: "password", type: 'string', example: "password")]
    public string $password;
    public function __construct($requestParams)
    {
        $validator = Validator::key('username', Validator::stringType()->notEmpty())
            ->key('password', Validator::stringType()->notEmpty()->length(3, 255));

        try {
            $validator->assert($requestParams);
        } catch (NestedValidationException $exception) {
            throw new APIException(implode($exception->getMessages()), 400);
        }

        $this->username = $requestParams['username'];
        $this->password = $requestParams['password'];
    }
}
