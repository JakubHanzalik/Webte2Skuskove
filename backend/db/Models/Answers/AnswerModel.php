<?php

declare(strict_types=1);

namespace Stuba\Db\Models\Answers;

use JsonSerializable;
use OpenApi\Attributes as OA;

#[OA\Schema(title: 'AnswerModel', schema: 'AnswerModel', type: 'object')]
class AnswerModel implements JsonSerializable
{
    #[OA\Property(title: "id", type: 'integer', example: 1)]
    public int $id;

    #[OA\Property(title: "answer", type: 'string', example: "Dobrý")]
    public string $answer;

    #[OA\Property(title: "correct", type: 'bool', example: true)]
    public bool $correct;

    #[OA\Property(title: "question_code", type: 'string', example: "ABCDE")]
    public string $question_code;

    public function __construct()
    {
    }

    public static function constructFromModel($answer): AnswerModel
    {
        $obj = new AnswerModel();
        $obj->id = $answer["id"];
        $obj->answer = $answer["answer"];
        $obj->correct = $answer["correct"];
        $obj->question_code = $answer["question_code"];

        return $obj;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
