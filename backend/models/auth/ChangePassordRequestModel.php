<?php

declare(strict_types=1);

namespace Stuba\Models\Auth;

use Stuba\Models\BaseRequestModel;
use OpenApi\Attributes as OA;
use Respect\Validation\Validator;

#[OA\Schema(type: 'object', title: 'ChangePassordRequestModel')]
class ChangePassordRequestModel extends BaseRequestModel
{
    #[OA\Property(type: 'string', description: 'Password', example: 'password123')]
    public string $password;

    public function __construct($requestParams)
    {
        $this->password = $requestParams['password'] ?? '';

        $this->validator = Validator::attribute('password', Validator::stringType()->notEmpty());
    }
}
