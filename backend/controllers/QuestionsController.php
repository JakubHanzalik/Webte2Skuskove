<?php

namespace Stuba\Controllers;

use OpenApi\Attributes as OA;
use Pecee\SimpleRouter\SimpleRouter;
use Stuba\Handlers\JwtHandler;
use Stuba\Models\Questions\GetAllQuestions\GetQuestionsResponseModel;
use Stuba\Models\Questions\GetQuestion\GetQuestionResponseModel;
use Stuba\Models\Questions\AnswerModel;
use Stuba\Models\Questions\EQuestionType;

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

    #[OA\Get(path: '/api/question/{id}', description: "Get question by id", tags: ['Question'])]
    #[OA\Parameter(name: "id", in: 'path', required: true, description: "Question code", example: "5", schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'Get question by id of logged user', content: new OA\JsonContent(ref: '#/components/schemas/GetQuestionResponseModel'))]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function getQuestionById(int $id)
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

    public function createQuestion()
    {
        $accessToken = $_COOKIE["AccessToken"];
        $username = $this->jwtHandler->decodeAccessToken($accessToken)["sub"];

    }
}