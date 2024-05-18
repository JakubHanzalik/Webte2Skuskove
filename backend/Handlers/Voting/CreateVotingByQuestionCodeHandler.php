<?php

declare(strict_types=1);

namespace Stuba\Handlers\Voting;

use PDO;
use Stuba\Db\DbAccess;
use Stuba\Exceptions\APIException;

class CreateVotingByQuestionCodeHandler
{
    private PDO $dbConnection;

    public function __construct()
    {
        $this->dbConnection = (new DbAccess())->getDbConnection();
    }

    public function handle(string $questionCode): int
    {
        $this->dbConnection->beginTransaction();
        try {
            $checkQuery = "SELECT COUNT(*) FROM Voting WHERE question_code = :questionCode AND date_to IS NULL";
            $stmt = $this->dbConnection->prepare($checkQuery);
            $stmt->bindValue(':questionCode', $questionCode, PDO::PARAM_STR);
            $stmt->execute();
            $activeVotingCount = (int) $stmt->fetchColumn();

            if ($activeVotingCount > 0) {
                throw new APIException('There is already an active voting session for this question code', 400);
            }
            $insertVotingQuery = "INSERT INTO Voting (question_code, date_from) VALUES (:questionCode, CURDATE())";
            $statement = $this->dbConnection->prepare($insertVotingQuery);
            $statement->bindValue(':questionCode', $questionCode, PDO::PARAM_STR);
            $statement->execute();


            $this->dbConnection->commit();
            return (int) $this->dbConnection->lastInsertId();
        } catch (\Exception $e) {
            $this->dbConnection->rollBack();
            throw $e;
        }

    }
}
