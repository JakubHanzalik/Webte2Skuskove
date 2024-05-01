<?php declare(strict_types=1);

namespace Stuba\Models\Questions;

use JsonSerializable;
use OpenApi\Attributes as OA;

#[OA\Schema(title: 'AnswerModel', schema: 'AnswerModel', type: 'object')]
class AnswerModel implements JsonSerializable
{
    #[OA\Property(title: "text", type: 'string', example: "Dobrý")]
    public string $text;

    #[OA\Property(title: "correct", type: 'string', example: "Y")]
    public string $correct;

    public function __construct($answer)
    {
        $this->text = $answer["text"];
        $this->correct = $answer["correct"];
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}