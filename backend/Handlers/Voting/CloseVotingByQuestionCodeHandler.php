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

    public function handle(string $questionCode): int
    {
        $this->dbConnection->beginTransaction();
        try {
            //TODO
            $this->dbConnection->commit();
            return (int) $this->dbConnection->lastInsertId();
        } catch (\Exception $e) {
            $this->dbConnection->rollBack();
            throw $e;
        }

    }
}
