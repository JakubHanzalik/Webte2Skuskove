<?php declare(strict_types=1);

namespace Stuba\Models\Voting\GetCorrectAnswerId;

use JsonSerializable;
use OpenApi\Attributes as OA;

#[OA\Schema(title: 'GetCorrectAnswerIdResponseModel', schema: 'GetCorrectAnswerIdResponseModel', type: 'object')]
class GetCorrectAnswerIdResponseModel implements JsonSerializable
{
    #[OA\Property(title: "answerId", type: 'integer', example: 1)]
    public int $answerId;

    public function __construct()
    {
    }

    public static function constructFromModel($answer): GetCorrectAnswerIdResponseModel
    {
        $obj = new GetCorrectAnswerIdResponseModel();
        $obj->answerId = $answer["answerId"];

        return $obj;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}