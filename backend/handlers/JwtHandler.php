<?php
namespace Stuba\Handlers;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Stuba\Exceptions\APIException;

class JwtHandler
{
    /**
     * JWT secret
     * @var string
     */
    private string $secret;
    public function __construct()
    {
        $this->secret = file_get_contents(__DIR__ . '/../jwt.key');
    }

    /**
     * Vytvori access token pre uzivatela
     * @param string $userName 
     * @return string $accessToken 
     */
    public function createAccessToken(string $username): string
    {
        $payload = [
            'iss' => 'https://node41.webte.fei.stuba.sk/',
            'sub' => $username,
            'exp' => strtotime('+3 minutes', time())
        ];

        return JWT::encode($payload, $this->secret, 'HS256');
    }

    /**
     * Authentifikuje uzivatela pomocou access a refresh tokenu
     * @param string $accessToken
     * @param string $refreshToken
     * @throws \Stuba\Exceptions\APIException
     * @return string $newAccessToken
     */
    public function authentificate(string|null $accessToken, string $refreshToken): string
    {
        if ($accessToken == null) {
            if ($this->validateRefreshToken($refreshToken)) {
                $username = "jano"; //TODO: Ziskat username z databazy
                return $this->createAccessToken($username);
            } else {
                throw new APIException('Unauthorized', 401);
            }
        }
        try {
            JWT::decode($accessToken, new Key($this->secret, 'HS256'));
        } catch (ExpiredException $e) {
            if ($this->validateRefreshToken($refreshToken)) {
                $username = "jano"; //TODO: Ziskat username z databazy
                return $this->createAccessToken($username);
            } else {
                throw new APIException('Unauthorized', 401);
            }
        } catch (\Exception $e) {
            throw new APIException('Unauthorized', 401);
        }
        return "";
    }

    /**
     * Vytvori refresh token pre uzivatela
     * @param string $userName 
     * @return string $refreshToken 
     */
    public function createRefreshToken(string $username): string
    {
        $refreshToken = $this->generateRandomString(30);

        //TODO: Ulozit refresh token do databazy spolu s expiracnym casom a username
        // Cas expiracie by mal byt aspon 1 tyzden od teraz

        return $refreshToken;
    }

    /**
     * Validuje refresh token voci databaze
     * @param string $refreshToken
     * @return bool
     */
    private function validateRefreshToken(string $refreshToken): bool
    {
        //TODO: Pridat validaciu refresh tokenu voci databaze

        return true;
    }

    private function generateRandomString(int $length = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

}