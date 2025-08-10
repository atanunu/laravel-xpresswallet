<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use Atanunu\XpressWallet\Models\WebhookEvent;
use Atanunu\XpressWallet\Http\Middleware\VerifyXpressWebhook;

it('accepts valid webhook and stores event', function() {
    Config::set('xpresswallet.webhook.secret', 'testsecret');

    Route::post('/_test/webhook', function(Request $r) {
        return response()->json(['ok' => true]);
    })->middleware(VerifyXpressWebhook::class);

    $timestamp = (string) time();
    $arrayPayload = ['event' => 'wallet.updated', 'data' => ['id' => 1]];
    $jsonPayload = json_encode($arrayPayload);
    $sig = hash_hmac('sha256', $timestamp.'.'.$jsonPayload, 'testsecret');

    $res = $this->postJson('/_test/webhook', $arrayPayload, [
        Config::get('xpresswallet.webhook.signature_header') => $timestamp.'.'.$sig,
    ]);

    $res->assertStatus(200);
    expect(WebhookEvent::count())->toBe(1);
});

it('rejects invalid signature', function() {
    Config::set('xpresswallet.webhook.secret', 'testsecret');

    Route::post('/_test/webhook2', function(Request $r) {
        return response()->json(['ok' => true]);
    })->middleware(VerifyXpressWebhook::class);

    $res = $this->postJson('/_test/webhook2', ['event' => 'wallet.updated'], [
        Config::get('xpresswallet.webhook.signature_header') => 'badformat',
    ]);
    $res->assertStatus(400);
    expect(WebhookEvent::count())->toBe(0);
});
