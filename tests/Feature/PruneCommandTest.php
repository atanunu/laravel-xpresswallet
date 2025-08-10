<?php

use Atanunu\XpressWallet\Models\ApiCallLog;
use Atanunu\XpressWallet\Models\XpressToken;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

it('prunes old logs and excess tokens', function () {
    // create older logs
    ApiCallLog::query()->create([
        'idempotency_key' => (string) Str::uuid(),
        'method' => 'GET', 'url' => 't', 'succeeded' => true,
    ]);
    $log = ApiCallLog::first();
    $log->created_at = Carbon::now()->subDays(400);
    $log->save();

    // create many tokens
    foreach (range(1, 60) as $i) {
        XpressToken::query()->create(['access_token' => 'a'.$i, 'refresh_token' => 'r'.$i]);
    }

    config()->set('xpresswallet.max_tokens', 10);
    config()->set('xpresswallet.retention_days', 30);

    Artisan::call('xpress:prune');

    expect(ApiCallLog::count())->toBe(0);
    expect(XpressToken::count())->toBe(10);
});
