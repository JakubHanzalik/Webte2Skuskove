<?php

declare(strict_types=1);

namespace Stuba\Db\Models\Questions;

use DateTime;

class QuestionModel
{
    public string $question_code;

    public bool $active;

    public string $question;

    public EQuestionType $response_type;

    public int $subject_id;

    public DateTime $creation_date;

    public int $author_id;

    public function __construct()
    {
        unset($this->response_type);
        unset($this->creation_date);
    }

    public function __set($key, $value)
    {
        if ($key === 'response_type' && !empty($value)) {
            $this->response_type = EQuestionType::from($value);
        }
        if ($key === 'creation_date' && !empty($value)) {
            $this->creation_date = new DateTime($value);
        }
    }
}
