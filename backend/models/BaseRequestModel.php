<?php

declare(strict_types=1);

namespace Stuba\Models;

use Respect\Validation\Exceptions\NestedValidationException;

class BaseRequestModel
{
    protected $validator;

    public function isValid(): bool
    {
        return $this->validator->validate($this);
    }

    public function getErrors(): array
    {
        try {
            $this->validator->assert($this);
        } catch (NestedValidationException $exception) {
            return $exception->getMessages();
        }
        return [];
    }
}
