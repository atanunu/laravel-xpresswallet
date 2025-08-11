<?php

namespace Atanunu\XpressWallet;

use Atanunu\XpressWallet\Contracts\XpressWalletClientContract;
use Atanunu\XpressWallet\Http\Client\XpressWalletClient;
use Atanunu\XpressWallet\Services\TokenStore;
use GuzzleHttp\Client as Guzzle;
use Illuminate\Support\ServiceProvider;

/**
 * Package service provider registering config, HTTP client binding, token store and console commands.
 *
 * Highlights:
 * - Merges package configuration allowing user overrides.
 * - Binds TokenStore (DB + cache) and XpressWalletClient (Guzzle wrapper with resilience features).
 * - Aliases contract to concrete for convenience.
 * - Conditionally loads helper API routes when enabled & routes not cached to avoid redeclaration.
 */
class XpressWalletServiceProvider extends ServiceProvider
{
    /** Register container bindings & merge configuration. */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/xpresswallet.php', 'xpresswallet');

        $this->app->singleton(TokenStore::class, function ($app) {
            return new TokenStore(
                cache: $app['cache']->store(),
                db: $app['db'], // DatabaseManager acceptable; TokenStore expects DatabaseManager alias
                config: $app['config']->get('xpresswallet')
            );
        });

        $this->app->singleton(XpressWalletClientContract::class, function ($app) {
            $cfg = $app['config']->get('xpresswallet');
            $guzzle = new Guzzle([
                'base_uri' => rtrim($cfg['base_url'], '/').'/',
                'timeout' => 30,
            ]);

            return new XpressWalletClient(
                http: $guzzle,
                tokens: $app->make(TokenStore::class),
                config: $cfg,
                logger: $app['log']
            );
        });

        // Backward compatibility: allow direct resolution of concrete class
        $this->app->alias(XpressWalletClientContract::class, XpressWalletClient::class);
    }

    /** Bootstrap publishing assets, console commands, and optional routes. */
    public function boot(): void
    {
        // Publish config and migrations
        $this->publishes([
            __DIR__.'/../config/xpresswallet.php' => config_path('xpresswallet.php'),
        ], 'xpresswallet-config');

        if (! class_exists('CreateXpressTokensTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/2025_08_10_000000_create_xpress_tokens_table.php' => database_path('migrations/2025_08_10_000000_create_xpress_tokens_table.php'),
                __DIR__.'/../database/migrations/2025_08_10_000100_create_api_call_logs_table.php' => database_path('migrations/2025_08_10_000100_create_api_call_logs_table.php'),
                __DIR__.'/../database/migrations/2025_08_10_000200_create_webhook_events_table.php' => database_path('migrations/2025_08_10_000200_create_webhook_events_table.php'),
            ], 'xpresswallet-migrations');
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Atanunu\XpressWallet\Commands\XpressLoginCommand::class,
                \Atanunu\XpressWallet\Commands\XpressRefreshTokensCommand::class,
                \Atanunu\XpressWallet\Commands\XpressPingCommand::class,
                \Atanunu\XpressWallet\Commands\XpressPruneCommand::class,
                \Atanunu\XpressWallet\Commands\XpressValidateConfigCommand::class,
            ]);
        }

        // Conditionally register package routes
        $routesCfg = $this->app['config']->get('xpresswallet.routes');
        if (($routesCfg['enabled'] ?? false) && ! $this->app->routesAreCached()) {
            // Isolate to avoid global function name collisions
            require __DIR__.'/Routes/routes.php';
            \Atanunu\XpressWallet\Routes\routes();
        }
    }
}
