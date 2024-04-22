<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once 'routes.php';

use Pecee\SimpleRouter\SimpleRouter;
use Pecee\Http\Request;

SimpleRouter::setDefaultNamespace('\Controllers');

SimpleRouter::error(function (Request $request, \Exception $exception) {

    switch ($exception->getCode()) {
        case 404:
            http_response_code(404);
            SimpleRouter::response()->json(['error' => '404 - Not found']);
            break;
        case 401:
            http_response_code(401);
            SimpleRouter::response()->json(['error' => '401 - Unauthorized']);
            break;
    }

});

SimpleRouter::start();