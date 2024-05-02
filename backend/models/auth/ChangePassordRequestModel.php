<?php declare(strict_types=1);

namespace Stuba\Models\Auth;

use OpenApi\Attributes as OA;

#[OA\Schema(type: 'object', title: 'ChangePassordRequestModel')]
class ChangePassordRequestModel
{
    #[OA\Property(type: 'string', description: 'Password', example: 'password123')]
    public string $password;

    public function __construct()
    {
    }

    public static function createFromModel($user): ChangePassordRequestModel
    {
        $obj = new ChangePassordRequestModel();
        $obj->password = $user["password"];

        return $obj;
    }
}