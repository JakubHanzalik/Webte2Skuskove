<?php declare(strict_types=1);

use Pecee\SimpleRouter\SimpleRouter;

use Stuba\Controllers\CodelistController;
use Stuba\Controllers\OpenAPIController;
use Stuba\Controllers\AuthController;
use Stuba\Controllers\QuestionsController;

use Stuba\Middleware\AuthMiddleware;

SimpleRouter::group(['prefix' => '/api'], function () {

    SimpleRouter::group(['middleware' => AuthMiddleware::class], function () {
        SimpleRouter::get('/question', [QuestionsController::class, 'getAllQuestionsByUser']);
        SimpleRouter::put('/question', [QuestionsController::class, 'createQuestion']);
        SimpleRouter::post('/question/{id}', [QuestionsController::class, 'updateQuestion']);
        SimpleRouter::delete('/question/{id}', [QuestionsController::class, 'deleteQuestion']);

        SimpleRouter::get('/login', [AuthController::class, 'getLoggedUser']);
    });

    SimpleRouter::get('/question/{id}', [QuestionsController::class, 'getQuestionById']);

    SimpleRouter::get('/codelist/{code}', [CodelistController::class, 'handle']);

    SimpleRouter::post('/login', [AuthController::class, 'login']);

    SimpleRouter::post('/register', [AuthController::class, 'register']);

    SimpleRouter::post('/logout', [AuthController::class, 'logout']);

    SimpleRouter::get('/swagger', [OpenAPIController::class, 'handle']);
});
