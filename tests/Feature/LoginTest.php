<?php

use Atanunu\XpressWallet\Http\Client\XpressWalletClient;
use Atanunu\XpressWallet\Services\TokenStore;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

it('logs in and stores tokens', function () {
    $mock = new MockHandler([
        new Response(200, ['X-Access-Token' => 'access123', 'X-Refresh-Token' => 'refresh123'], json_encode(['status' => true])),
    ]);
    $client = new Client(['handler' => HandlerStack::create($mock), 'base_uri' => 'https://example.com/']);
    $svc = new XpressWalletClient($client, app(TokenStore::class), config('xpresswallet'), app('log'));
    $res = $svc->login('user@example.com', 'secret');
    expect($res['status'])->toBeTrue();
    expect(app(TokenStore::class)->access())->toBe('access123');
    expect(app(TokenStore::class)->refresh())->toBe('refresh123');
});
