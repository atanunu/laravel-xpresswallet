<?php

namespace Atanunu\XpressWallet\Http\Client\Endpoints;

use Atanunu\XpressWallet\Http\Client\XpressWalletClient;
use GuzzleHttp\Client as Guzzle;

/** Customer endpoints: create, list, fetch & update customer resources. */
class Customers
{
    public function __construct(protected XpressWalletClient $root, protected Guzzle $http) {}

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    /** Create a new customer record. */
    public function create(array $payload): array
    {
        return $this->root->post('customer', $payload);
    }

    /** @return array<string,mixed> */
    /** List customers with simple page support (server-side pagination). */
    public function all(int $page = 1): array
    {
        return $this->root->get('customer', ['page' => $page]);
    }

    /** @return array<string,mixed> */
    /** Retrieve a customer by its unique identifier. */
    public function findById(string $customerId): array
    {
        return $this->root->get("customer/{$customerId}");
    }

    /** @return array<string,mixed> */
    /** Retrieve a customer via phone number lookup. */
    public function findByPhone(string $phone): array
    {
        return $this->root->get('customer/phone', ['phoneNumber' => $phone]);
    }

    /**
     * @param  array<string,mixed>  $payload
     * @return array<string,mixed>
     */
    public function update(string $customerId, array $payload): array
    {
        return $this->root->put("customer/{$customerId}", $payload);
    }
}
