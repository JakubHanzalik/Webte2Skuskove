<?php declare(strict_types=1);

namespace Stuba\Models\Auth;

use OpenApi\Attributes as OA;
use Respect\Validation\Validator;
use Respect\Validation\Exceptions\NestedValidationException;

#[OA\Schema(type: 'object', title: 'ChangePassordRequestModel')]
class ChangePassordRequestModel
{
    #[OA\Property(type: 'string', description: 'Password', example: 'password123')]
    public string $password;
    private $validator;

    public function __construct($requestParams)
    {
        $this->password = $requestParams['password'];
        $this->validator = Validator::attribute('password', Validator::stringType()->notEmpty());
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