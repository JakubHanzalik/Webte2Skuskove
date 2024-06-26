<?php

declare(strict_types=1);

namespace Stuba\Handlers\Jwt;

use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Stuba\Handlers\User\GetUserByUsernameHandler;
use Stuba\Db\Models\User\EUserRole;
use Stuba\Exceptions\APIException;
use Stuba\Db\DbAccess;
use PDO;

class JwtHandler
{
    /**
     * JWT secret
     * @var string
     */
    private string $secret;
    private PDO $dbConnection;
    private GetUserByUsernameHandler $getUserByUsernameHandler;

    public function __construct()
    {
        $this->secret = file_get_contents(__DIR__ . '/../../jwt.key');
        $this->dbConnection = (new DbAccess())->getDbConnection();
        $this->getUserByUsernameHandler = new GetUserByUsernameHandler();
    }

    /**
     * Vytvori access token pre uzivatela
     * @param string $userName 
     * @return string $accessToken 
     */
    public function createAccessToken(string $username, EUserRole $userRole): string
    {
        $payload = [
            'iss' => 'https://node41.webte.fei.stuba.sk/',
            'sub' => $username,
            'exp' => strtotime('+3 minutes', time()),
            'role' => $userRole->value
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
                $username = $this->getUsernameByRefreshToken($refreshToken);

                $user = $this->getUserByUsernameHandler->handle($username);

                return $this->createAccessToken($username, $user->role);
            } else {
                throw new APIException('Unauthorized', 401);
            }
        }
        try {
            JWT::decode($accessToken, new Key($this->secret, 'HS256'));
        } catch (ExpiredException $e) {
            if ($this->validateRefreshToken($refreshToken)) {
                $username = $this->getUsernameByRefreshToken($refreshToken);

                $user = $this->getUserByUsernameHandler->handle($username);

                return $this->createAccessToken($username, $user->role);
            } else {
                throw new APIException('Unauthorized', 401);
            }
        } catch (Exception $e) {
            throw new APIException('Unauthorized', 401);
        }
        return "";
    }

    /**
     * Vytvori refresh token pre uzivatela
     * @param string $userName meno pouzivatela
     * @return string $refreshToken 
     */
    public function createRefreshToken(string $username): string
    {
        $this->deleteRefreshToken($username);

        $refreshToken = $this->generateRandomString(30);

        $expirationTime = strtotime('+1 week', time());
        $expirationDateTime = date('Y-m-d H:i:s', $expirationTime);
        $query = "INSERT INTO Token (username, token, validity) VALUES (:username, :token, :validity)";
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(":username", $username);
        $statement->bindParam(":token", $refreshToken);
        $statement->bindParam(":validity", $expirationDateTime);
        $statement->execute();

        return $refreshToken;
    }

    public function deleteRefreshToken(string $username): void
    {
        $this->dbConnection->beginTransaction();
        try {
            $query = "DELETE FROM Token WHERE username = :username";
            $statement = $this->dbConnection->prepare($query);
            $statement->bindParam(":username", $username);
            $statement->execute();

            $this->dbConnection->commit();
        } catch (Exception $e) {
            $this->dbConnection->rollBack();
            throw $e;
        }
    }

    /**
     * Dekoduje access token
     * @param string $accessToken
     * @return array pole s informaciami o uzivatelovi [iss, sub, exp]
     */
    public function decodeAccessToken(string $accessToken): array
    {
        $accessToken = $_COOKIE["AccessToken"];
        $decoded = JWT::decode($accessToken, new Key($this->secret, 'HS256'));
        return (array) $decoded;
    }

    public function isAdmin(): bool
    {
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return false;
        }
        $accessToken = $_SERVER['HTTP_AUTHORIZATION'];
        $decoded = $this->decodeAccessToken($accessToken);
        return $decoded['role'] == EUserRole::ADMIN;
    }

    /**
     * Validuje refresh token voci databaze
     * @param string $refreshToken
     * @return bool
     */
    private function validateRefreshToken(string $refreshToken): bool
    {
        $query = "SELECT validity FROM Token WHERE token = :token";
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(":token", $refreshToken);
        $statement->execute();
        if ($statement->rowCount() > 0) {
            $expires = strtotime($statement->fetchColumn());
            return $expires > time();
        }
        return false;
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

    private function getUsernameByRefreshToken(string $refreshToken): string
    {
        $query = "SELECT username FROM Token WHERE token = :refreshToken";
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(":refreshToken", $refreshToken);
        $statement->execute();
        if ($statement->rowCount() > 0) {
            return $statement->fetchColumn();
        } else {
            throw new APIException("Unauthorized", 401);
        }
    }
}
