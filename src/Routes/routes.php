<?php

namespace Atanunu\XpressWallet\Routes;

use Illuminate\Support\Facades\Route;
use Atanunu\XpressWallet\Http\Controllers\Examples\CustomerController;
use Atanunu\XpressWallet\Http\Controllers\Examples\WalletController;

function routes(): void
{
    Route::get('/customers', [CustomerController::class, 'index']);
    Route::get('/customers/{id}', [CustomerController::class, 'show']);
    Route::get('/wallets', [WalletController::class, 'index']);
    Route::post('/wallets/create', [WalletController::class, 'create']);
}