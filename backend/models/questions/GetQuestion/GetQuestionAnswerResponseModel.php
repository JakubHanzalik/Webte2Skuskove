<?php declare(strict_types=1);

namespace Stuba\Models\Questions\GetQuestion;

use OpenApi\Attributes as OA;
use JsonSerializable;

#[OA\Schema(title: 'GetQuestionAnswerResponseModel', schema: 'GetQuestionAnswerResponseModel', type: 'object')]
class GetQuestionAnswerResponseModel implements JsonSerializable
{
    #[OA\Property(title: "id", type: 'integer', example: 1)]
    public int $id;
    #[OA\Property(title: "answer", type: 'string', example: "Bratislava")]
    public string $answer;

    #[OA\Property(title: "correct", type: 'bool', example: true)]
    public bool $correct;

    public function __construct()
    {
    }

    public static function constructFromModel($answer): GetQuestionAnswerResponseModel
    {
        $model = new GetQuestionAnswerResponseModel();
        $model->id = $answer["id"];
        $model->answer = $answer["answer"];
        $model->correct = $answer["correct"];
        return $model;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}