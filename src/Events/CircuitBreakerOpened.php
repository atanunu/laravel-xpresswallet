<?php

namespace Atanunu\XpressWallet\Events;

class CircuitBreakerOpened
{
    public function __construct(public string $url) {}
}
