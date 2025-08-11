<?php

namespace Atanunu\XpressWallet\Http\Client\Endpoints;

use Atanunu\XpressWallet\Http\Client\XpressWalletClient;
use GuzzleHttp\Client as Guzzle;

/** Card lifecycle & funding endpoints. */
class Cards
{
    public function __construct(protected XpressWalletClient $root, protected Guzzle $http) {}

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    /** Initialize card creation/setup process. */
    public function setup(array $payload): array
    {
        return $this->root->post('card/setup', $payload);
    }

    /** @param array<string,mixed> $filters @return array<string,mixed> */
    /** List cards with optional filters. */
    public function all(array $filters = []): array
    {
        return $this->root->get('card', $filters);
    }

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    /** Activate a provisioned card. */
    public function activate(array $payload): array
    {
        return $this->root->post('card/activate', $payload);
    }

    /** @param array<string,mixed> $filters @return array<string,mixed> */
    /** Retrieve balance for a card. */
    public function balance(array $filters): array
    {
        return $this->root->get('card/balance', $filters);
    }

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    /** Fund / top-up a card. */
    public function fund(array $payload): array
    {
        return $this->root->post('card/fund', $payload);
    }
}
