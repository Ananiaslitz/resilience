<?php

namespace Resilience\RateLimiter\Exceptions;

use RuntimeException;
use Throwable;

class RateLimitExceededException extends RuntimeException
{
    public function __construct(
        string $message = "Rate limit exceeded",
        int $code = 429,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
