<?php declare(strict_types=1);

namespace Stuba\Models\Questions\ExportQuestion;

use JsonSerializable;
use DateTime;

class ExportVotingModel implements JsonSerializable
{
    public int $id;
    public DateTime $startDate;
    public DateTime $endDate;
    public string|null $note;
    public array $votes;

    public function __construct()
    {
        unset($this->startDate);
        unset($this->endDate);
    }

    public function __set($key, $value)
    {
        if ($key === 'startDate' && $value !== null) {
            $this->startDate = new DateTime($value);
        }
        if ($key === 'endDate' && $value !== null) {
            $this->endDate = new DateTime($value);
        }
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}