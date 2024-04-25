<?php

namespace Stuba\Controllers;


use OpenApi\Attributes as OA;
use PDO;
use Stuba\Db\DbAccess;
use Pecee\SimpleRouter\SimpleRouter;

class CodelistController
{
    private PDO $dbConnection;

    public function __construct()
    {
        $this->dbConnection = (new DbAccess())->getDbConnection();
    }

    #[OA\Get(path: "/api/codelist/{code}")]
    #[OA\Parameter(name: "code", in: 'path', required: true, description: "Codelist code", example: "subject", schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: '200', description: "Get codelist values", content: new OA\MediaType(mediaType: 'application/json', schema: new OA\Schema(type: 'array', items: new OA\Items(ref: '#/components/schemas/CodelistResponseModel'))))]
    public function handle(string $code)
    {
        //TODO: Na zaklade kodu vrati hodnoty ciselnika z databazy
        SimpleRouter::response()->json(['testValue'])->httpCode(200);
    }
}