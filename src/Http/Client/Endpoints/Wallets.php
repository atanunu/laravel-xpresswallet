<?php

namespace Atanunu\XpressWallet\Http\Client\Endpoints;

use Atanunu\XpressWallet\Http\Client\XpressWalletClient;
use GuzzleHttp\Client as Guzzle;

class Wallets
{
    public function __construct(protected XpressWalletClient $root, protected Guzzle $http) {}

    /**
     * @param  array<string,mixed>  $payload
     * @return array<string,mixed>
     */
    public function create(array $payload): array
    {
        return $this->root->post('wallet', $payload);
    }

    /** @return array<string,mixed> */
    public function all(): array
    {
        return $this->root->get('wallet');
    }

    /** @return array<string,mixed> */
    public function customerWallet(string $customerId): array
    {
        return $this->root->get('wallet/customer', ['customerId' => $customerId]);
    }

    /**
     * @param  array<string,mixed>  $payload
     * @return array<string,mixed>
     */
    public function credit(array $payload): array
    {
        return $this->root->post('wallet/credit', $payload);
    }

    /**
     * @param  array<string,mixed>  $payload
     * @return array<string,mixed>
     */
    public function debit(array $payload): array
    {
        return $this->root->post('wallet/debit', $payload);
    }

    /** @return array<string,mixed> */
    public function freeze(string $customerId): array
    {
        return $this->root->post('wallet/close', ['customerId' => $customerId]);
    }

    /** @return array<string,mixed> */
    public function unfreeze(string $customerId): array
    {
        return $this->root->post('wallet/enable', ['customerId' => $customerId]);
    }

    /**
     * @param  array<string,mixed>  $payload
     * @return array<string,mixed>
     */
    public function batchCredit(array $payload): array
    {
        return $this->root->post('wallet/batch-credit-customer-wallet', $payload);
    }

    /**
     * @param  array<string,mixed>  $payload
     * @return array<string,mixed>
     */
    public function batchDebit(array $payload): array
    {
        return $this->root->post('wallet/batch-debit-customer-wallet', $payload);
    }

    /**
     * @param  array<string,mixed>  $payload
     * @return array<string,mixed>
     */
    public function customerBatchCredit(array $payload): array
    {
        return $this->root->post('wallet/customer-batch-credit-customer-wallet', $payload);
    }

    /** @return array<string,mixed> */
    public function fundMerchantSandboxWallet(int $amount): array
    {
        return $this->root->post('merchant/fund-wallet', ['amount' => $amount]);
    }
}
