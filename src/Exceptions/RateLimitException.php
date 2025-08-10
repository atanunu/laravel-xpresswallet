<?php

namespace Atanunu\XpressWallet\Exceptions;

class RateLimitException extends ApiException
{
    /** @param array<string,mixed> $context */
    public function __construct(string $message, public ?int $retryAfterSeconds = null, array $context = [])
    {
        parent::__construct($message, 429, $context);
    }
}
