<?php

namespace Stuba\Models\Auth;

use OpenApi\Attributes as OA;
use JsonSerializable;

#[OA\Schema(title: 'LoggedUserModel', schema: 'LoggedUserModel', type: 'object')]
class LoggedUserResponseModel implements JsonSerializable
{
    public function __construct($user)
    {
        $this->id = $user['id'];
        $this->username = $user['username'];
        $this->name = $user['name'];
        $this->surname = $user['surname'];
    }

    function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    #[OA\Property(title: "id", type: 'integer')]
    public int $id;

    #[OA\Property(title: "username", type: 'string')]
    public string $username;

    #[OA\Property(title: "name", type: 'string')]
    public string $name;

    #[OA\Property(title: "surname", type: 'string')]
    public string $surname;
}
