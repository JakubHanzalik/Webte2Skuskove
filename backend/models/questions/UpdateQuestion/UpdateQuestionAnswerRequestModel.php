<?php

declare(strict_types=1);

namespace Stuba\Models\Questions\UpdateQuestion;

use OpenApi\Attributes as OA;
use Respect\Validation\Validator;
use Stuba\Exceptions\APIException;
use Respect\Validation\Exceptions\NestedValidationException;

#[OA\Schema(title: 'UpdateQuestionAnswerRequestModel', schema: 'UpdateQuestionAnswerRequestModel', type: 'object')]
class UpdateQuestionAnswerRequestModel
{
    #[OA\Property(title: "answer", type: 'string', example: "Dobrý")]
    public string $answer;

    #[OA\Property(title: "correct", type: 'bool', example: true)]
    public bool $correct;

    public function __construct($answer)
    {
        $validator = Validator::key('answer', Validator::stringType()->notEmpty())
            ->key('correct', Validator::boolType()->notEmpty());

        try {
            $validator->assert($answer);
        } catch (NestedValidationException $exception) {
            throw new APIException(implode($exception->getMessages()), 400);
        }

        $this->answer = $answer["answer"];
        $this->correct = $answer["correct"];
    }
}
