<?php

namespace Stuba\Controllers;

use OpenApi\Generator;
use OpenApi\Attributes as OA;

#[OA\Info(title: "Webte skuskove API", version: "0.1")]
class OpenAPIController
{
    public function handle(): string
    {
        $openapi = Generator::scan(['./controllers', './models']);

        header('Content-Type: application/json');
        return $openapi->toJson();
    }
}
