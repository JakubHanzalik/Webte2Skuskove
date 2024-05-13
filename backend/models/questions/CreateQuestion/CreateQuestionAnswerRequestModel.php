<?php

declare(strict_types=1);

namespace Stuba\Models\Questions\CreateQuestion;

use OpenApi\Attributes as OA;
use Respect\Validation\Validator;
use Respect\Validation\Exceptions\NestedValidationException;
use Stuba\Exceptions\APIException;

#[OA\Schema(title: 'CreateQuestionAnswerRequestModel', schema: 'CreateQuestionAnswerRequestModel', type: 'object')]
class CreateQuestionAnswerRequestModel
{

    #[OA\Property(title: "answer", type: 'string', example: "Dobrý")]
    public string $answer;

    #[OA\Property(title: "correct", type: 'bool', example: true)]
    public bool $correct;

    public function __construct($answer)
    {
        $validator = Validator::key('answer', Validator::stringType()->notEmpty())
            ->key('correct', Validator::boolType());

        try {
            $validator->assert($answer);
        } catch (NestedValidationException $exception) {
            throw new APIException(implode($exception->getMessages()), 400);
        }

        $this->answer = $answer["answer"];
        $this->correct = $answer["correct"];
    }

}
