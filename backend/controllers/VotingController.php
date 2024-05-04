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

    public function voteByCode(string $code)
    {

    }
}