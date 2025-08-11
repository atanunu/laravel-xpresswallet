<?php

namespace Atanunu\XpressWallet\Http\Client\Endpoints;

use Atanunu\XpressWallet\Http\Client\XpressWalletClient;
use GuzzleHttp\Client as Guzzle;

/** Transaction listing & lookup endpoints. */
class Transactions
{
    public function __construct(protected XpressWalletClient $root, protected Guzzle $http) {}

    /**
     * @param  array<string,mixed>  $filters
     * @return array<string,mixed>
     */
    /** List merchant transactions with optional filter set. */
    public function merchant(array $filters = []): array
    {
        return $this->root->get('merchant/transactions', $filters);
    }

    /** @return array<string,mixed> */
    /** Retrieve transaction detail by reference. */
    public function details(string $reference): array
    {
        return $this->root->get("merchant/transaction/{$reference}");
    }
}
