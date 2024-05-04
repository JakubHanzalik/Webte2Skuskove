<?php declare(strict_types=1);

namespace Stuba\Models\Auth;

use OpenApi\Attributes as OA;
use Respect\Validation\Validator;
use Respect\Validation\Exceptions\NestedValidationException;

#[OA\Schema(title: 'LoginModel', schema: 'LoginModel', type: 'object')]
class LoginRequestModel
{
    #[OA\Property(title: "username", type: 'string', example: "JanKowalski")]
    public string $username;

    #[OA\Property(title: "password", type: 'string', example: "password")]
    public string $password;

    private $validator;
    public function __construct($requestParams)
    {
        $this->username = $requestParams['username'] ?? '';
        $this->password = $requestParams['password'] ?? '';

        $this->validator = Validator::attribute('username', Validator::stringType()->notEmpty())
            ->attribute('password', Validator::stringType()->notEmpty()->length(3, 255));
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
}
