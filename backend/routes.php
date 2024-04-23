<?php

use Pecee\SimpleRouter\SimpleRouter;

use Stuba\Controllers\OpenAPIController;
use Stuba\Controllers\AuthController;

use Stuba\Middleware\AuthMiddleware;

SimpleRouter::group(['prefix' => '/api'], function () {

    SimpleRouter::group(['middleware' => AuthMiddleware::class], function () {
        SimpleRouter::get('/user', function () {
            return 'Hello from user';
        });
    });

    SimpleRouter::post('/login', [AuthController::class, 'login']);
    SimpleRouter::post('/logout', [AuthController::class, 'logout']);

    SimpleRouter::get('/swagger', [OpenAPIController::class, 'handle']);
});
