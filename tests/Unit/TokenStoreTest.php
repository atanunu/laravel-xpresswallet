<?php

use Atanunu\XpressWallet\Services\TokenStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

it('stores and retrieves tokens', function () {
    $store = app(TokenStore::class);
    $store->put('a-token', 'r-token');
    expect($store->access())->toBe('a-token');
    expect($store->refresh())->toBe('r-token');
});