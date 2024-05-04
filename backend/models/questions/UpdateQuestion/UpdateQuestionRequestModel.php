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
        //foreach ($question["answers"] as $answer) {
        //    array_push($this->answers, AnswerModel::constructFromModel($answer));
        //}

        $this->validator = Validator::create()
            ->key('text', Validator::stringType()->notEmpty())
            ->key('subjectId', Validator::intType()->positive())
            ->key('active', Validator::boolType())
            ->key('answers', Validator::arrayType()->each(
                Validator::instance(AnswerModel::class)->setName('Answer')
            )
            );

    }

    public function isValid(): bool
    {
        try {
            $this->validator->assert([
                'text' => $this->text,
                'subjectId' => $this->subjectId,
                'active' => $this->active,
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
                'subjectId' => $this->subjectId,
                'active' => $this->active,
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