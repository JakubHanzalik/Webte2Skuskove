<?php

declare(strict_types=1);

namespace Stuba\Models\User\CreateUser;

use JsonSerializable;
use OpenApi\Attributes as OA;
use Stuba\Db\Models\User\EUserRole; 

#[OA\Schema(type: 'object', title: 'CreateUserResponseModel')]
class CreateUserResponseModel implements JsonSerializable
{
    #[OA\Property(type: 'integer', description: 'User id', example: 1)]
    public int $id;

    public function __construct()
    {
    }

    public static function createFromModel($user): CreateUserResponseModel
    {
        $obj = new CreateUserResponseModel();
        $obj->id = $user["id"];

        return $obj;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}