<?php declare(strict_types=1);

namespace Stuba\Models\Questions\UpdateQuestion;

use JsonSerializable;
use OpenApi\Attributes as OA;

#[OA\Schema(title: 'UpdateQuestionRequestModel', schema: 'UpdateQuestionRequestModel', type: 'object')]
class UpdateQuestionRequestModel implements JsonSerializable
{
    #[OA\Property(title: "text", type: 'string', example: "Aky je tvoj obľúbený predmet?")]
    public string $text;

    #[OA\Property(title: "type", type: 'integer', example: 7)]
    public int $subjectId;

    #[OA\Property(title: "active", type: 'boolean', example: true)]
    public bool $active;

    #[OA\Property(title: "answers", type: 'array', items: new OA\Items(ref: '#/components/schemas/AnswerModel'))]
    public array $answers;

    public function __construct($question)
    {
        $this->text = $question["text"];
        $this->subjectId = $question["subjectId"];
        $this->active = $question["active"];
        $this->answers = $question["answers"];
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}