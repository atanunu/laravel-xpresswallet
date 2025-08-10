<?php

use Atanunu\XpressWallet\Http\Client\XpressWalletClient;
use Atanunu\XpressWallet\Services\TokenStore;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

it('caches get responses when enabled', function () {
    config()->set('xpresswallet.cache_get_ttl', 60);
    app(TokenStore::class)->put('acc', 'ref');
    $mock = new MockHandler([
        new Response(200, [], json_encode(['value' => 1])),
        new Response(200, [], json_encode(['value' => 2])), // should not be reached
    ]);
    $client = new Client(['handler' => HandlerStack::create($mock), 'base_uri' => 'https://example.com/']);
    $svc = new XpressWalletClient($client, app(TokenStore::class), config('xpresswallet'), app('log'));
    $first = $svc->get('resource');
    $second = $svc->get('resource');
    expect($first['value'])->toBe(1);
    expect($second['value'])->toBe(1);
});
