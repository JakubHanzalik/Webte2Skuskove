<?php declare(strict_types=1);

namespace Stuba\Models\Questions\CreateQuestion;

use JsonSerializable;
use OpenApi\Attributes as OA;
use Stuba\Models\Questions\EQuestionType;
use Stuba\Models\Questions\AnswerModel;
use Respect\Validation\Validator;
use Respect\Validation\Exceptions\NestedValidationException;

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
    private $validator;

    public function __construct($question)
    {
        $this->text = $question["text"];
        $this->active = $question["active"];
        $this->type = EQuestionType::from($question["type"]);
        $this->subjectId = $question["subjectId"];
        $this->authorId = $question["authorId"];
        $this->answers = [];
        $this->answers = array_map([AnswerModel::class, 'constructFromModel'], $question["answers"]);

        $this->validator = Validator::attribute('text', Validator::stringType()->notEmpty())
            ->attribute('active', Validator::boolType()->notEmpty())
            ->attribute('type', Validator::instance(EQuestionType::class)->notEmpty())
            ->attribute('subjectId', Validator::intType()->positive()->notEmpty())
            ->attribute('authorId', Validator::intType()->positive()->notEmpty())
            ->attribute('answers', Validator::arrayType()->each(Validator::instance(AnswerModel::class))->notEmpty());
    }

    public function isValid(): bool
    {
        return $this->validator->validate($this);
    }

    public function getErrors(): array
    {
        try {
            $this->validator->assert($this);
        } catch (NestedValidationException $exception) {
            return $exception->getMessages();
        }
        return [];
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}