<?php

use Atanunu\XpressWallet\Http\Client\XpressWalletClient;
use Atanunu\XpressWallet\Models\ApiCallLog;
use Atanunu\XpressWallet\Services\TokenStore;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

it('masks tokens in stored logs', function () {
    config()->set('xpresswallet.log_bodies', true);
    app(TokenStore::class)->put('ACCESS_TOKEN_SAMPLE', 'REFRESH_TOKEN_SAMPLE');

    $mock = new MockHandler([
        new Response(200, [], json_encode(['ok' => true])),
    ]);
    $client = new Client(['handler' => HandlerStack::create($mock), 'base_uri' => 'https://example.com/']);
    $svc = new XpressWalletClient($client, app(TokenStore::class), config('xpresswallet'), app('log'));

    $svc->get('merchant/transactions');

    $log = ApiCallLog::query()->latest('id')->first();
    expect($log)->not->toBeNull();
    $headers = json_decode($log->request_headers, true);
    expect($headers['X-Access-Token'])->toContain('***MASKED***');
    expect($headers['X-Refresh-Token'])->toContain('***MASKED***');
});
