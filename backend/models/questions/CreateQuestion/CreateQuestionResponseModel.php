<?php declare(strict_types=1);

namespace Stuba\Models\Questions\CreateQuestion;

use JsonSerializable;
use OpenApi\Attributes as OA;

#[OA\Schema(title: 'CreateQuestionResponseModel', schema: 'CreateQuestionResponseModel', type: 'object')]
class CreateQuestionResponseModel implements JsonSerializable
{
    #[OA\Property(title: "code", type: 'string', example: "abcde")]
    public string $code;

    public function __construct($question)
    {
        $this->code = $question["code"];
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}