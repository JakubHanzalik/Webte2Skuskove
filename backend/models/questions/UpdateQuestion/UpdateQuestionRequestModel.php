<?php declare(strict_types=1);

namespace Stuba\Models\Questions\UpdateQuestion;

use JsonSerializable;
use OpenApi\Attributes as OA;
use Stuba\Models\Questions\AnswerModel;
use Respect\Validation\Validator;
use Respect\Validation\Exceptions\NestedValidationException;

#[OA\Schema(title: 'UpdateQuestionRequestModel', schema: 'UpdateQuestionRequestModel', type: 'object')]
class UpdateQuestionRequestModel implements JsonSerializable
{
    #[OA\Property(title: "text", type: 'string', example: "Aky je tvoj obľúbený predmet?")]
    public string $text;

    #[OA\Property(title: "type", type: 'integer', example: 7)]
    public int $subjectId;

    #[OA\Property(title: "active", type: 'bool', example: true)]
    public bool $active;

    #[OA\Property(title: "answers", type: 'array', items: new OA\Items(ref: '#/components/schemas/AnswerModel'))]
    public array $answers;
    private $validator;
    public function __construct($question)
    {
        $this->text = $question["text"];
        $this->subjectId = $question["subjectId"];
        $this->active = $question["active"];
        $this->answers = [];
        $this->answers = array_map([AnswerModel::class, 'constructFromModel'], $question["answers"] ?? []);

        $this->validator = Validator::attribute('text', Validator::stringType()->notEmpty())
            ->attribute('active', Validator::boolType()->notEmpty())
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