<?php

declare(strict_types=1);

namespace Stuba\Models\User\CreateUser;

use OpenApi\Attributes as OA;
use Stuba\Db\Models\User\EUserRole;
use Respect\Validation\Validator;
use Stuba\Exceptions\APIException;
use Respect\Validation\Exceptions\NestedValidationException;

#[OA\Schema(type: 'object', title: 'CreateUserRequestModel')]
class CreateUserRequestModel
{
    #[OA\Property(type: 'string', description: 'Username', example: 'Janko123')]
    public string $username;

    #[OA\Property(type: 'string', description: 'Password', example: 'password')]
    public string $password;

    #[OA\Property(type: 'string', description: 'Name', example: 'Janko')]
    public string $name;

    #[OA\Property(type: 'string', description: 'Surname', example: 'Hrasko')]
    public string $surname;

    #[OA\Property(title: 'type', type: 'integer', enum: [0, 1])]
    public EUserRole $role;

    public function __construct(array $user)
    {
        $validator = Validator::key('username', Validator::stringType()->notEmpty())
            ->key('password', Validator::stringType()->notEmpty())
            ->key('name', Validator::stringType()->notEmpty())
            ->key('surname', Validator::stringType()->notEmpty())
            ->key('role', Validator::intVal()->in([0, 1]));

        try {
            $validator->assert($user);
        } catch (NestedValidationException $exception) {
            throw new APIException(implode($exception->getMessages()), 400);
        }

        $this->username = $user["username"];
        $this->password = $user["password"];
        $this->name = $user["name"];
        $this->surname = $user["surname"];
        $this->role = EUserRole::from($user["role"]);
    }
}
