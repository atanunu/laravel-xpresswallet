<?php

namespace Atanunu\XpressWallet\Http\Client\Endpoints;

use Atanunu\XpressWallet\Http\Client\XpressWalletClient;
use GuzzleHttp\Client as Guzzle;

class Merchant
{
    public function __construct(protected XpressWalletClient $root, protected Guzzle $http) {}

    /** @return array<string,mixed> */
    public function requestPasswordChange(string $email): array
    {
        return $this->root->post('merchant/request-merchant-password-change', ['email' => $email]);
    }

    /** @return array<string,mixed> */
    public function changePassword(string $resetCode, string $password): array
    {
        return $this->root->post('merchant/change-merchant-password', ['resetCode' => $resetCode, 'password' => $password]);
    }

    /** @return array<string,mixed> */
    public function verify(string $code): array
    {
        return $this->root->post('merchant/verify', ['code' => $code]);
    }

    /** @return array<string,mixed> */
    public function resendVerification(?string $email = null): array
    {
        $payload = $email ? ['email' => $email] : [];

        return $this->root->post('merchant/verify/resend', $payload);
    }

    /** @return array<string,mixed> */
    public function resendActivation(?string $email = null): array
    {
        $payload = $email ? ['email' => $email] : [];

        return $this->root->post('merchant/resend-activation-code', $payload);
    }

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    public function completeRegistration(array $payload): array
    {
        return $this->root->post('merchant/complete-merchant-registration', $payload);
    }

    /** @return array<string,mixed> */
    public function profile(): array
    {
        return $this->root->get('merchant/profile');
    }

    /** @return array<string,mixed> */
    public function accessKeys(): array
    {
        return $this->root->get('merchant/my-access-keys');
    }

    /** @return array<string,mixed> */
    public function generateAccessKeys(): array
    {
        return $this->root->post('merchant/generate-access-keys');
    }

    /** @return array<string,mixed> */
    public function accountMode(): array
    {
        return $this->root->get('merchant/account-mode');
    }

    /** @return array<string,mixed> */
    public function switchAccountMode(string $mode): array
    {
        return $this->root->patch('merchant/account-mode', ['mode' => $mode]);
    }

    /** @return array<string,mixed> */
    public function summary(): array
    {
        return $this->root->get('merchant');
    }

    /** @return array<string,mixed> */
    public function wallet(): array
    {
        return $this->root->get('merchant/wallet');
    }
}
