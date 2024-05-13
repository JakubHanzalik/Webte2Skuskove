<?php

declare(strict_types=1);

namespace Stuba\Models\Voting\VoteByCode;

use OpenApi\Attributes as OA;
use Respect\Validation\Validator;
use Stuba\Db\Models\Questions\EQuestionType;
use Stuba\Exceptions\APIException;
use Respect\Validation\Exceptions\NestedValidationException;

#[OA\Schema(title: 'VoteByCodeRequestModel', schema: 'VoteByCodeRequestModel', type: 'object')]
class VoteByCodeRequestModel
{
    #[OA\Property(title: "answer ids", type: 'array', items: new OA\Items(type: 'integer', example: 1), nullable: true)]
    public array|null $answerIds;

    #[OA\Property(title: "answer text", type: 'string', example: "Dobrý", nullable: true)]
    public string|null $answerText;


    public function __construct($data)
    {
        $validator = Validator::callback(
            function ($object) {
                $answerIdsEmpty = empty ($object->answerIds);
                $answerTextEmpty = empty ($object->answerText);

                return ($answerTextEmpty && $answerIdsEmpty) ||
                    (!$answerTextEmpty && $answerIdsEmpty);
            }
        )->setName('One of the fields must be filled');

        try {
            $validator->assert($data);
        } catch (NestedValidationException $exception) {
            throw new APIException(implode($exception->getMessages()), 400);
        }

        $this->answerText = $data["answerText"] ?? null;
        $this->answerIds = $data["answerIds"] ?? null;
    }

    public function getFilledType(): EQuestionType
    {
        if (!is_null($this->answerIds)) {
            if (count($this->answerIds) > 1) {
                return EQuestionType::MULTIPLE_CHOICE;
            } else {
                return EQuestionType::SINGLE_CHOICE;
            }
        }

        if (!is_null($this->answerText)) {
            return EQuestionType::TEXT;
        } else {
            throw new APIException('One of the fields must be filled', 400);
        }
    }
}
