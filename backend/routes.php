<?php

use Pecee\SimpleRouter\SimpleRouter;

use Stuba\Controllers\CodelistController;
use Stuba\Controllers\OpenAPIController;
use Stuba\Controllers\AuthController;
use Stuba\Controllers\QuestionsController;

use Stuba\Middleware\AuthMiddleware;

SimpleRouter::group(['prefix' => '/api'], function () {

    SimpleRouter::group(['middleware' => AuthMiddleware::class], function () {
        SimpleRouter::get('/question', [QuestionsController::class, 'getAllQuestionsByUser']);
        SimpleRouter::get('/question/{id}', [QuestionsController::class, 'getQuestionById']);

        SimpleRouter::get('/login', [AuthController::class, 'getLoggedUser']);
    });

    SimpleRouter::get('/codelist/{code}', [CodelistController::class, 'handle']);

    SimpleRouter::post('/login', [AuthController::class, 'login']);

    SimpleRouter::post('/register', [AuthController::class, 'register']);

    SimpleRouter::post('/logout', [AuthController::class, 'logout']);

    SimpleRouter::get('/swagger', [OpenAPIController::class, 'handle']);
});
