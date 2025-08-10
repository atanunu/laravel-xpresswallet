<?php

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;
use Atanunu\XpressWallet\Http\Client\XpressWalletClient;
use Atanunu\XpressWallet\Services\TokenStore;

it('logs out successfully', function () {
    // Seed tokens
    app(TokenStore::class)->put('acc','ref');
    $mock = new MockHandler([
        new Response(200, [], json_encode(['status'=>true,'message'=>'Logged out'])),
    ]);
    $client = new Client(['handler' => HandlerStack::create($mock), 'base_uri' => 'https://example.com/']);
    $svc = new XpressWalletClient($client, app(TokenStore::class), config('xpresswallet'), app('log'));
    $res = $svc->logout();
    expect($res['status'])->toBeTrue();
});

it('requests password reset', function () {
    app(TokenStore::class)->put('acc','ref'); // not strictly needed for auth/password/forget
    $mock = new MockHandler([
        new Response(200, [], json_encode(['status'=>true,'message'=>'reset sent'])),
    ]);
    $client = new Client(['handler' => HandlerStack::create($mock), 'base_uri' => 'https://example.com/']);
    $svc = new XpressWalletClient($client, app(TokenStore::class), config('xpresswallet'), app('log'));
    $res = $svc->requestPasswordReset('user@example.com');
    expect($res['status'])->toBeTrue();
});

it('resets password', function () {
    app(TokenStore::class)->put('acc','ref');
    $mock = new MockHandler([
        new Response(200, [], json_encode(['status'=>true,'message'=>'password reset'])),
    ]);
    $client = new Client(['handler' => HandlerStack::create($mock), 'base_uri' => 'https://example.com/']);
    $svc = new XpressWalletClient($client, app(TokenStore::class), config('xpresswallet'), app('log'));
    $res = $svc->resetPassword('123456','newpass');
    expect($res['status'])->toBeTrue();
});

it('fetches user profile', function () {
    app(TokenStore::class)->put('acc','ref');
    $mock = new MockHandler([
        new Response(200, [], json_encode(['status'=>true,'data'=>['id'=>'abc']]))
    ]);
    $client = new Client(['handler' => HandlerStack::create($mock), 'base_uri' => 'https://example.com/']);
    $svc = new XpressWalletClient($client, app(TokenStore::class), config('xpresswallet'), app('log'));
    $res = $svc->user()->profile();
    expect($res['data']['id'])->toBe('abc');
});
