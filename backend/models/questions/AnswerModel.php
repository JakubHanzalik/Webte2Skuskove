<?php declare(strict_types=1);

namespace Stuba\Models\Questions;

use JsonSerializable;
use OpenApi\Attributes as OA;

#[OA\Schema(title: 'AnswerModel', schema: 'AnswerModel', type: 'object')]
class AnswerModel implements JsonSerializable
{
    #[OA\Property(title: "id", type: 'integer', example: 1)]
    public int $id;

    #[OA\Property(title: "text", type: 'string', example: "Dobrý")]
    public string $text;

    #[OA\Property(title: "correct", type: 'bool', example: true)]
    public bool $correct;

    public function __construct()
    {
    }

    public static function constructFromModel($answer): AnswerModel
    {
        $obj = new AnswerModel();
        $obj->id = $answer["id"];
        $obj->text = $answer["text"];
        $obj->correct = $answer["correct"];

        return $obj;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}