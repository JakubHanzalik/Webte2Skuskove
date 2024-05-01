<?php declare(strict_types=1);

namespace Stuba\Models\Questions\CreateQuestion;

use JsonSerializable;
use OpenApi\Attributes as OA;
use Stuba\Models\Questions\EQuestionType;

#[OA\Schema(title: 'CreateQuestionRequestModel', schema: 'CreateQuestionRequestModel', type: 'object')]
class CreateQuestionRequestModel implements JsonSerializable
{
    #[OA\Property(title: "text", type: 'string', example: "What is the capital of Slovakia?")]
    public string $text;

    #[OA\Property(title: "active", type: 'boolean', example: true)]
    public bool $active;

    #[OA\Property(title: 'type', type: 'integer', enum: EQuestionType::class)]
    public EQuestionType $type;

    #[OA\Property(title: "subjectId", type: 'int', example: 5)]
    public int $subjectId;

    #[OA\Property(title: "authorId", type: 'int', example: 5)]
    public int $authorId;

    #[OA\Property(title: "answers", type: 'array', items: new OA\Items(ref: '#/components/schemas/AnswerModel'))]
    public array $answers;

    public function __construct($question)
    {
        $this->text = $question["text"];
        $this->active = $question["active"];
        $this->type = $question["type"];
        $this->subjectId = $question["subjectId"];
        $this->authorId = $question["authorId"];
        $this->answers = $question["answers"];
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}