<?php declare(strict_types=1);

namespace Stuba\Models\Auth;

use OpenApi\Attributes as OA;
use Respect\Validation\Validator;
use Respect\Validation\Exceptions\NestedValidationException;

#[OA\Schema(type: 'object', title: 'ChangePassordRequestModel')]
class ChangePassordRequestModel
{
    #[OA\Property(type: 'string', description: 'Password', example: 'password123')]
    public string $password;
    private $validator;

    public function __construct($requestParams)
    {
        $this->password = $requestParams['password'];
        
        $this->validator = Validator::stringType()->notEmpty()->setTemplate('The password cannot be empty.')->setName('Password')
            ->length(6, 30)->setTemplate('The password must be between {{minValue}} and {{maxValue}} characters long.');

        

    }

    public function isValid(): bool
    {
        return $this->validator->validate($this->password);
    }

    public function getErrors(): array
    {
        try {
            $this->validator->assert($this->password);
        } catch (NestedValidationException $exception) {
            return $exception->getMessages();
        }
        return [];
    }
    /* public static function createFromModel($user): ChangePassordRequestModel
    {
        $obj = new ChangePassordRequestModel();
        $obj->password = $user["password"];

        return $obj;
    } */
}