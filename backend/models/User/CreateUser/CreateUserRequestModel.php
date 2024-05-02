<?php declare(strict_types=1);

namespace Stuba\Models\User\CreateUser;

use JsonSerializable;
use OpenApi\Attributes as OA;
use Stuba\Models\User\EUserRole;

#[OA\Schema(type: 'object', title: 'CreateUserRequestModel')]
class CreateUserRequestModel implements JsonSerializable
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
    public EUserRole $role;

    public function __construct()
    {
        unset($this->role);
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
        $obj->username = $user["username"];
        $obj->password = $user["password"];
        $obj->name = $user["name"];
        $obj->surname = $user["surname"];
        $obj->role = $user["role"];

        return $obj;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}