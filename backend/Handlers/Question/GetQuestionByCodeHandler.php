<?php

declare(strict_types=1);

namespace Stuba\Handlers\Question;

use PDO;
use Stuba\Db\Models\Questions\QuestionModel;
use Stuba\Db\DbAccess;

class GetQuestionByCodeHandler
{
    private PDO $dbConnection;

    public function __construct()
    {
        $this->dbConnection = (new DbAccess())->getDbConnection();
    }
    public function handle(string $code): QuestionModel | null
    {
        $selectQuestionQuery = "SELECT * FROM Questions WHERE question_code = :question_code";
        $stmt = $this->dbConnection->prepare($selectQuestionQuery);
        $stmt->bindParam(':question_code', $code);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, QuestionModel::class);

        if ($stmt->rowCount() == 0) {
            return null;
        }
        return $stmt->fetch();
    }
}
