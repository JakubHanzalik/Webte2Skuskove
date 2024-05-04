<?php declare(strict_types=1);

namespace Stuba\Models\Voting\VoteByCode;

use JsonSerializable;
use OpenApi\Attributes as OA;

#[OA\Schema(title: 'VoteByCodeRequestModel', schema: 'VoteByCodeRequestModel', type: 'object')]
class VoteByCodeRequestModel implements JsonSerializable
{
    #[OA\Property(title: "answer id", type: 'integer', example: 0)]
    public int $answerId;

    public function __construct()
    {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}