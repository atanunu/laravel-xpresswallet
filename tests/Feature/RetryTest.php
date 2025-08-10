<?php

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;
use Atanunu\XpressWallet\Http\Client\XpressWalletClient;
use Atanunu\XpressWallet\Services\TokenStore;

it('retries on transient 500 and succeeds', function() {
    // Seed tokens so headers attach
    app(TokenStore::class)->put('aX','rX');

    config()->set('xpresswallet.retries.max_attempts', 3);
    config()->set('xpresswallet.retries.initial_delay_ms', 1); // speed up test

    $mock = new MockHandler([
        new Response(500, [], ''),
        new Response(500, [], ''),
        new Response(200, [], json_encode(['ok' => true]))
    ]);
    $client = new Client(['handler' => HandlerStack::create($mock), 'base_uri' => 'https://example.com/']);
    $svc = new XpressWalletClient($client, app(TokenStore::class), config('xpresswallet'), app('log'));
    $res = $svc->get('merchant/transactions');
    expect($res['ok'])->toBeTrue();
});
