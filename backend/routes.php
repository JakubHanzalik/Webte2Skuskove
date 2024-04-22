<?php

use Pecee\SimpleRouter\SimpleRouter;
use Stuba\Controllers\OpenAPIController;

SimpleRouter::group(['prefix' => '/api'], function () {

    SimpleRouter::get('/login', function () {
        return 'Hello world';
    });

    SimpleRouter::get('/swagger', [OpenAPIController::class, 'handle']);
});
