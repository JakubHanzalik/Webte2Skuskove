<?php

use Pecee\SimpleRouter\SimpleRouter;

use Stuba\Controllers\OpenAPIController;
use Stuba\Controllers\AuthController;

SimpleRouter::group(['prefix' => '/api'], function () {

    SimpleRouter::post('/login', [AuthController::class, 'login']);

    SimpleRouter::get('/swagger', [OpenAPIController::class, 'handle']);
});
