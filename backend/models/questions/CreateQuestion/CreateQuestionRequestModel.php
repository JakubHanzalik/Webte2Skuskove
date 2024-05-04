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

    #[OA\Property(title: "subjectId", type: 'int', example: 5)]
    public int $subjectId;

    #[OA\Property(title: "authorId", type: 'int', example: 5)]
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
        //foreach ($question["answers"] as $answer) {
        //    array_push($this->answers, AnswerModel::constructFromModel($answer));
        // }

        $this->validator = Validator::create()
            ->key('text', Validator::stringType()->notEmpty())
            ->key('active', Validator::boolType()->notEmpty())
            ->key('type', Validator::instance(EQuestionType::class)->notEmpty())
            ->key('subjectId', Validator::intType()->positive()->notEmpty())
            ->key('authorId', Validator::intType()->positive()->notEmpty())
            ->key('answers', Validator::arrayType()->each(Validator::instance(AnswerModel::class))->notEmpty());
    }

    public function isValid(): bool
    {
        try {
            $this->validator->assert([
                'text' => $this->text,
                'active' => $this->active,
                'type' => $this->type,
                'subjectId' => $this->subjectId,
                'authorId' => $this->authorId,
                'answers' => $this->answers
            ]);
            return true;
        } catch (NestedValidationException $exception) {
            return false;
        }
    }

    public function getErrors(): array
    {
        try {
            $this->validator->assert([
                'text' => $this->text,
                'active' => $this->active,
                'type' => $this->type,
                'subjectId' => $this->subjectId,
                'authorId' => $this->authorId,
                'answers' => $this->answers
            ]);
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