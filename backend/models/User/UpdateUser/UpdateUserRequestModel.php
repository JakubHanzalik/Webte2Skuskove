<?php

declare(strict_types=1);

namespace Stuba\Models\User\UpdateUser;

use Stuba\Models\BaseRequestModel;
use OpenApi\Attributes as OA;
use Stuba\Db\Models\User\EUserRole;
use Respect\Validation\Validator as Validator;

#[OA\Schema(type: 'object', title: 'UpdateUserRequestModel')]
class UpdateUserRequestModel extends BaseRequestModel
{
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
        $this->validator = Validator::attribute('password', Validator::stringType()->notEmpty())
            ->attribute('name', Validator::stringType()->notEmpty())
            ->attribute('surname', Validator::stringType()->notEmpty())
            ->attribute('role', Validator::instance(EUserRole::class));
    }

    public function __set($key, $value)
    {
        if ($key === 'role') {
            $this->role = EUserRole::from($value);
        }
    }

    public static function createFromModel($user): UpdateUserRequestModel
    {
        $obj = new UpdateUserRequestModel();
        $obj->password = $user["password"] ?? "";
        $obj->name = $user["name"] ?? "";
        $obj->surname = $user["surname"] ?? "";

        if (isset($user["role"]))
            $obj->role = $user["role"];

        return $obj;
    }
}
