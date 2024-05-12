<?php

declare(strict_types=1);

use Pecee\SimpleRouter\SimpleRouter;
use Pecee\Http\Request;

use Stuba\Controllers\CodelistController;
use Stuba\Controllers\OpenAPIController;
use Stuba\Controllers\AuthController;
use Stuba\Controllers\QuestionsController;
use Stuba\Controllers\DocumentationController;
use Stuba\Controllers\UserController;
use Stuba\Controllers\VotingController;

use Stuba\Middleware\AuthMiddleware;
use Stuba\Middleware\AdminAuthMiddleware;

SimpleRouter::group(['prefix' => '/api'], function () {

    //Logged user
    SimpleRouter::group(['middleware' => AuthMiddleware::class], function () {
        // Auth
        SimpleRouter::get('/login', [AuthController::class, 'getLoggedUser']);
        SimpleRouter::post('/logout', [AuthController::class, 'logout']);
        SimpleRouter::post('/change-password', [AuthController::class, 'changePassword']);

        // Questions
        SimpleRouter::get('/question', [QuestionsController::class, 'getAllQuestionsByUser']);
        SimpleRouter::put('/question', [QuestionsController::class, 'createQuestion']);
        SimpleRouter::post('/question/{id}', [QuestionsController::class, 'updateQuestion']);
        SimpleRouter::delete('/question/{id}', [QuestionsController::class, 'deleteQuestion']);
        SimpleRouter::get('/question/{code}', [QuestionsController::class, 'getQuestionByCode']);
    });

    //Auth
    SimpleRouter::post('/register', [AuthController::class, 'register']);

    // Admin
    SimpleRouter::group(['middleware' => AdminAuthMiddleware::class], function () {
        // User
        SimpleRouter::get('/user', [UserController::class, 'getAllUsers']);
        SimpleRouter::get('/user/{id}', [UserController::class, 'getUserById']);
        SimpleRouter::put('/user', [UserController::class, 'createUser']);
        SimpleRouter::post('/user/{id}', [UserController::class, 'updateUser']);
        SimpleRouter::delete('/user/{id}', [UserController::class, 'deleteUserById']);
    });

    // Voting
    SimpleRouter::get('/voting/{code}', [VotingController::class, 'getQuestionWithAnswersByCode']);
    SimpleRouter::post('/voting/{code}', [VotingController::class, 'voteByCode']);
    SimpleRouter::get('/voting/{code}/correct', [VotingController::class, 'getCorrectAnswerId']);
    SimpleRouter::get('/voting/{code}/statistics', [VotingController::class, 'getQuestionStatistics']);


    // Documentation
    SimpleRouter::get('/docs', [DocumentationController::class, 'generateDocs']);

    SimpleRouter::get('/subject', [CodelistController::class, 'handle']);

    SimpleRouter::post('/login', [AuthController::class, 'login']);

    SimpleRouter::get('/swagger', [OpenAPIController::class, 'handle']);
});

SimpleRouter::error(function (Request $request, \Exception $exception) {
    SimpleRouter::response()->httpCode($exception->getCode());
    SimpleRouter::response()->json(['error' => $exception->getMessage()]);
});
