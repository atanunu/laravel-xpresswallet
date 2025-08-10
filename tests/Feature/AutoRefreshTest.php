<?php

use Atanunu\XpressWallet\Http\Client\XpressWalletClient;
use Atanunu\XpressWallet\Services\TokenStore;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

it('auto-refreshes on 401 and retries original request', function () {
    // First ensure we have initial tokens
    $loginMock = new MockHandler([
        new Response(200, ['X-Access-Token' => 'a1', 'X-Refresh-Token' => 'r1'], json_encode(['status' => true])),
    ]);
    $loginClient = new Client(['handler' => HandlerStack::create($loginMock), 'base_uri' => 'https://example.com/']);
    $svcForLogin = new XpressWalletClient($loginClient, app(TokenStore::class), config('xpresswallet'), app('log'));
    $svcForLogin->login('user@example.com', 'secret');

    // Now simulate 401 then success after refresh (refresh endpoint returns new tokens then original endpoint returns data)
    $sequence = new MockHandler([
        // Original protected call gets 401
        new Response(401, [], ''),
        // Refresh call succeeds
        new Response(200, ['X-Access-Token' => 'a2', 'X-Refresh-Token' => 'r2'], json_encode(['status' => true])),
        // Retried original call succeeds
        new Response(200, [], json_encode(['ok' => true])),
    ]);
    $client = new Client(['handler' => HandlerStack::create($sequence), 'base_uri' => 'https://example.com/']);
    $svc = new XpressWalletClient($client, app(TokenStore::class), config('xpresswallet'), app('log'));

    // Manually seed tokens from first login
    app(TokenStore::class)->put('a1', 'r1');

    $res = $svc->get('merchant/transactions');
    expect($res['ok'])->toBeTrue();
    expect(app(TokenStore::class)->access())->toBe('a2');
    expect(app(TokenStore::class)->refresh())->toBe('r2');
});
