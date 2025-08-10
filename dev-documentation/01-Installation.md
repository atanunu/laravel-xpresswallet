# Installation

## 1) Add the package
Use path or VCS install. For local development, place the package in `packages/atanunu/laravel-xpresswallet` of your app repo and run:

```bash
composer config repositories.atanunu-laravel-xpresswallet path ./packages/atanunu/laravel-xpresswallet
composer require atanunu/laravel-xpresswallet:*
```

## 2) Publish assets and run migrations
```bash
php artisan vendor:publish --provider="Atanunu\XpressWallet\XpressWalletServiceProvider" --tag=xpresswallet-config
php artisan vendor:publish --provider="Atanunu\XpressWallet\XpressWalletServiceProvider" --tag=xpresswallet-migrations
php artisan migrate
```

## 3) Configure environment
Add to your `.env`:

```env
XPRESSWALLET_BASE_URL=https://<sandbox-or-live-base-url>
XPRESSWALLET_EMAIL=merchant@example.com   # raw email (we encode before login)
XPRESSWALLET_PASSWORD=your-secret        # raw password (we encode before login)
XPRESSWALLET_CACHE_TTL=3300
XPRESSWALLET_LOG_BODIES=false
XPRESSWALLET_MAX_BODY=4000
XPRESSWALLET_RETENTION_DAYS=90
```

## 4) First login
```bash
php artisan xpress:login
```

Tokens are stored in DB + cache. You can refresh anytime:
```bash
php artisan xpress:refresh
```