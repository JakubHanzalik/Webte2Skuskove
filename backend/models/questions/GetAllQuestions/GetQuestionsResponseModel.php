<?php declare(strict_types=1);

namespace Stuba\Models\Questions\GetAllQuestions;

use JsonSerializable;
use OpenApi\Attributes as OA;

#[OA\Schema(title: 'GetQuestionsResponseModel', schema: 'GetQuestionsResponseModel', type: 'object')]
class GetQuestionsResponseModel implements JsonSerializable
{
    public function __construct($question)
    {
        $this->text = $question["text"];
        $this->active = $question["active"];
        $this->subjectId = $question["subjectId"];
    }

    #[OA\Property(title: "text", type: 'string', example: "Aky je tvoj obľúbený predmet?")]
    public string $text;

    #[OA\Property(title: "active", type: 'string', example: true)]
    public string $active;

    #[OA\Property(title: "subjectId", type: 'int', example: 5)]
    public string $subjectId;

    #[OA\Property(title: "code", type: 'string', example: "abcde")]
    public string $code;

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}