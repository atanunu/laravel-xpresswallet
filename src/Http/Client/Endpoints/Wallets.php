<?php

namespace Atanunu\XpressWallet\Http\Client\Endpoints;

use Atanunu\XpressWallet\Http\Client\XpressWalletClient;
use GuzzleHttp\Client as Guzzle;

/** Wallet management endpoints: create, credit/debit, batch operations, lifecycle. */
class Wallets
{
    public function __construct(protected XpressWalletClient $root, protected Guzzle $http) {}

    /**
     * @param  array<string,mixed>  $payload
     * @return array<string,mixed>
     */
    /** Create a new wallet record. */
    public function create(array $payload): array
    {
        return $this->root->post('wallet', $payload);
    }

    /** @return array<string,mixed> */
    /** List all wallets for current merchant. */
    public function all(): array
    {
        return $this->root->get('wallet');
    }

    /** @return array<string,mixed> */
    /** Get wallet details for a specific customer. */
    public function customerWallet(string $customerId): array
    {
        return $this->root->get('wallet/customer', ['customerId' => $customerId]);
    }

    /**
     * @param  array<string,mixed>  $payload
     * @return array<string,mixed>
     */
    /** Credit (increase balance) of a customer wallet. */
    public function credit(array $payload): array
    {
        return $this->root->post('wallet/credit', $payload);
    }

    /**
     * @param  array<string,mixed>  $payload
     * @return array<string,mixed>
     */
    /** Debit (decrease balance) of a customer wallet. */
    public function debit(array $payload): array
    {
        return $this->root->post('wallet/debit', $payload);
    }

    /** @return array<string,mixed> */
    /** Freeze / close a wallet temporarily. */
    public function freeze(string $customerId): array
    {
        return $this->root->post('wallet/close', ['customerId' => $customerId]);
    }

    /** @return array<string,mixed> */
    /** Unfreeze / re-enable a wallet. */
    public function unfreeze(string $customerId): array
    {
        return $this->root->post('wallet/enable', ['customerId' => $customerId]);
    }

    /**
     * @param  array<string,mixed>  $payload
     * @return array<string,mixed>
     */
    /** Batch credit operation across multiple wallets. */
    public function batchCredit(array $payload): array
    {
        return $this->root->post('wallet/batch-credit-customer-wallet', $payload);
    }

    /**
     * @param  array<string,mixed>  $payload
     * @return array<string,mixed>
     */
    /** Batch debit operation across multiple wallets. */
    public function batchDebit(array $payload): array
    {
        return $this->root->post('wallet/batch-debit-customer-wallet', $payload);
    }

    /**
     * @param  array<string,mixed>  $payload
     * @return array<string,mixed>
     */
    /** Batch credit targeted specifically at customer wallets. */
    public function customerBatchCredit(array $payload): array
    {
        return $this->root->post('wallet/customer-batch-credit-customer-wallet', $payload);
    }

    /** @return array<string,mixed> */
    /** Add sandbox test funds to merchant wallet (non-production convenience). */
    public function fundMerchantSandboxWallet(int $amount): array
    {
        return $this->root->post('merchant/fund-wallet', ['amount' => $amount]);
    }
}
