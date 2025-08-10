<?php

use Atanunu\XpressWallet\Exceptions\PasswordChangeException;
use Atanunu\XpressWallet\Exceptions\PasswordResetException;
use Atanunu\XpressWallet\Exceptions\VerificationException;
use Atanunu\XpressWallet\Http\Client\XpressWalletClient;
use Atanunu\XpressWallet\Services\TokenStore;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

it('throws PasswordChangeException on user password failure', function () {
    $mock = new MockHandler([
        // login success
        new Response(200, ['X-Access-Token' => 'a', 'X-Refresh-Token' => 'r'], json_encode(['status' => true])),
        // password change 422
        new Response(422, [], json_encode(['status' => false, 'message' => 'Weak password'])),
    ]);
    $client = new Client(['handler' => HandlerStack::create($mock), 'base_uri' => 'https://example.com/']);
    $svc = new XpressWalletClient($client, app(TokenStore::class), config('xpresswallet'), app('log'));
    $svc->login('e@example.com', 'secret');
    $svc->user()->changePassword('secret', 'short');
})->throws(PasswordChangeException::class);

it('throws PasswordResetException on reset failure', function () {
    $mock = new MockHandler([
        new Response(422, [], json_encode(['status' => false, 'message' => 'Invalid reset code'])),
    ]);
    $client = new Client(['handler' => HandlerStack::create($mock), 'base_uri' => 'https://example.com/']);
    $svc = new XpressWalletClient($client, app(TokenStore::class), config('xpresswallet'), app('log'));
    $svc->resetPassword('bad', 'NewPassword123!');
})->throws(PasswordResetException::class);

it('throws VerificationException on verify failure', function () {
    $mock = new MockHandler([
        // login first so tokens present
        new Response(200, ['X-Access-Token' => 'a', 'X-Refresh-Token' => 'r'], json_encode(['status' => true])),
        new Response(422, [], json_encode(['status' => false, 'message' => 'Invalid code'])),
    ]);
    $client = new Client(['handler' => HandlerStack::create($mock), 'base_uri' => 'https://example.com/']);
    $svc = new XpressWalletClient($client, app(TokenStore::class), config('xpresswallet'), app('log'));
    $svc->login('e@example.com', 'secret');
    $svc->merchant()->verify('999999');
})->throws(VerificationException::class);
