<?php

declare(strict_types=1);

namespace Stuba\Models\Questions\UpdateQuestion;

use OpenApi\Attributes as OA;
use Respect\Validation\Validator;
use Stuba\Exceptions\APIException;
use Respect\Validation\Exceptions\NestedValidationException;

#[OA\Schema(title: 'UpdateQuestionRequestModel', schema: 'UpdateQuestionRequestModel', type: 'object')]
class UpdateQuestionRequestModel
{
    #[OA\Property(title: "text", type: 'string', example: "Aky je tvoj obľúbený predmet?")]
    public string $text;

    #[OA\Property(title: "type", type: 'integer', example: 7)]
    public int $subjectId;

    #[OA\Property(title: "active", type: 'bool', example: true)]
    public bool $active = null;

    #[OA\Property(title: "answers", type: 'array', items: new OA\Items(ref: '#/components/schemas/UpdateQuestionAnswerRequestModel'))]
    public array $answers = [];
    public function __construct($question)
    {
        $validator = Validator::key('text', Validator::stringType()->notEmpty())
            ->key('active', Validator::boolType())
            ->key('subjectId', Validator::intType()->positive()->notEmpty())
            ->key('answers', Validator::arrayType()->each(Validator::instance(UpdateQuestionAnswerRequestModel::class))->notEmpty());

        try {
            $validator->assert($question);
        } catch (NestedValidationException $exception) {
            throw new APIException(implode($exception->getMessages()), 400);
        }

        $this->text = $question["text"];
        $this->subjectId = $question["subjectId"];
        $this->active = $question["active"];
        $this->answers = [];
        $this->answers = array_map(function ($args) {
            return new UpdateQuestionAnswerRequestModel($args);
        }, $question["answers"]);
    }
}
