<?php

namespace Atanunu\XpressWallet\Http\Client\Endpoints;

use Atanunu\XpressWallet\Http\Client\XpressWalletClient;
use GuzzleHttp\Client as Guzzle;

class Team
{
    public function __construct(protected XpressWalletClient $root, protected Guzzle $http) {}

    /** @param array<string,mixed> $query @return array<string,mixed> */
    public function invitations(array $query = []): array
    {
        return $this->root->get('team/invitations', $query);
    }

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    public function invite(array $payload): array
    {
        return $this->root->post('team/invitations', $payload);
    }

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    public function resendInvitation(array $payload): array
    {
        return $this->root->post('team/invitations/resend', $payload);
    }

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    public function acceptInvitation(array $payload): array
    {
        return $this->root->post('team/invitations/accept', $payload);
    }

    /** @return array<string,mixed> */
    public function members(): array
    {
        return $this->root->get('team/members');
    }

    /** @return array<string,mixed> */
    public function merchants(): array
    {
        return $this->root->get('team/merchants');
    }

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    public function switchMerchant(array $payload): array
    {
        return $this->root->post('team/merchants/switch', $payload);
    }

    /** @return array<string,mixed> */
    public function permissions(): array
    {
        return $this->root->get('team/permissions');
    }

    /** @return array<string,mixed> */
    public function roles(): array
    {
        return $this->root->get('team/roles');
    }
}
