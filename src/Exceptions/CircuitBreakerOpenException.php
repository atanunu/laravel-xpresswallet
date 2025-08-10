<?php

namespace Atanunu\XpressWallet\Exceptions;

class CircuitBreakerOpenException extends ApiException
{
    public function __construct(string $message = 'Circuit breaker is open; calls temporarily blocked.')
    {
        parent::__construct($message, 0, []);
    }
}
