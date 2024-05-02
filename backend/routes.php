<?php declare(strict_types=1);

use Pecee\SimpleRouter\SimpleRouter;

use Stuba\Controllers\CodelistController;
use Stuba\Controllers\OpenAPIController;
use Stuba\Controllers\AuthController;
use Stuba\Controllers\QuestionsController;
use Stuba\Controllers\DocumentationController;

use Stuba\Middleware\AuthMiddleware;

SimpleRouter::group(['prefix' => '/api'], function () {

    SimpleRouter::group(['middleware' => AuthMiddleware::class], function () {
        // Questions
        SimpleRouter::get('/question', [QuestionsController::class, 'getAllQuestionsByUser']);
        SimpleRouter::put('/question', [QuestionsController::class, 'createQuestion']);
        SimpleRouter::post('/question/{id}', [QuestionsController::class, 'updateQuestion']);
        SimpleRouter::delete('/question/{id}', [QuestionsController::class, 'deleteQuestion']);

        // Auth
        SimpleRouter::get('/login', [AuthController::class, 'getLoggedUser']);
        SimpleRouter::post('/logout', [AuthController::class, 'logout']);

        // User
        SimpleRouter::get('/user', [AuthController::class, 'getAllUsers']);
        SimpleRouter::get('/user/{id}', [AuthController::class, 'getUserById']);
        SimpleRouter::put('/user', [AuthController::class, 'createUser']);
        SimpleRouter::post('/user/{id}', [AuthController::class, 'updateUser']);
        SimpleRouter::delete('/user/{id}', [AuthController::class, 'deleteUser']);
    });

    // Documentation
    SimpleRouter::get('/docs', [DocumentationController::class, 'generateDocs']);

    SimpleRouter::get('/question/{id}', [QuestionsController::class, 'getQuestionByCode']);

    SimpleRouter::get('/subject', [CodelistController::class, 'handle']);

    SimpleRouter::post('/login', [AuthController::class, 'login']);

    SimpleRouter::post('/register', [AuthController::class, 'register']);

    SimpleRouter::get('/swagger', [OpenAPIController::class, 'handle']);
});
