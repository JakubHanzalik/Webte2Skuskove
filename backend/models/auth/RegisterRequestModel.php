<?php

declare(strict_types=1);

namespace Stuba\Models\Auth;

use OpenApi\Attributes as OA;
use Respect\Validation\Validator;
use Stuba\Exceptions\APIException;
use Respect\Validation\Exceptions\NestedValidationException;

#[OA\Schema(title: 'RegisterModel', schema: 'RegisterModel', type: 'object')]
class RegisterRequestModel
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
        $validator = Validator::key('username', Validator::stringType()->notEmpty())
            ->key('password', Validator::stringType()->notEmpty())
            ->key('name', Validator::stringType()->notEmpty())
            ->key('surname', Validator::stringType()->notEmpty());

        try {
            $validator->assert($requestParams);
        } catch (NestedValidationException $exception) {
            throw new APIException(implode($exception->getMessages()), 400);
        }

        $this->username = $requestParams['username'];
        $this->password = $requestParams['password'];
        $this->name = $requestParams['name'];
        $this->surname = $requestParams['surname'];
    }
}
