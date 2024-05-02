<?php declare(strict_types=1);

namespace Stuba\Models\User\GetAllUsers;

use OpenApi\Attributes as OA;
use Stuba\Models\User\EUserRole;
use JsonSerializable;

#[OA\Schema(type: 'object', title: 'GetAllUsersResponseModel')]
class GetAllUsersResponseModel implements JsonSerializable
{
    #[OA\Property(type: 'integer', description: 'User ID', example: 1)]
    public int $id;

    #[OA\Property(type: 'string', description: 'Username', example: 'Janko123')]
    public string $username;

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

    public static function createFromModel($user): GetAllUsersResponseModel
    {
        $obj = new GetAllUsersResponseModel();
        $obj->id = $user["id"];
        $obj->username = $user["username"];
        $obj->role = $user["role"];

        return $obj;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}