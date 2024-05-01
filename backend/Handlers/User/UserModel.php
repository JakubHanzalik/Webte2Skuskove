<?php declare(strict_types=1);

namespace Stuba\Handlers\User;

class UserModel
{
    public int $id;
    public string $username;
    public string $password;
    public string $name;
    public string $surname;
    public string $role;

    public function __construct()
    {

    }

    public static function createFromModel($user): UserModel
    {
        $obj = new UserModel();
        $obj->id = $user["id"];
        $obj->username = $user["username"];
        $obj->password = $user["password"];
        $obj->name = $user["name"];
        $obj->surname = $user["surname"];
        $obj->role = $user["role"];

        return $obj;
    }
}