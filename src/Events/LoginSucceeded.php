<?php

namespace Atanunu\XpressWallet\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoginSucceeded
{
    use Dispatchable, SerializesModels;

    /**
     * @param array<string,mixed> $payload
     */
    public function __construct(public array $payload) {}
}
