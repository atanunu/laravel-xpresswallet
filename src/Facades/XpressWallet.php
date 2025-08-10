<?php

namespace Atanunu\XpressWallet\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Atanunu\XpressWallet\Http\Client\XpressWalletClient client()
 * @method static array login(?string $email = null, ?string $password = null)
 * @method static array refresh()
 * @method static \Atanunu\XpressWallet\Http\Client\Endpoints\Customers customers()
 * @method static \Atanunu\XpressWallet\Http\Client\Endpoints\Wallets wallets()
 * @method static \Atanunu\XpressWallet\Http\Client\Endpoints\Transactions transactions()
 */
class XpressWallet extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Atanunu\XpressWallet\Contracts\XpressWalletClientContract::class;
    }
}