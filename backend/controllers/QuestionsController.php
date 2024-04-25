<?php

namespace Stuba\Controllers;

use OpenApi\Attributes as OA;
use Pecee\SimpleRouter\SimpleRouter;
use Stuba\Handlers\JwtHandler;
use Stuba\Models\Questions\QuestionResponseModel;
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
    #[OA\Response(response: 200, description: 'Get all questions of logged user', content: new OA\JsonContent(ref: '#/components/schemas/QuestionResponseModel'))]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function getAllQuestionsByUser()
    {
        $accessToken = $_COOKIE["AccessToken"];
        $username = $this->jwtHandler->decodeAccessToken($accessToken)["sub"];

        //TODO: Na zaklade username vrati vsetky otazky, ktore vytvoril
        // Vratit pole QuestionResponseModel

        // Mock data
        SimpleRouter::response()->json([
            new QuestionResponseModel([
                "text" => "Ahoj",
                "active" => true,
                "type" => EQuestionType::MULTIPLE_CHOICE,
                "subjectId" => 1,
                "creationDate" => "2021-10-10",
                "authorId" => 1
            ]),
            new QuestionResponseModel([
                "text" => "Ako sa mas?",
                "active" => true,
                "type" => EQuestionType::TEXT,
                "subjectId" => 1,
                "creationDate" => "2021-10-10",
                "authorId" => 1
            ])
        ])->httpCode(200);
    }

    public function getQuestionById(int $id)
    {
        $accessToken = $_COOKIE["AccessToken"];
        $username = $this->jwtHandler->decodeAccessToken($accessToken)["sub"];

        //TODO: Overit ci ma uzivatel pristup k otazke

        //TODO: Na zaklade id vrati otazku

        // Mock data
        SimpleRouter::response()->json([
            new QuestionResponseModel([
                "text" => "Ahoj",
                "active" => true,
                "type" => EQuestionType::SINGLE_CHOICE,
                "subjectId" => 2,
                "creationDate" => "2021-10-10",
                "authorId" => 1
            ])
        ])->httpCode(200);
    }
}