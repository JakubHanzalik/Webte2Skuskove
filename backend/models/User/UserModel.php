<?php declare(strict_types=1);

namespace Stuba\Models\User;

class UserModel
{
    public int $id;
    public string $username;
    public string $password;
    public string $name;
    public string $surname;
    public EUserRole $role;

    public function __construct()
    {
        unset($this->role);
    }

    public function __set($key, $value)
    {
        if ($key === 'role') {
            $this->role = EUserRole::from((int)$value);
        }
    }

    public static function createFromModel($user): UserModel
    {
        $obj = new UserModel();
        $obj->id = $user["id"];
        $obj->username = $user["username"];
        $obj->password = $user["password"];
        $obj->name = $user["name"];
        $obj->surname = $user["surname"];
        $obj->role->value = $user["role"];

        return $obj;
    }
}