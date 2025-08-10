<?php

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client;
use Atanunu\XpressWallet\Http\Client\XpressWalletClient;
use Atanunu\XpressWallet\Services\TokenStore;
use Atanunu\XpressWallet\Exceptions\CircuitBreakerOpenException;

it('opens circuit breaker after threshold failures', function () {
    config()->set('xpresswallet.circuit_breaker.enabled', true);
    config()->set('xpresswallet.circuit_breaker.failure_threshold', 2);
    config()->set('xpresswallet.circuit_breaker.cool_down_seconds', 60);
    config()->set('xpresswallet.retries.max_attempts', 1); // simplify: one attempt per call
    app(TokenStore::class)->put('acc','ref');

    $mock = new MockHandler([
        new Response(500, [], ''),
        new Response(500, [], ''),
    ]);
    $client = new Client(['handler' => HandlerStack::create($mock), 'base_uri' => 'https://example.com/']);
    $svc = new XpressWalletClient($client, app(TokenStore::class), config('xpresswallet'), app('log'));
    // First failure
    try { $svc->get('unstable'); } catch (Throwable $e) {}
    // Second failure reaches threshold and opens breaker internally
    try { $svc->get('unstable'); } catch (Throwable $e) {}
    // Next call should short circuit immediately without consuming mock (breaker open)
    $this->expectException(CircuitBreakerOpenException::class);
    $svc->get('unstable');
});
