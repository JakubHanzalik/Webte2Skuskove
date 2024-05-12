<?php

declare(strict_types=1);

namespace Stuba\Models\Auth;

use OpenApi\Attributes as OA;
use Respect\Validation\Validator;
use Stuba\Exceptions\APIException;
use Respect\Validation\Exceptions\NestedValidationException;

#[OA\Schema(type: 'object', title: 'ChangePassordRequestModel')]
class ChangePassordRequestModel
{
    #[OA\Property(type: 'string', description: 'Password', example: 'password123')]
    public string $password;

    public function __construct($requestParams)
    {
        $validator = Validator::key('password', Validator::stringType()->notEmpty());

        try {
            $validator->assert($requestParams);
        } catch (NestedValidationException $exception) {
            var_dump($exception->getMessages());
            throw new APIException(implode($exception->getMessages()), 400);
        }

        $this->password = $requestParams['password'];
    }
}
