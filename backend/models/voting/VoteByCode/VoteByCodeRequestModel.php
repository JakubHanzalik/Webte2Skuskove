<?php

declare(strict_types=1);

namespace Stuba\Models\Voting\VoteByCode;

use JsonSerializable;
use OpenApi\Attributes as OA;
use Respect\Validation\Validator;
use Respect\Validation\Exceptions\NestedValidationException;

#[OA\Schema(title: 'VoteByCodeRequestModel', schema: 'VoteByCodeRequestModel', type: 'object')]
class VoteByCodeRequestModel implements JsonSerializable
{
    #[OA\Property(title: "answer id", type: 'integer', example: 0, nullable: true)]
    public int|null $answerId;

    #[OA\Property(title: "answer ids", type: 'array', items: new OA\Items(type: 'integer', example: 1), nullable: true)]
    public array|null $answerIds;

    #[OA\Property(title: "answer text", type: 'string', example: "Dobrý", nullable: true)]
    public string|null $answerText;

    private $validator;

    public function __construct()
    {
        $this->validator = Validator::callback(
            function ($object) {
                $answerIdEmpty = empty($object->answerId);
                $answerIdsEmpty = empty($object->answerIds);
                $answerTextEmpty = empty($object->answerText);

                return (!$answerIdEmpty && $answerTextEmpty && $answerIdsEmpty) ||
                    ($answerIdEmpty && !$answerTextEmpty && $answerIdsEmpty) ||
                    ($answerIdEmpty && $answerTextEmpty && !$answerIdsEmpty);
            }
        )->setName('One of the fields must be filled');
    }

    public static function constructFromModel($data): VoteByCodeRequestModel
    {
        $model = new VoteByCodeRequestModel();
        $model->answerId = $data["answerId"] ?? null;
        $model->answerText = $data["answerText"] ?? null;
        return $model;
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
