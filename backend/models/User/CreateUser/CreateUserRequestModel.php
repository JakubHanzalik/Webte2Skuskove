<?php

declare(strict_types=1);

namespace Stuba\Models\User\CreateUser;

use OpenApi\Attributes as OA;
use Stuba\Db\Models\User\EUserRole;
use Respect\Validation\Validator;
use Stuba\Models\BaseRequestModel;

#[OA\Schema(type: 'object', title: 'CreateUserRequestModel')]
class CreateUserRequestModel extends BaseRequestModel
{
    #[OA\Property(type: 'string', description: 'Username', example: 'Janko123')]
    public string $username;

    #[OA\Property(type: 'string', description: 'Password', example: 'password')]
    public string $password;

    #[OA\Property(type: 'string', description: 'Name', example: 'Janko')]
    public string $name;

    #[OA\Property(type: 'string', description: 'Surname', example: 'Hrasko')]
    public string $surname;

    #[OA\Property(title: 'type', type: 'integer', enum: EUserRole::class)]
    public EUserRole|null $role = null;

    public function __construct()
    {
        unset($this->role);
        $this->validator = Validator::attribute('username', Validator::stringType()->notEmpty())
            ->attribute('password', Validator::stringType()->notEmpty())
            ->attribute('name', Validator::stringType()->notEmpty())
            ->attribute('surname', Validator::stringType()->notEmpty())
            ->attribute('role', Validator::in([EUserRole::ADMIN, EUserRole::USER])->notEmpty());
    }

    public function __set($key, $value)
    {
        if ($key === 'role') {
            $this->role = EUserRole::from($value);
        }
    }

    public static function createFromModel($user): CreateUserRequestModel
    {
        $obj = new CreateUserRequestModel();
        $obj->username = $user["username"] ?? "";
        $obj->password = $user["password"] ?? "";
        $obj->name = $user["name"] ?? "";
        $obj->surname = $user["surname"] ?? "";

        if (isset($user["role"]))
            $obj->role = $user["role"];

        return $obj;
    }
}
