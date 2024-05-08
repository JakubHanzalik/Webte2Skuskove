<?php

declare(strict_types=1);

namespace Stuba\Models\Questions\UpdateQuestion;

use OpenApi\Attributes as OA;
use Stuba\Models\BaseRequestModel;

#[OA\Schema(title: 'UpdateQuestionAnswerRequestModel', schema: 'UpdateQuestionAnswerRequestModel', type: 'object')]
class UpdateQuestionAnswerRequestModel extends BaseRequestModel
{
    #[OA\Property(title: "answer", type: 'string', example: "Dobrý")]
    public string $answer;

    #[OA\Property(title: "correct", type: 'bool', example: true)]
    public bool|null $correct;

    public function __construct($answer)
    {
        $this->answer = $answer["answer"] ?? "";
        $this->correct = $answer["correct"] ?? null;
    }
}
