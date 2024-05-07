<?php

namespace Stuba\Controllers;


use OpenApi\Attributes as OA;
use PDO;
use Stuba\Db\DbAccess;
use Pecee\SimpleRouter\SimpleRouter;
use Stuba\Models\Codelist\CodelistResponseModel;

#[OA\Tag('Subject')]
class CodelistController
{
    private PDO $dbConnection;

    public function __construct()
    {
        $this->dbConnection = (new DbAccess())->getDbConnection();
    }

    #[OA\Get(path: "/api/subject", tags: ['Subject'])]
    #[OA\Response(response: '200', description: "Get subject values", content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'array', items: new OA\Items(ref: '#/components/schemas/CodelistResponseModel'))))]
    public function handle()
    {
        $query =
            "SELECT 
                s.text AS text, 
                s.value AS value
            FROM Subject s";
        $statement = $this->dbConnection->prepare($query);
        $statement->execute();

        $response = $statement->fetchAll(PDO::FETCH_CLASS, CodelistResponseModel::class);

        SimpleRouter::response()->httpCode(200);
        SimpleRouter::response()->json($response);
    }
}
