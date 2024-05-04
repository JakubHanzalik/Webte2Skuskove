<?php declare(strict_types=1);

namespace Stuba\Models\Auth;

use OpenApi\Attributes as OA;
use JsonSerializable;
use Stuba\Models\User\EUserRole;

#[OA\Schema(title: 'LoggedUserModel', schema: 'LoggedUserModel', type: 'object')]
class LoggedUserResponseModel implements JsonSerializable
{
    public function __construct($user)
    {
        $this->role = $user['role'];
        $this->username = $user['username'];
        $this->name = $user['name'];
        $this->surname = $user['surname'];
    }

    function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    #[OA\Property(title: "username", type: 'string', example: "JanKowalski")]
    public string $username;

    #[OA\Property(title: "name", type: 'string', example: "Jan")]
    public string $name;

    #[OA\Property(title: "surname", type: 'string', example: "Kowalski")]
    public string $surname;

    #[OA\Property(title: 'type', type: 'integer', enum: EUserRole::class)]
    public EUserRole $role;
}
