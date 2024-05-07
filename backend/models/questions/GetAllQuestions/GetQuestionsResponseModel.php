<?php

declare(strict_types=1);

namespace Stuba\Models\Questions\GetAllQuestions;

use JsonSerializable;
use OpenApi\Attributes as OA;

#[OA\Schema(title: 'GetQuestionsResponseModel', schema: 'GetQuestionsResponseModel', type: 'object')]
class GetQuestionsResponseModel implements JsonSerializable
{
    #[OA\Property(title: "text", type: 'string', example: "Aky je tvoj obľúbený predmet?")]
    public string $text;

    #[OA\Property(title: "active", type: 'string', example: true)]
    public string $active;

    #[OA\Property(title: "subjectId", type: 'integer', example: 5)]
    public int $subjectId;

    #[OA\Property(title: "code", type: 'string', example: "abcde")]
    public string $code;

    public function __construct()
    {
    }

    public static function constructFromModel($question): GetQuestionsResponseModel
    {
        $obj = new GetQuestionsResponseModel();
        $obj->text = $question["text"];
        $obj->active = $question["active"];
        $obj->subjectId = $question["subjectId"];
        $obj->code = $question["code"];

        return $obj;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
