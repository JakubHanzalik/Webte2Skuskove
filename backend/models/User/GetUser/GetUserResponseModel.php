<?php

declare(strict_types=1);

namespace Stuba\Models\User\GetUser;

use JsonSerializable;
use OpenApi\Attributes as OA;
use Stuba\Db\Models\User\EUserRole;

#[OA\Schema(type: 'object', title: 'GetUserResponseModel')]
class GetUserResponseModel implements JsonSerializable
{
    #[OA\Property(type: 'string', description: 'Username', example: 'Janko123')]
    public string $username;

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

    public static function createFromModel($user): GetUserResponseModel
    {
        $obj = new GetUserResponseModel();
        $obj->username = $user["username"];
        $obj->name = $user["name"];
        $obj->surname = $user["surname"];
        $obj->role = $user["role"]->value;

        return $obj;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
