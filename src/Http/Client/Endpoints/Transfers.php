<?php

namespace Atanunu\XpressWallet\Http\Client\Endpoints;

use Atanunu\XpressWallet\Http\Client\XpressWalletClient;
use GuzzleHttp\Client as Guzzle;

/** Bank & wallet transfer initiation and lookup endpoints. */
class Transfers
{
    public function __construct(protected XpressWalletClient $root, protected Guzzle $http) {}

    /** @return array<string,mixed> */
    /** Retrieve supported banks list. */
    public function banks(): array
    {
        return $this->root->get('transfer/banks');
    }

    /** @return array<string,mixed> */
    /** Verify beneficiary account details. */
    public function accountDetails(string $sortCode, string $accountNumber): array
    {
        return $this->root->get('transfer/account/details', ['sortCode' => $sortCode, 'accountNumber' => $accountNumber]);
    }

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    /** Initiate single bank transfer (merchant funding). */
    public function bank(array $payload): array
    {
        return $this->root->post('transfer/bank', $payload);
    }

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    /** Initiate single bank transfer from customer context. */
    public function bankCustomer(array $payload): array
    {
        return $this->root->post('transfer/bank/customer', $payload);
    }

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    /** Initiate batch bank transfer. */
    public function bankBatch(array $payload): array
    {
        return $this->root->post('transfer/bank/batch', $payload);
    }

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    /** Transfer between wallets. */
    public function wallet(array $payload): array
    {
        return $this->root->post('transfer/wallet', $payload);
    }
}
