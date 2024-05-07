<?php

declare(strict_types=1);

namespace Stuba\Models\Voting\GetQuestionWithAnswers;

use OpenApi\Attributes as OA;
use JsonSerializable;

#[OA\Schema(title: 'GetQuestionAnswerModel', schema: 'GetQuestionAnswerModel', type: 'object')]
class GetQuestionAnswerModel implements JsonSerializable
{
    #[OA\Property(title: "question id", type: 'integer', example: 1)]
    public int $id;

    #[OA\Property(title: "answer", type: 'string', example: "Bratislava")]
    public string $answer;

    public function __construct()
    {
    }

    public static function constructFromModel($answer): GetQuestionAnswerModel
    {
        $model = new GetQuestionAnswerModel();
        $model->id = $answer["id"];
        $model->answer = $answer["answer"];
        return $model;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
