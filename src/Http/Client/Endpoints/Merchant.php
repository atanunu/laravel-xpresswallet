<?php

namespace Atanunu\XpressWallet\Http\Client\Endpoints;

use Atanunu\XpressWallet\Http\Client\XpressWalletClient;
use GuzzleHttp\Client as Guzzle;

/** Merchant profile, security, account mode & wallet summary endpoints. */
class Merchant
{
    public function __construct(protected XpressWalletClient $root, protected Guzzle $http) {}

    /** @return array<string,mixed> */
    /** Kick off merchant password change flow by sending reset code. */
    public function requestPasswordChange(string $email): array
    {
        return $this->root->post('merchant/request-merchant-password-change', ['email' => $email]);
    }

    /** @return array<string,mixed> */
    /** Complete password change using reset code. */
    public function changePassword(string $resetCode, string $password): array
    {
        return $this->root->post('merchant/change-merchant-password', ['resetCode' => $resetCode, 'password' => $password]);
    }

    /** @return array<string,mixed> */
    /** Verify merchant account using emailed code. */
    public function verify(string $code): array
    {
        return $this->root->post('merchant/verify', ['code' => $code]);
    }

    /** @return array<string,mixed> */
    /** Resend verification code; optional override email. */
    public function resendVerification(?string $email = null): array
    {
        $payload = $email ? ['email' => $email] : [];

        return $this->root->post('merchant/verify/resend', $payload);
    }

    /** @return array<string,mixed> */
    /** Resend activation code (pre‑verification). */
    public function resendActivation(?string $email = null): array
    {
        $payload = $email ? ['email' => $email] : [];

        return $this->root->post('merchant/resend-activation-code', $payload);
    }

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    /** Complete outstanding registration steps. */
    public function completeRegistration(array $payload): array
    {
        return $this->root->post('merchant/complete-merchant-registration', $payload);
    }

    /** @return array<string,mixed> */
    /** Fetch current merchant profile. */
    public function profile(): array
    {
        return $this->root->get('merchant/profile');
    }

    /** @return array<string,mixed> */
    /** List existing API access keys. */
    public function accessKeys(): array
    {
        return $this->root->get('merchant/my-access-keys');
    }

    /** @return array<string,mixed> */
    /** Generate new API access keys (rotation). */
    public function generateAccessKeys(): array
    {
        return $this->root->post('merchant/generate-access-keys');
    }

    /** @return array<string,mixed> */
    /** Retrieve current account mode (e.g., live / test). */
    public function accountMode(): array
    {
        return $this->root->get('merchant/account-mode');
    }

    /** @return array<string,mixed> */
    /** Switch account operational mode. */
    public function switchAccountMode(string $mode): array
    {
        return $this->root->patch('merchant/account-mode', ['mode' => $mode]);
    }

    /** @return array<string,mixed> */
    /** Merchant summary (high-level stats). */
    public function summary(): array
    {
        return $this->root->get('merchant');
    }

    /** @return array<string,mixed> */
    /** Merchant wallet balances & related info. */
    public function wallet(): array
    {
        return $this->root->get('merchant/wallet');
    }
}
