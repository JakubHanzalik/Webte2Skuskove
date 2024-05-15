<?php declare(strict_types=1);

namespace Stuba\Models\Questions\CreateQuestion;

use OpenApi\Attributes as OA;
use Stuba\Db\Models\Questions\EQuestionType;
use Respect\Validation\Validator;
use Respect\Validation\Exceptions\NestedValidationException;
use Stuba\Exceptions\APIException;

#[OA\Schema(title: 'CreateQuestionRequestModel', schema: 'CreateQuestionRequestModel', type: 'object')]
class CreateQuestionRequestModel
{
    #[OA\Property(title: "text", type: 'string', example: "What is the capital of Slovakia?")]
    public string $text;

    #[OA\Property(title: "active", type: 'bool', example: false)]
    public bool $active;

    #[OA\Property(title: 'type', type: 'integer', enum: EQuestionType::class)]
    public EQuestionType $type;

    #[OA\Property(title: "subjectId", type: 'integer', example: 5)]
    public int $subjectId;

    #[OA\Property(title: "answers", type: 'array', items: new OA\Items(ref: '#/components/schemas/CreateQuestionAnswerRequestModel'))]
    public array $answers;

    public function __construct($question)
    {
        $validator = Validator::key('text', Validator::stringType()->notEmpty())
            ->key('active', Validator::boolType()->notEmpty())
            ->key('type', Validator::callback(function ($object) {
                return EQuestionType::tryFrom($object) !== null;
            }))
            ->key('subjectId', Validator::intType()->positive()->notEmpty());

        try {
            $validator->assert($question);
        } catch (NestedValidationException $exception) {
            throw new APIException(implode($exception->getMessages()), 400);
        }

        $this->text = $question["text"];
        $this->active = $question["active"];
        $this->type = EQuestionType::from($question["type"]);
        $this->subjectId = $question["subjectId"];
        $this->answers = [];

        if ($this->type == EQuestionType::SINGLE_CHOICE || $this->type == EQuestionType::MULTIPLE_CHOICE) {
            $this->answers = array_map(function ($args) {
                return new CreateQuestionAnswerRequestModel($args);
            }, $question["answers"]);
        }

    }
}