<?php declare(strict_types=1);

namespace Stuba\Models\Questions\ExportQuestion;

use JsonSerializable;

class ExportAnswerModel implements JsonSerializable
{
    public int $id;
    public string $text;
    public bool $isCorrect;

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}