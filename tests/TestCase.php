<?php

namespace Atanunu\XpressWallet\Tests;

use Atanunu\XpressWallet\XpressWalletServiceProvider;
use Orchestra\Testbench\TestCase as Base;

class TestCase extends Base
{
    protected function getPackageProviders($app)
    {
        return [XpressWalletServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Load package migrations so test DB has required tables
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
