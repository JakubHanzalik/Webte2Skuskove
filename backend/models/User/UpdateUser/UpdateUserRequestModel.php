<?php declare(strict_types=1);

namespace Stuba\Models\User\UpdateUser;

use JsonSerializable;
use OpenApi\Attributes as OA;
use Stuba\Models\User\EUserRole;
use Respect\Validation\Validator as v ;
use Respect\Validation\Exceptions\NestedValidationException;

#[OA\Schema(type: 'object', title: 'UpdateUserRequestModel')]
class UpdateUserRequestModel implements JsonSerializable
{
    #[OA\Property(type: 'string', description: 'Username', example: 'Janko123')]
    public string $username;

    #[OA\Property(type: 'string', description: 'Password', example: 'password')]
    public string $password;

    #[OA\Property(type: 'string', description: 'Name', example: 'Janko')]
    public string $name;

    #[OA\Property(type: 'string', description: 'Surname', example: 'Hrasko')]
    public string $surname;

    #[OA\Property(title: 'type', type: 'integer', enum: EUserRole::class)]
    public EUserRole $role;
    private $validator;

    public function __construct()
    {
        unset($this->role);
        $this->validator = v::attribute('username', v::stringType()->notEmpty()->length(3, 255))
            ->attribute('password', v::stringType()->notEmpty())
            ->attribute('name', v::stringType()->notEmpty())
            ->attribute('surname', v::stringType()->notEmpty())
            ->attribute('role', v:: instanceOf(EUserRole::class));
    }

    public function __set($key, $value)
    {
        if ($key === 'role') {
            $this->role = EUserRole::from($value);
        }
    }
    public function isValid(): bool
    {
        return $this->validator->validate($this);
    }

    public function getErrors(): array
    {
        try {
            $this->validator->assert($this);
        } catch (NestedValidationException $exception) {
            return $exception->getMessages();
        }
        return [];
    }

    public static function createFromModel($user): UpdateUserRequestModel
    {
        $obj = new UpdateUserRequestModel();
        $obj->username = $user["username"];
        $obj->password = $user["password"];
        $obj->name = $user["name"];
        $obj->surname = $user["surname"];
        $obj->role = $user["role"];

        return $obj;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}