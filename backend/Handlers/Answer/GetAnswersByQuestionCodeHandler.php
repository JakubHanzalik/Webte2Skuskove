<?php

declare(strict_types=1);

namespace Stuba\Handlers\Answer;

use PDO;
use Stuba\Db\Models\Answers\AnswerModel;
use Stuba\Db\DbAccess;

class GetAnswersByQuestionCodeHandler
{
    private PDO $dbConnection;

    public function __construct()
    {
        $this->dbConnection = (new DbAccess())->getDbConnection();
    }
    public function handle(string $code): array | null
    {
        $selectQuestionQuery = "SELECT * FROM Answers a WHERE a.question_code = :code";
        $stmt = $this->dbConnection->prepare($selectQuestionQuery);
        $stmt->bindParam(':code', $code);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            return null;
        }
        return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, AnswerModel::class);
    }
}
