<?php

declare(strict_types=1);

namespace Stuba\Handlers\Voting;

use PDO;
use Stuba\Db\DbAccess;

class CloseVotingByQuestionCodeHandler
{
    private PDO $dbConnection;

    public function __construct()
    {
        $this->dbConnection = (new DbAccess())->getDbConnection();
    }

    public function handle(string $questionCode, string $note): int
    {
        $this->dbConnection->beginTransaction();
        try {
            $updateVotingQuery = "UPDATE Voting SET date_to = CURDATE(), note = :note WHERE question_code = :questionCode AND date_to IS NULL";
            $stmt = $this->dbConnection->prepare($updateVotingQuery);
            $stmt->bindValue(':questionCode', $questionCode, PDO::PARAM_STR);
            $stmt->bindValue(':note', $note, PDO::PARAM_STR);
            $stmt->execute();

            $updateQuestionQuery = "UPDATE Questions SET active = 0 WHERE question_code = :questionCode";
            $stmt = $this->dbConnection->prepare($updateQuestionQuery);
            $stmt->bindValue(':questionCode', $questionCode, PDO::PARAM_STR);
            $stmt->execute();

            $this->dbConnection->commit();
            return (int) $this->dbConnection->lastInsertId();
        } catch (\Exception $e) {
            $this->dbConnection->rollBack();
            throw $e;
        }

    }
}
