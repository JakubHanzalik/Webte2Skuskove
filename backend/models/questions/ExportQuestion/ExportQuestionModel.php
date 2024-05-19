<?php declare(strict_types=1);

namespace Stuba\Models\Questions\ExportQuestion;

use Stuba\Db\Models\Questions\EQuestionType;
use DateTime;
use JsonSerializable;


class ExportQuestionModel implements JsonSerializable
{
    public string $code;
    public string $text;
    public EQuestionType $type;
    public string $subject;
    public bool $isActive;
    public DateTime $creationDate;
    public string $author;
    public array $answers;
    public array $votings;

    public function __construct()
    {
        unset($this->type);
        unset($this->creationDate);
    }

    public function __set($key, $value)
    {
        if ($key === 'type') {
            $this->type = EQuestionType::from($value);
        }
        if ($key === 'creationDate') {
            $this->creationDate = new DateTime($value);
        }
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}