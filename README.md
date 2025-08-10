# Atanunu Laravel XpressWallet

![CI](https://github.com/atanunu/laravel-xpresswallet/actions/workflows/ci.yml/badge.svg)
![Coverage](https://codecov.io/gh/atanunu/laravel-xpresswallet/branch/main/graph/badge.svg)
![Security Audit](https://github.com/atanunu/laravel-xpresswallet/actions/workflows/security-audit.yml/badge.svg)
![License](https://img.shields.io/github/license/atanunu/laravel-xpresswallet)
[![Endpoints Dashboard](https://img.shields.io/badge/docs-endpoints%20dashboard-blue)](https://atanunu.github.io/laravel-xpresswallet/)

A Laravel package to integrate with Providus Xpress Wallet API. It handles:
* Login and token refresh (X-Access-Token / X-Refresh-Token)
* Secure token storage (DB) + caching
* Request/response logging & auditing (masking, truncation, correlation IDs)
* Automatic retries with exponential (full-jitter) backoff & 429 handling
* Automatic 401 refresh
* Circuit breaker (configurable failure threshold & cool-down)
* Rate limiting detection & events
* Optional GET response caching
* Idempotency-Key automatic header for unsafe methods
* Pagination helper (`paginate()`)
* Webhook verification (multi-secret rotation & optional async queue)
* OpenTelemetry tracing (optional)
* Example controllers & routes
* Pruning command for old logs & limiting token history
* Publishable config & migrations
* Testbench-powered tests, GitHub Actions CI, PHPStan level 8, mutation testing config
* Public endpoint coverage dashboard: https://atanunu.github.io/laravel-xpresswallet/

## Installation

```bash
composer require atanunu/laravel-xpresswallet
php artisan vendor:publish --provider="Atanunu\XpressWallet\XpressWalletServiceProvider" --tag=xpresswallet-config
php artisan vendor:publish --provider="Atanunu\XpressWallet\XpressWalletServiceProvider" --tag=xpresswallet-migrations
php artisan migrate
```

## Configuration Overview

Once published, open `config/xpresswallet.php`. Key sections & env flags:

Core Credentials:
* `XPRESSWALLET_BASE_URL` – API base (e.g. sandbox URL)
* `XPRESSWALLET_EMAIL`, `XPRESSWALLET_PASSWORD` – raw credentials (auto base64 on login)

Retry & Backoff:
* `XPRESSWALLET_RETRY_ATTEMPTS` (default 2 total attempts)
* `XPRESSWALLET_RETRY_DELAY` / `XPRESSWALLET_RETRY_MAX_DELAY`
* `XPRESSWALLET_RETRY_FULL_JITTER` (bool) – full jitter algorithm
* `XPRESSWALLET_RATE_LIMIT_ATTEMPTS` – separate cap for 429 retries

Authentication & Tokens:
* `XPRESSWALLET_AUTO_REFRESH` – auto refresh on first 401
* Cache TTL & keys under `cache` array

Logging:
* `XPRESSWALLET_LOG_BODIES` – log raw bodies (careful in prod)
* `XPRESSWALLET_MAX_BODY` – truncate length
* `XPRESSWALLET_MASK_TOKENS` – mask auth headers

Retention & Pruning:
* `XPRESSWALLET_RETENTION_DAYS` – older logs/webhooks removed by prune command
* `XPRESSWALLET_MAX_TOKENS` – keep only latest N token rows

Webhooks:
* `XPRESSWALLET_WEBHOOK_SECRET` – primary secret (legacy)
* `XPRESSWALLET_WEBHOOK_SECRETS` – comma-separated list for rotation
* `XPRESSWALLET_WEBHOOK_SIGNATURE_HEADER`
* `XPRESSWALLET_WEBHOOK_TOLERANCE`
* `XPRESSWALLET_WEBHOOK_ASYNC` – queue processing (provide queue config)

Response Caching:
* `XPRESSWALLET_CACHE_GET_TTL` – seconds to cache successful GETs (0 disables)

Correlation IDs:
* `XPRESSWALLET_CORRELATION_HEADER` – header injected; logged & can propagate downstream

Circuit Breaker:
* `XPRESSWALLET_CB_ENABLED`
* `XPRESSWALLET_CB_FAILURES` – consecutive failures to open
* `XPRESSWALLET_CB_COOLDOWN` – cool-down in seconds

Idempotency:
* `XPRESSWALLET_IDEMPOTENCY_AUTO` (bool)
* `XPRESSWALLET_IDEMPOTENCY_HEADER` (default `Idempotency-Key`)

OpenTelemetry:
* `XPRESSWALLET_OTEL_ENABLED`
* `XPRESSWALLET_OTEL_SERVICE`

All settings may be overridden per environment. Keep secrets in `.env` and do not commit them.

## Quick start

```php
use Atanunu\XpressWallet\Facades\XpressWallet;

$response = XpressWallet::customers()->all();
```

## Example routes

After installing in a Laravel app, you can load example routes:

```php
Route::prefix('xpress-demo')->middleware('web')->group(function() {
    \Atanunu\XpressWallet\Routes\routes();
});
```

## Testing & Coverage

Basic test run:

```bash
composer test
```

To generate coverage locally (requires Xdebug or PCOV):

```bash
# Enable Xdebug in php.ini (add: zend_extension=xdebug)
# Then run
composer coverage
```

If you prefer PCOV (often faster):

```bash
pecl install pcov
echo "extension=pcov" >> $(php --ini | grep ".ini" | head -1 | awk '{print $NF}')
php -d pcov.enabled=1 -d pcov.directory=src vendor/bin/pest --coverage
```

CI runs a separate coverage job (PHP 8.3) – you can later enforce a minimum threshold by raising the `--min` value in the workflow once baseline is established.

## CI

GitHub Actions workflows:
- `ci.yml`: matrix quality (PHP 8.2/8.3) + dedicated coverage job (8.3 with Xdebug).
- `release-draft.yml`: auto-drafts release notes from merged PR labels on tag push (`v*.*.*`).

Planned enhancements you can enable:
- Upload coverage to Codecov (add a step with `codecov/codecov-action`).
- Add a minimum coverage gate by increasing `--min=0` to your baseline (e.g. 70).

## Maintenance / Pruning

Run periodically (e.g. daily):

```bash
php artisan xpress:prune
```

Use `--dry-run` to preview and `--days=30` to override retention for that run.

## Webhooks

Add the middleware to your webhook route:

```php
Route::post('/xpress/webhook', XpressWebhookController::class)
    ->middleware(\Atanunu\XpressWallet\Http\Middleware\VerifyXpressWebhook::class);
```

Set `XPRESSWALLET_WEBHOOK_SECRET` and (optionally) `XPRESSWALLET_WEBHOOK_SIGNATURE_HEADER`.

## Events

Dispatched:
* `Atanunu\XpressWallet\Events\LoginSucceeded`
* `Atanunu\XpressWallet\Events\TokensRefreshed`
* `Atanunu\XpressWallet\Events\RateLimited` (each 429 attempt with method/url/attempt/retryAfter)
* `Atanunu\XpressWallet\Events\CircuitBreakerOpened` (breaker transition)

Use listeners to instrument metrics, alerts or custom logging.

Example listener registration:
```php
Event::listen(\Atanunu\XpressWallet\Events\RateLimited::class, function($e) {
    logger()->warning('Xpress rate limited', ['url' => $e->url, 'attempt' => $e->attempt, 'retry_after' => $e->retryAfterSeconds]);
});
```

## Pagination Helper

Use `paginate()` when endpoint accepts `page` & `per_page`:
```php
$page1 = XpressWallet::client()->paginate('customers', [], 1, 50);
while ($next = $page1['meta']['next_page']) {
    $page1 = XpressWallet::client()->paginate('customers', [], $next, 50);
}
```

## Rate Limiting & Circuit Breaker

On 429, the client retries with full-jitter until `rate_limit_max_attempts` reached then throws `RateLimitException` (contains `retryAfterSeconds`).
Consecutive failures trigger a breaker; once open, calls throw `CircuitBreakerOpenException` until cool-down passes.

## GET Response Caching

Enable by setting `XPRESSWALLET_CACHE_GET_TTL>0`. Subsequent identical GET calls within TTL return cached payload (per URI+query). Use prudent TTLs for data freshness.

## Idempotency

Unsafe methods automatically include an `Idempotency-Key` header (UUID) unless you override or disable via config. Set your own key by passing it in headers: `XpressWallet::client()->post('endpoint', [...]);` (add custom header via method overload / PR for header injection if needed).

## Tracing (OpenTelemetry)

If OpenTelemetry SDK is installed, spans are created per request (attributes: `http.method`, `http.url`, `http.status_code`, errors flagged). Configure exporter in your host app; this package only emits spans when the global tracer provider is available.

## Static Analysis

Run PHPStan (Larastan) locally:

```bash
composer analyse
```

Raised to level 8 (strict). Keep code green by adding types / phpdoc when extending.

## Dependency Updates

Consider enabling Dependabot (`.github/dependabot.yml`):

```yaml
version: 2
updates:
    - package-ecosystem: "composer"
        directory: "/"
        schedule:
            interval: "weekly"
    - package-ecosystem: "github-actions"
        directory: "/"
        schedule:
            interval: "weekly"
```

## Security

Recommendations:
- Add `roave/security-advisories` (conflict package) in `require-dev` for vulnerable dependency prevention.
- Run `composer audit` in CI (Composer 2.4+).
- CodeQL workflow is currently disabled pending general availability for PHP; rely on PHPStan + Infection.

## Release Process

1. Update `CHANGELOG.md` (optional – or rely on auto draft).
2. Bump version in your tag: `git tag v0.1.0 && git push origin v0.1.0`.
3. GitHub Action drafts release notes (edit if needed, then publish).

## Support / Issues

Open issues or PRs with clear reproduction steps. Use labels (`bug`, `feature`, `docs`, `test`) to improve autogenerated changelog quality.

## License

MIT