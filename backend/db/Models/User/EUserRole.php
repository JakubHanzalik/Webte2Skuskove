<?php

declare(strict_types=1);

namespace Stuba\Db\Models\User;

use OpenApi\Attributes as OA;

#[OA\Schema(type: 'integer', enum: ['user', 'admin'], example: 0)]
enum EUserRole: int
{
    case USER = 0;

    case ADMIN = 1;
}
