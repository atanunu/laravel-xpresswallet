<?php

namespace Atanunu\XpressWallet\Exceptions;

class ApiException extends XpressWalletException
{
    /** @param array<string,mixed> $context */
    public function __construct(string $message, public ?int $status = null, public array $context = [])
    {
        parent::__construct($message, $status ?? 0);
    }
}
