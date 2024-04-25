<?php

namespace Stuba\Models\Questions;

use OpenApi\Attributes as OA;

#[OA\Schema(type: 'integer', enum: ['single_choice', 'multiple_choice', 'text'], example: '0')]
enum EQuestionType: int
{
    case SINGLE_CHOICE = 0;

    case MULTIPLE_CHOICE = 1;

    case TEXT = 2;
}