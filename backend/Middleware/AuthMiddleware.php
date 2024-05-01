<?php declare(strict_types=1);

namespace Stuba\Middleware;

use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;

use Stuba\Handlers\Jwt\JwtHandler;
use Stuba\Exceptions\APIException;

class AuthMiddleware implements IMiddleware
{
    public function handle(Request $request): void
    {
        if (!isset($_COOKIE["RefreshToken"])) {
            throw new APIException('Unauthorized', 401);
        }

        $accessToken = $_COOKIE["AccessToken"] ?? null;
        $refreshToken = $_COOKIE["RefreshToken"];

        $jwtHandler = new JwtHandler();
        $newAccessToken = $jwtHandler->authentificate($accessToken, $refreshToken);
        if ($newAccessToken != "") {
            setcookie('AccessToken', $newAccessToken, strtotime('+3 minutes', time()), '/', '', true, true);
            $_COOKIE["AccessToken"] = $newAccessToken;
        }
    }
}
