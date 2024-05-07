<?php

declare(strict_types=1);

namespace Stuba\Models\Voting\GetCorrectAnswerId;

use JsonSerializable;
use OpenApi\Attributes as OA;

#[OA\Schema(title: 'GetCorrectAnswerIdResponseModel', schema: 'GetCorrectAnswerIdResponseModel', type: 'object')]
class GetCorrectAnswerIdResponseModel implements JsonSerializable
{
    #[OA\Property(description: 'Correct answer ids', type: 'array', items: new OA\Items(type: 'integer', example: 1))]
    public array $answerIds;

    public function __construct()
    {
    }

    public static function constructFromModel($answer): GetCorrectAnswerIdResponseModel
    {
        $obj = new GetCorrectAnswerIdResponseModel();
        $obj->answerIds = $answer["answerId"];

        return $obj;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
