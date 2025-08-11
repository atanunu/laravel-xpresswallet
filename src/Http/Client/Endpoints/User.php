<?php

namespace Atanunu\XpressWallet\Http\Client\Endpoints;

use Atanunu\XpressWallet\Http\Client\XpressWalletClient;
use GuzzleHttp\Client as Guzzle;

/** Authenticated user profile & security endpoints. */
class User
{
    public function __construct(protected XpressWalletClient $root, protected Guzzle $http) {}

    /** @return array<string,mixed> */
    /** Retrieve user profile details. */
    public function profile(): array
    {
        return $this->root->get('user/profile');
    }

    /** @return array<string,mixed> */
    /** Change current user password. */
    public function changePassword(string $currentPassword, string $newPassword): array
    {
        return $this->root->put('user/password', [
            'currentPassword' => $currentPassword,
            'newPassword' => $newPassword,
        ]);
    }
}
