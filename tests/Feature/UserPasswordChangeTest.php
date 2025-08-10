<?php

use Atanunu\XpressWallet\Http\Client\XpressWalletClient;
use Atanunu\XpressWallet\Services\TokenStore;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

it('changes user password', function () {
    $mock = new MockHandler([
        // First call: login
        new Response(200, ['X-Access-Token' => 'a1', 'X-Refresh-Token' => 'r1'], json_encode(['status' => true])),
        // Second call: change password
        new Response(200, [], json_encode(['status' => true, 'message' => 'Password updated'])),
    ]);
    $client = new Client(['handler' => HandlerStack::create($mock), 'base_uri' => 'https://example.com/']);
    $svc = new XpressWalletClient($client, app(TokenStore::class), config('xpresswallet'), app('log'));
    $svc->login('user@example.com', 'oldpass');
    $res = $svc->user()->changePassword('oldpass', 'newpass');
    expect($res['status'])->toBeTrue();
    expect($res['message'])->toBe('Password updated');
});
