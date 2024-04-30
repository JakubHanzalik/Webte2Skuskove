<?php declare(strict_types=1);

namespace Stuba\Models\Questions;

use OpenApi\Attributes as OA;
use JsonSerializable;


#[OA\Schema(title: 'QuestionResponseModel', schema: 'QuestionResponseModel', type: 'object')]
class QuestionResponseModel implements JsonSerializable
{
    #[OA\Property(title: "text", type: 'string', example: "Ako sa Vám páči tento predmet?")]
    public string $text;

    #[OA\Property(title: "active", type: 'boolean', example: true)]
    public bool $active;

    #[OA\Property(title: 'type', type: 'int', enum: EQuestionType::class)]
    public EQuestionType $type;

    #[OA\Property(title: "subjectId", type: 'integer', example: 1)]
    public int $subjectId;

    #[OA\Property(title: "creationDate", type: 'string', example: "2024-6-17 13:58:32")]
    public string $creationDate;

    #[OA\Property(title: "authorId", type: 'integer', example: 1)]
    public int $authorId;

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    public function __construct($question)
    {
        $this->text = $question["text"];
        $this->active = $question["active"];
        $this->type = $question["type"];
        $this->subjectId = $question["subjectId"];
        $this->creationDate = $question["creationDate"];
        $this->authorId = $question["authorId"];
    }
}