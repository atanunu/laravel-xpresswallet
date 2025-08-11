<?php

namespace Atanunu\XpressWallet\Http\Client\Endpoints;

use Atanunu\XpressWallet\Http\Client\XpressWalletClient;
use GuzzleHttp\Client as Guzzle;

/** Team collaboration endpoints: invitations, membership, role & merchant switching. */
class Team
{
    public function __construct(protected XpressWalletClient $root, protected Guzzle $http) {}

    /** @param array<string,mixed> $query @return array<string,mixed> */
    /** List pending invitations (filterable). */
    public function invitations(array $query = []): array
    {
        return $this->root->get('team/invitations', $query);
    }

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    /** Invite a new team member. */
    public function invite(array $payload): array
    {
        return $this->root->post('team/invitations', $payload);
    }

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    /** Resend invitation token to prospective member. */
    public function resendInvitation(array $payload): array
    {
        return $this->root->post('team/invitations/resend', $payload);
    }

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    /** Accept an existing invitation. */
    public function acceptInvitation(array $payload): array
    {
        return $this->root->post('team/invitations/accept', $payload);
    }

    /** @return array<string,mixed> */
    /** List team members. */
    public function members(): array
    {
        return $this->root->get('team/members');
    }

    /** @return array<string,mixed> */
    /** List merchants available to current user context. */
    public function merchants(): array
    {
        return $this->root->get('team/merchants');
    }

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    /** Switch active merchant context. */
    public function switchMerchant(array $payload): array
    {
        return $this->root->post('team/merchants/switch', $payload);
    }

    /** @return array<string,mixed> */
    /** Enumerate available permissions. */
    public function permissions(): array
    {
        return $this->root->get('team/permissions');
    }

    /** @return array<string,mixed> */
    /** Enumerate defined roles. */
    public function roles(): array
    {
        return $this->root->get('team/roles');
    }
}
