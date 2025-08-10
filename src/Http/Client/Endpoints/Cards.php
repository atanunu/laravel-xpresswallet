<?php

namespace Atanunu\XpressWallet\Http\Client\Endpoints;

use Atanunu\XpressWallet\Http\Client\XpressWalletClient;
use GuzzleHttp\Client as Guzzle;

class Cards
{
    public function __construct(protected XpressWalletClient $root, protected Guzzle $http) {}

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    public function setup(array $payload): array
    {
        return $this->root->post('card/setup', $payload);
    }

    /** @param array<string,mixed> $filters @return array<string,mixed> */
    public function all(array $filters = []): array
    {
        return $this->root->get('card', $filters);
    }

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    public function activate(array $payload): array
    {
        return $this->root->post('card/activate', $payload);
    }

    /** @param array<string,mixed> $filters @return array<string,mixed> */
    public function balance(array $filters): array
    {
        return $this->root->get('card/balance', $filters);
    }

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    public function fund(array $payload): array
    {
        return $this->root->post('card/fund', $payload);
    }
}
