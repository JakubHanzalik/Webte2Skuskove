<?php declare(strict_types=1);

namespace Stuba\Models\Questions\ExportQuestion;

use JsonSerializable;

class ExportVoteModel implements JsonSerializable
{
    public string $answerText;
    public int $count;

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}