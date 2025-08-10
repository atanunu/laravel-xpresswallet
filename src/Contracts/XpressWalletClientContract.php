<?php

namespace Atanunu\XpressWallet\Contracts;

interface XpressWalletClientContract
{
    /**
     * Authenticate and store access/refresh tokens.
     */
    /**
     * @return array<string,mixed>
     */
    public function login(?string $email = null, ?string $password = null): array;

    /**
     * Refresh tokens using the stored refresh token.
     */
    /**
     * @return array<string,mixed>
     */
    public function refresh(): array;

    /** Endpoint groups */
    public function customers(): \Atanunu\XpressWallet\Http\Client\Endpoints\Customers;

    public function wallets(): \Atanunu\XpressWallet\Http\Client\Endpoints\Wallets;

    public function transactions(): \Atanunu\XpressWallet\Http\Client\Endpoints\Transactions;

    /** Low-level HTTP helpers (array decoded JSON). */
    /**
     * @param  array<string,mixed>  $query
     * @return array<string,mixed>
     */
    public function get(string $uri, array $query = []): array;

    /**
     * @param  array<string,mixed>  $payload
     * @return array<string,mixed>
     */
    public function post(string $uri, array $payload = []): array;

    /**
     * @param  array<string,mixed>  $payload
     * @return array<string,mixed>
     */
    public function put(string $uri, array $payload = []): array;
}
