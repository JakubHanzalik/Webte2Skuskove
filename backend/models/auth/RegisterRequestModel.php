<?php declare(strict_types=1);

namespace Stuba\Models\Auth;

use OpenApi\Attributes as OA;
use Respect\Validation\Validator;
use Respect\Validation\Exceptions\NestedValidationException;

#[OA\Schema(title: 'RegisterModel', schema: 'RegisterModel', type: 'object')]
class RegisterRequestModel
{
    public function __construct($requestParams)
    {
        $this->username = $requestParams['username'];
        $this->password = $requestParams['password'];
        $this->name = $requestParams['name'];
        $this->surname = $requestParams['surname'];

        $this->validator = Validator::attribute('username', Validator::stringType()->notEmpty()->length(5, 30))
            ->attribute('password', Validator::stringType()->notEmpty()->length(6, 30))
            ->attribute('name', Validator::stringType()->notEmpty()->length(1, 255))
            ->attribute('surname', Validator::stringType()->notEmpty()->length(1, 255));
    }
    public function isValid(): bool
    {
        return $this->validator->validate($this);
    }

    public function getErrors(): array
    {
        try {
            $this->validator->assert($this);
        } catch (NestedValidationException $exception) {
            return $exception->getMessages();
        }
        return [];
    }

    #[OA\Property(title: "username", type: 'string', example: "JanKowalski")]
    public string $username;

    #[OA\Property(title: "password", type: 'string', example: "password")]
    public string $password;

    #[OA\Property(title: "name", type: 'string', example: "Jan")]
    public string $name;

    #[OA\Property(title: "surname", type: 'string', example: "Kowalski")]
    public string $surname;
    private $validator;
}
