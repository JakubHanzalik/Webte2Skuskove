<?php

declare(strict_types=1);

namespace Stuba\Models\User\UpdateUser;

use OpenApi\Attributes as OA;
use Stuba\Db\Models\User\EUserRole;
use Respect\Validation\Validator as Validator;
use Stuba\Exceptions\APIException;
use Respect\Validation\Exceptions\NestedValidationException;

#[OA\Schema(type: 'object', title: 'UpdateUserRequestModel')]
class UpdateUserRequestModel
{
    #[OA\Property(type: 'string', description: 'Password', example: 'password')]
    public string $password;

    #[OA\Property(type: 'string', description: 'Name', example: 'Janko')]
    public string $name;

    #[OA\Property(type: 'string', description: 'Surname', example: 'Hrasko')]
    public string $surname;

    #[OA\Property(title: 'type', type: 'integer', enum: EUserRole::class)]
    public EUserRole $role;

    public function __construct($user)
    {
        $validator = Validator::key('password', Validator::stringType()->notEmpty())
            ->key('name', Validator::stringType()->notEmpty())
            ->key('surname', Validator::stringType()->notEmpty())
            ->key('role', Validator::instance(EUserRole::class));

        try {
            $validator->assert($user);
        } catch (NestedValidationException $exception) {
            throw new APIException(implode($exception->getMessages()), 400);
        }

        $this->password = $user["password"];
        $this->name = $user["name"];
        $this->surname = $user["surname"];
        $this->role = EUserRole::from($user["role"]);
    }
}
