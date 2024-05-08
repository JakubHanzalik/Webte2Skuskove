<?php

declare(strict_types=1);

namespace Stuba\Models\Voting\GetQuestionWithAnswers;

use OpenApi\Attributes as OA;
use JsonSerializable;
use Stuba\Db\Models\Questions\EQuestionType;

#[OA\Schema(title: 'GetQuestionWithAnswersResponseModel', schema: 'GetQuestionWithAnswersResponseModel', type: 'object')]
class GetQuestionWithAnswersResponseModel implements JsonSerializable
{
    #[OA\Property(title: "question", type: 'string', example: "What is the capital of Slovakia?")]
    public string $question;

    #[OA\Property(title: 'type', type: 'integer', enum: EQuestionType::class)]
    public EQuestionType $type;

    #[OA\Property(title: "answers", type: 'array', items: new OA\Items(ref: '#/components/schemas/GetQuestionAnswerModel'))]
    public array $answers;

    public function __construct()
    {
    }

    public function __set($key, $value)
    {
        if ($key === 'type') {
            $this->type = EQuestionType::from($value);
        }
    }

    public static function constructFromModel($question): GetQuestionWithAnswersResponseModel
    {
        $model = new GetQuestionWithAnswersResponseModel();
        $model->question = $question["question"] ?? "";
        if (isset($question["type"])) {
            $model->type = EQuestionType::from($question["type"]);
        }
        $model->answers = $question["answers"] ?? [];
        return $model;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
