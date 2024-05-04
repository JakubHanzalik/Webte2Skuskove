<?php declare(strict_types=1);

use Pecee\SimpleRouter\SimpleRouter;

use Stuba\Controllers\CodelistController;
use Stuba\Controllers\OpenAPIController;
use Stuba\Controllers\AuthController;
use Stuba\Controllers\QuestionsController;
use Stuba\Controllers\DocumentationController;
use Stuba\Controllers\UserController;

use Stuba\Middleware\AuthMiddleware;
use Stuba\Middleware\AdminAuthMiddleware;

SimpleRouter::group(['prefix' => '/api'], function () {

    //Logged user
    SimpleRouter::group(['middleware' => AuthMiddleware::class], function () {
        // Questions
        SimpleRouter::get('/question', [QuestionsController::class, 'getAllQuestionsByUser']);
        SimpleRouter::put('/question', [QuestionsController::class, 'createQuestion']);
        SimpleRouter::post('/question/{id}', [QuestionsController::class, 'updateQuestion']);
        SimpleRouter::delete('/question/{id}', [QuestionsController::class, 'deleteQuestion']);

        // Auth
        SimpleRouter::get('/login', [AuthController::class, 'getLoggedUser']);
        SimpleRouter::post('/logout', [AuthController::class, 'logout']);
        SimpleRouter::post('/change-password', [AuthController::class, 'changePassword']);

    });

    // Admin
    SimpleRouter::group(['middleware' => AdminAuthMiddleware::class], function () {
        // User
        SimpleRouter::get('/user', [UserController::class, 'getAllUsers']);
        SimpleRouter::get('/user/{id}', [UserController::class, 'getUserById']);
        SimpleRouter::put('/user', [UserController::class, 'createUser']);
        SimpleRouter::post('/user/{id}', [UserController::class, 'updateUser']);
        SimpleRouter::delete('/user/{id}', [UserController::class, 'deleteUserById']);
    });

    // Documentation
    SimpleRouter::get('/docs', [DocumentationController::class, 'generateDocs']);

    SimpleRouter::get('/question/{id}', [QuestionsController::class, 'getQuestionByCode']);

    SimpleRouter::get('/subject', [CodelistController::class, 'handle']);

    SimpleRouter::post('/login', [AuthController::class, 'login']);

    SimpleRouter::post('/register', [AuthController::class, 'register']);

    SimpleRouter::get('/swagger', [OpenAPIController::class, 'handle']);
});
