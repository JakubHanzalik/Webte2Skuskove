<?php declare(strict_types=1);

namespace Stuba\Controllers;

use OpenApi\Attributes as OA;

#[OA\Tag('Voting')]
class VotingController
{

    #[OA\Get(path: '/api/voting/{code}', tags: ['Voting'])]
    #[OA\Parameter(name: "code", in: 'path', required: true, description: "Question code", example: "abcde", schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'Get question with answers', content: new OA\JsonContent(ref: '#/components/schemas/GetQuestionWithAnswersResponseModel'))]
    #[OA\Response(response: 404, description: 'Question not found')]
    public function getQuestionWithAnswersByCode(string $code)
    {

    }

    #[OA\Post(path: '/api/voting/{code}', tags: ['Voting'])]
    #[OA\Parameter(name: "code", in: 'path', required: true, description: "Question code", example: "abcde", schema: new OA\Schema(type: 'string'))]
    #[OA\RequestBody(description: 'Vote by code', required: true, content: new OA\JsonContent(ref: '#/components/schemas/VoteByCodeRequestModel'))]
    #[OA\Response(response: 200, description: 'Vote successful')]
    #[OA\Response(response: 401, description: 'Already voted')]
    #[OA\Response(response: 404, description: 'Question not found')]

    public function voteByCode(string $code)
    {

    }

    #[OA\Get(path: '/api/voting/{code}/correct', tags: ['Voting'])]
    #[OA\Parameter(name: "code", in: 'path', required: true, description: "Question code", example: "abcde", schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'Get correct answer id', content: new OA\JsonContent(ref: '#/components/schemas/GetCorrectAnswerIdResponseModel'))]
    #[OA\Response(response: 401, description: 'Did not vote yet')]
    #[OA\Response(response: 400, description: 'Question does not have correct answer')]
    #[OA\Response(response: 404, description: 'Question not found')]

    public function getCorrectAnswerId(string $code)
    {

    }

    #[OA\Get(path: '/api/voting/{code}/statistics', tags: ['Voting'])]
    #[OA\Parameter(name: "code", in: 'path', required: true, description: "Question code", example: "abcde", schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'Get question statistics', content: new OA\JsonContent(ref: '#/components/schemas/GetQuestionStatisticsResponseModel'))]
    #[OA\Response(response: 401, description: 'Did not vote yet')]
    #[OA\Response(response: 404, description: 'Question not found')]
    public function getQuestionStatistics(string $code)
    {

    }
}