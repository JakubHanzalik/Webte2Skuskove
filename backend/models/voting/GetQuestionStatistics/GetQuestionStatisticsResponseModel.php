<?php declare(strict_types=1);

namespace Stuba\Models\Voting\GetQuestionStatistics;

use JsonSerializable;
use OpenApi\Attributes as OA;

#[OA\Schema(title: 'GetQuestionStatisticsResponseModel', schema: 'GetQuestionStatisticsResponseModel', type: 'object')]
class GetQuestionStatisticsResponseModel implements JsonSerializable
{
    #[OA\Property(title: "answer id", type: 'integer', example: 1)]
    public int $answerId;

    #[OA\Property(title: "answer text", type: 'string', example: "Dobrý")]
    public string $questionText;

    #[OA\Property(title: "count", type: 'integer', example: 10)]
    public int $count;

    public function __construct()
    {
    }

    public static function constructFromModel($answer): GetQuestionStatisticsResponseModel
    {
        $obj = new GetQuestionStatisticsResponseModel();
        $obj->answerId = $answer["answerId"];
        $obj->questionText = $answer["questionText"];
        $obj->count = $answer["count"];

        return $obj;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}