<?php declare(strict_types=1);

namespace Stuba\Handlers\Jwt;

use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Stuba\Exceptions\APIException;
use Stuba\db\DbAccess;
use PDO;

class JwtHandler
{
    /**
     * JWT secret
     * @var string
     */
    private string $secret;
    private PDO $dbConnection;

    public function __construct()
    {
        $this->secret = file_get_contents(__DIR__ . '/../../jwt.key');
        $this->dbConnection = (new DbAccess())->getDbConnection();
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

                $username = $this->getUsernameByRefreshToken($refreshToken);
                return $this->createAccessToken($username);
            } else {
                throw new APIException('Unauthorized', 401);
            }
        }
        try {
            JWT::decode($accessToken, new Key($this->secret, 'HS256'));
        } catch (ExpiredException $e) {
            if ($this->validateRefreshToken($refreshToken)) {
                $username = $this->getUsernameByRefreshToken($refreshToken);
                return $this->createAccessToken($username);
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

    /**
     * Dekoduje access token
     * @param string $accessToken
     * @return array pole s informaciami o uzivatelovi [iss, sub, exp]
     */
    public function decodeAccessToken(string $accessToken): array
    {
        $decoded = JWT::decode($accessToken, new Key($this->secret, 'HS256'));
        return (array) $decoded;
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