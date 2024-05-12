<?php

declare(strict_types=1);

namespace Stuba\Handlers\Voting;

use PDO;
use Stuba\Db\DbAccess;

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
