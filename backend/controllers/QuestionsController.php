<?php

namespace Stuba\Controllers;

use OpenApi\Attributes as OA;
use Pecee\SimpleRouter\SimpleRouter;
use Stuba\Handlers\JwtHandler;
use Stuba\Models\Questions\CreateQuestion\CreateQuestionResponseModel;
use Stuba\Models\Questions\GetAllQuestions\GetQuestionsResponseModel;
use Stuba\Models\Questions\GetQuestion\GetQuestionResponseModel;
use Stuba\Models\Questions\AnswerModel;
use Stuba\Models\Questions\EQuestionType;
use Stuba\Models\Questions\UpdateQuestion\UpdateQuestionRequestModel;
use Stuba\Models\Questions\CreateQuestion\CreateQuestionRequestModel;


#[OA\Tag('Question')]
class QuestionsController
{
    private JwtHandler $jwtHandler;

    public function __construct()
    {
        $this->jwtHandler = new JwtHandler();
    }

    #[OA\Get(path: '/api/question', tags: ['Question'])]
    #[OA\Response(response: 200, description: 'Get all questions of logged user', content: new OA\JsonContent(ref: '#/components/schemas/GetQuestionsResponseModel'))]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function getAllQuestionsByUser()
    {
        $accessToken = $_COOKIE["AccessToken"];
        $username = $this->jwtHandler->decodeAccessToken($accessToken)["sub"];

        //TODO: Na zaklade username vrati vsetky otazky, ktore vytvoril
        // Vratit pole GetQuestionsResponseModel

        // Mock data
        SimpleRouter::response()->json([
            new GetQuestionsResponseModel([
                "text" => "Ako sa Vám páči tento predmet?",
                "active" => true,
                "subjectId" => 2,
                "code" => "abcde"
            ]),
            new GetQuestionsResponseModel([
                "text" => "Aky je tvoj obľúbený predmet?",
                "active" => true,
                "subjectId" => 5,
                "code" => "fghij"
            ])
        ])->httpCode(200);
    }

    #[OA\Get(path: '/api/question/{code}', description: "Get question by code", tags: ['Question'])]
    #[OA\Parameter(name: "code", in: 'path', required: true, description: "Question code", example: "abcde", schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'Get question by id of logged user', content: new OA\JsonContent(ref: '#/components/schemas/GetQuestionResponseModel'))]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function getQuestionById(string $code)
    {
        $accessToken = $_COOKIE["AccessToken"];
        $username = $this->jwtHandler->decodeAccessToken($accessToken)["sub"];

        //TODO: Overit ci ma uzivatel pristup k otazke

        //TODO: Na zaklade id vrati otazku

        // Mock data
        SimpleRouter::response()->json([
            new GetQuestionResponseModel([
                "text" => "Ako sa Vám páči tento predmet?",
                "active" => true,
                "type" => EQuestionType::SINGLE_CHOICE,
                "subjectId" => 1,
                "creationDate" => "2024-6-17 13:58:32",
                "authorId" => 1,
                "code" => "abcde",
                "answers" => [
                    new AnswerModel([
                        "text" => "Dobrý",
                        "correct" => true
                    ]),
                    new AnswerModel([
                        "text" => "Zlý",
                        "correct" => false
                    ])
                ]
            ])
        ])->httpCode(200);
    }

    #[OA\Post(path: '/api/question/{code}', tags: ['Question'])]
    #[OA\Parameter(name: "code", in: 'path', required: true, description: "Question code", example: "abcde", schema: new OA\Schema(type: 'string'))]
    #[OA\RequestBody(description: 'Update question', required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdateQuestionRequestModel'))]
    #[OA\Response(response: 200, description: 'Update question')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function updateQuestion(string $code)
    {
        $accessToken = $_COOKIE["AccessToken"];
        $username = $this->jwtHandler->decodeAccessToken($accessToken)["sub"];

        $model = new UpdateQuestionRequestModel(SimpleRouter::request()->getInputHandler()->all());

        //TODO: Aktualizovat otazku v databaze

        SimpleRouter::response()->httpCode(200);
    }

    #[OA\Put(path: '/api/question', tags: ['Question'])]
    #[OA\RequestBody(description: 'Create question', required: true, content: new OA\JsonContent(ref: '#/components/schemas/CreateQuestionRequestModel'))]
    #[OA\Response(response: 200, description: 'Create question', content: new OA\JsonContent(ref: '#/components/schemas/CreateQuestionResponseModel'))]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function createQuestion()
    {
        $model = new CreateQuestionRequestModel(SimpleRouter::request()->getInputHandler()->all());

        //TODO: Vytvorit otazku v databaze

        //Mock data
        SimpleRouter::response()->json([
            new CreateQuestionResponseModel([
                "code" => "abcde",
            ])
        ])->httpCode(200);
    }

    #[OA\Delete(path: '/api/question/{code}', tags: ['Question'])]
    #[OA\Parameter(name: "code", in: 'path', required: true, description: "Question code", example: "abcde", schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'Delete question')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function deleteQuestion(string $code)
    {
        $accessToken = $_COOKIE["AccessToken"];
        $username = $this->jwtHandler->decodeAccessToken($accessToken)["sub"];

        SimpleRouter::response()->httpCode(200);
    }
}