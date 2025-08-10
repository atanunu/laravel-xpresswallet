<?php

use Atanunu\XpressWallet\Exceptions\RateLimitException;
use Atanunu\XpressWallet\Http\Client\XpressWalletClient;
use Atanunu\XpressWallet\Services\TokenStore;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

it('retries on 429 then throws RateLimitException', function () {
    config()->set('xpresswallet.retries.rate_limit_max_attempts', 3);
    app(TokenStore::class)->put('acc', 'ref');

    $mock = new MockHandler([
        new Response(429, ['Retry-After' => '0'], ''),
        new Response(429, ['Retry-After' => '0'], ''),
        new Response(429, ['Retry-After' => '0'], ''), // after max attempts, should throw
    ]);
    $client = new Client(['handler' => HandlerStack::create($mock), 'base_uri' => 'https://example.com/']);
    $svc = new XpressWalletClient($client, app(TokenStore::class), config('xpresswallet'), app('log'));
    $svc->get('ratelimited');
})->throws(RateLimitException::class);
