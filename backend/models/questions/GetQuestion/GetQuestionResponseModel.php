<?php

declare(strict_types=1);

namespace Stuba\Models\Questions\GetQuestion;

use OpenApi\Attributes as OA;
use JsonSerializable;
use Stuba\Db\Models\Questions\EQuestionType;

#[OA\Schema(title: 'GetQuestionResponseModel', schema: 'GetQuestionResponseModel', type: 'object')]
class GetQuestionResponseModel implements JsonSerializable
{
    #[OA\Property(title: "text", type: 'string', example: "Ako sa Vám páči tento predmet?")]
    public string $text;

    #[OA\Property(title: "active", type: 'bool', example: true)]
    public bool $active;

    #[OA\Property(title: 'type', type: 'integer', enum: EQuestionType::class)]
    public EQuestionType $type;

    #[OA\Property(title: "subjectId", type: 'integer', example: 1)]
    public int $subjectId;

    #[OA\Property(title: "creationDate", type: 'string', example: "2024-6-17 13:58:32")]
    public string $creationDate;

    #[OA\Property(title: "code", type: 'string', example: "abcde")]
    public string $code;

    #[OA\Property(title: "answers", type: 'array', items: new OA\Items(ref: '#/components/schemas/GetQuestionAnswerResponseModel'))]
    public array $answers;

    public function __construct()
    {
        unset($this->type);
        unset($this->creationDate);
    }

    public function __set($key, $value)
    {
        if ($key === 'type') {
            $this->type = EQuestionType::from($value);
        }
        if ($key === 'creationDate') {
            $this->creationDate = $value->format('Y-m-d H:i:s');
        }
    }

    public static function constructFromModel($question): GetQuestionResponseModel
    {
        $obj = new GetQuestionResponseModel();
        $obj->text = $question["text"];
        $obj->active = $question["active"];
        $obj->subjectId = $question["subjectId"];
        $obj->creationDate = $question["creationDate"];
        $obj->code = $question["code"];
        $obj->answers = $question["answers"];

        if (isset($question["type"])) {
            $obj->type = $question["type"];
        }

        return $obj;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
