<?php

use Atanunu\XpressWallet\Services\TokenStore;

it('stores and retrieves tokens', function () {
    $store = app(TokenStore::class);
    $store->put('a-token', 'r-token');
    expect($store->access())->toBe('a-token');
    expect($store->refresh())->toBe('r-token');
});
