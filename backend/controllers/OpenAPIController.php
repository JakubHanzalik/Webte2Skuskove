<?php

namespace Stuba\Controllers;

use OpenApi\Generator;
use OpenApi\Attributes as OA;
use Pecee\SimpleRouter\SimpleRouter;

#[OA\Info(title: "Webte skuskove API", version: "0.1")]
class OpenAPIController
{
    public function handle()
    {
        $openapi = Generator::scan(['./Controllers', './Models', './Db/Models']);

        header('Content-Type: application/json');

        SimpleRouter::response()->httpCode(200);
        echo $openapi->toJson();
    }
}
