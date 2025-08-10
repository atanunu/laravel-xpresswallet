<?php

namespace Atanunu\XpressWallet\Http\Client\Endpoints;

use Atanunu\XpressWallet\Http\Client\XpressWalletClient;
use GuzzleHttp\Client as Guzzle;

class Transactions
{
    public function __construct(protected XpressWalletClient $root, protected Guzzle $http) {}

    /**
     * @param  array<string,mixed>  $filters
     * @return array<string,mixed>
     */
    public function merchant(array $filters = []): array
    {
        return $this->root->get('merchant/transactions', $filters);
    }

    /** @return array<string,mixed> */
    public function details(string $reference): array
    {
        return $this->root->get("merchant/transaction/{$reference}");
    }
}
