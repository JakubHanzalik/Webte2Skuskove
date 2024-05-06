<?php declare(strict_types=1);

namespace Stuba\Models\Voting\VoteByCode;

use JsonSerializable;
use OpenApi\Attributes as OA;
use Respect\Validation\Validator;
use Respect\Validation\Exceptions\NestedValidationException;

#[OA\Schema(title: 'VoteByCodeRequestModel', schema: 'VoteByCodeRequestModel', type: 'object')]
class VoteByCodeRequestModel implements JsonSerializable
{
    #[OA\Property(title: "answer id", type: 'integer', example: 0)]
    public int $answerId;

    #[OA\Property(title: "answer text", type: 'string', example: "Dobrý")]
    public string $answerText;

    private $validator;

    public function __construct()
    {
        $this->validator = Validator::attribute('answerId', Validator::intType()->positive()->notEmpty())
            ->attribute('answerText', Validator::stringType()->notEmpty());
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