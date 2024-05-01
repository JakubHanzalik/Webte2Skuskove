<?php declare(strict_types=1);
namespace Stuba\Exceptions;

use Exception;
use Throwable;

class APIException extends Exception
{
    /**
     * @param string $message [optional] The Exception message to throw.
     * @param Throwable|null $previous [optional] The previous throwable used for the exception chaining.
     * @param int $code [optional] The Exception code.
     */
    public function __construct(string $message, int $code)
    {
        parent::__construct($message, $code);
        $this->message = $message;
        $this->code = $code;
    }
}
