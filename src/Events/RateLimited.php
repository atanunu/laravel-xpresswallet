<?php

namespace Atanunu\XpressWallet\Events;

class RateLimited
{
    public function __construct(public string $method, public string $url, public int $attempt, public ?int $retryAfterSeconds) {}
}
