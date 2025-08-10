<?php

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;
use Atanunu\XpressWallet\Http\Client\XpressWalletClient;
use Atanunu\XpressWallet\Services\TokenStore;

it('paginates results and computes next page', function () {
    // Provide tokens first
    app(TokenStore::class)->put('acc','ref');
    $mock = new MockHandler([
        new Response(200, [], json_encode(['data' => range(1, 50)])), // page 1 full
        new Response(200, [], json_encode(['data' => range(51, 60)])), // page 2 partial
    ]);
    $client = new Client(['handler' => HandlerStack::create($mock), 'base_uri' => 'https://example.com/']);
    $svc = new XpressWalletClient($client, app(TokenStore::class), config('xpresswallet'), app('log'));

    $p1 = $svc->paginate('items', [], 1, 50);
    expect($p1['meta']['next_page'])->toBe(2);
    $p2 = $svc->paginate('items', [], 2, 50);
    expect($p2['meta']['next_page'])->toBeNull();
});
