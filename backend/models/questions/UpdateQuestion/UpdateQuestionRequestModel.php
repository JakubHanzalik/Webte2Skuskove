<?php

declare(strict_types=1);

namespace Stuba\Models\Questions\UpdateQuestion;

use OpenApi\Attributes as OA;
use Respect\Validation\Validator;
use Stuba\Models\BaseRequestModel;

#[OA\Schema(title: 'UpdateQuestionRequestModel', schema: 'UpdateQuestionRequestModel', type: 'object')]
class UpdateQuestionRequestModel extends BaseRequestModel
{
    #[OA\Property(title: "text", type: 'string', example: "Aky je tvoj obľúbený predmet?")]
    public string $text;

    #[OA\Property(title: "type", type: 'integer', example: 7)]
    public int|null $subjectId;

    #[OA\Property(title: "active", type: 'bool', example: true)]
    public bool|null $active = null;

    #[OA\Property(title: "answers", type: 'array', items: new OA\Items(ref: '#/components/schemas/UpdateQuestionAnswerRequestModel'))]
    public array $answers = [];
    public function __construct($question)
    {
        $this->text = $question["text"] ?? "";
        $this->subjectId = $question["subjectId"] ?? null;
        $this->active = $question["active"] ?? null;
        $this->answers = [];
        $this->answers = array_map(function ($args) {
            return new UpdateQuestionAnswerRequestModel($args);
        }, $question["answers"] ?? []);

        $this->validator = Validator::attribute('text', Validator::stringType()->notEmpty())
            ->attribute('active', Validator::boolType())
            ->attribute('subjectId', Validator::intType()->positive()->notEmpty())
            ->attribute('answers', Validator::arrayType()->each(Validator::instance(UpdateQuestionAnswerRequestModel::class))->notEmpty());
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
