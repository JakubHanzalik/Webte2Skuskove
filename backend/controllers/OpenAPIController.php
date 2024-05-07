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
        $openapi = Generator::scan(['./Controllers', './Models']);

        header('Content-Type: application/json');

        SimpleRouter::response()->httpCode(200);
        SimpleRouter::response()->json(json_decode($openapi->toJson()));
    }
}
