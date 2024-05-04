<?php declare(strict_types=1);

namespace Stuba\Models\Questions\CreateQuestion;

use JsonSerializable;
use OpenApi\Attributes as OA;
use Stuba\Models\Questions\EQuestionType;
use Stuba\Models\Questions\AnswerModel;

#[OA\Schema(title: 'CreateQuestionRequestModel', schema: 'CreateQuestionRequestModel', type: 'object')]
class CreateQuestionRequestModel implements JsonSerializable
{
    #[OA\Property(title: "text", type: 'string', example: "What is the capital of Slovakia?")]
    public string $text;

    #[OA\Property(title: "active", type: 'bool', example: false)]
    public bool $active;

    #[OA\Property(title: 'type', type: 'integer', enum: EQuestionType::class)]
    public EQuestionType $type;

    #[OA\Property(title: "subjectId", type: 'integer', example: 5)]
    public int $subjectId;

    #[OA\Property(title: "authorId", type: 'integer', example: 5)]
    public int $authorId;

    #[OA\Property(title: "answers", type: 'array', items: new OA\Items(ref: '#/components/schemas/AnswerModel'))]
    public array $answers;

    public function __construct($question)
    {
        $this->text = $question["text"];
        $this->active = $question["active"];
        $this->type = EQuestionType::from($question["type"]);
        $this->subjectId = $question["subjectId"];
        $this->authorId = $question["authorId"];
        $this->answers = [];

        foreach ($question["answers"] as $answer) {
            array_push($this->answers, AnswerModel::constructFromModel($answer));
        }
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}