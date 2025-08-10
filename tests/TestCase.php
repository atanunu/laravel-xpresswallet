<?php

namespace Atanunu\XpressWallet\Tests;

use Orchestra\Testbench\TestCase as Base;
use Atanunu\XpressWallet\XpressWalletServiceProvider;

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