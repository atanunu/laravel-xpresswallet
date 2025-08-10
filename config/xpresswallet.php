<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Xpress Wallet Base URL
    |--------------------------------------------------------------------------
    */
    'base_url' => env('XPRESSWALLET_BASE_URL', 'https://api.example.com'),
    /*
    |--------------------------------------------------------------------------
    | Credentials (raw). We will base64-encode them for the login call.
    |--------------------------------------------------------------------------
    */
    'email' => env('XPRESSWALLET_EMAIL'),
    'password' => env('XPRESSWALLET_PASSWORD'),
    /*
    |--------------------------------------------------------------------------
    | Cache keys and TTL (seconds). Access token typically short-lived.
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'access_key' => 'xpresswallet:access_token',
        'refresh_key' => 'xpresswallet:refresh_token',
        'ttl' => env('XPRESSWALLET_CACHE_TTL', 3300), // ~55 minutes
    ],
    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */
    'log_bodies' => env('XPRESSWALLET_LOG_BODIES', false), // careful in production
    'max_body_length' => env('XPRESSWALLET_MAX_BODY', 4000),
    // Whether to mask sensitive headers (X-Access-Token / X-Refresh-Token) in logs
    'mask_tokens' => env('XPRESSWALLET_MASK_TOKENS', true),
    // Automatically attempt refresh() when a 401 is encountered for authenticated requests
    'auto_refresh_on_401' => env('XPRESSWALLET_AUTO_REFRESH', true),
    // Retry configuration for transient failures (network / 5xx)
    'retries' => [
        'max_attempts' => env('XPRESSWALLET_RETRY_ATTEMPTS', 2), // total attempts including first
        'initial_delay_ms' => env('XPRESSWALLET_RETRY_DELAY', 200),
        'max_delay_ms' => env('XPRESSWALLET_RETRY_MAX_DELAY', 2000),
    // When true, use full jitter exponential backoff instead of simple doubling (feature 10)
    'full_jitter' => env('XPRESSWALLET_RETRY_FULL_JITTER', true),
    // Maximum retry attempts specifically for 429 responses (rate limiting) (feature 1)
    'rate_limit_max_attempts' => env('XPRESSWALLET_RATE_LIMIT_ATTEMPTS', 5),
    ],
    /*
    |--------------------------------------------------------------------------
    | Retention (days) for API call logs & webhook events
    |--------------------------------------------------------------------------
    */
    'retention_days' => env('XPRESSWALLET_RETENTION_DAYS', 90),
    // Maximum historical token rows to retain (older ones pruned by prune command)
    'max_tokens' => env('XPRESSWALLET_MAX_TOKENS', 50),
    /*
    |--------------------------------------------------------------------------
    | Webhook Verification
    |--------------------------------------------------------------------------
    | secret: Shared secret for validating incoming webhook signatures.
    | signature_header: Header carrying signature (timestamp.hmac format).
    | tolerance_seconds: Reject if timestamp skew exceeds this window.
    */
    'webhook' => [
        // Support multiple active secrets for rotation (feature 11)
        'secret' => env('XPRESSWALLET_WEBHOOK_SECRET'), // primary legacy key
        'secrets' => array_filter(array_map('trim', explode(',', (string) env('XPRESSWALLET_WEBHOOK_SECRETS', '')))),
        'signature_header' => env('XPRESSWALLET_WEBHOOK_SIGNATURE_HEADER', 'X-Xpress-Signature'),
        'tolerance_seconds' => env('XPRESSWALLET_WEBHOOK_TOLERANCE', 300),
        // When true, dispatch webhook processing to a queue job (feature 13)
        'async' => env('XPRESSWALLET_WEBHOOK_ASYNC', false),
    ],
    /*
    |--------------------------------------------------------------------------
    | Response Caching for GET (feature 5)
    |--------------------------------------------------------------------------
    | cache_get_ttl: seconds to cache successful GET responses. 0 disables.
    */
    'cache_get_ttl' => env('XPRESSWALLET_CACHE_GET_TTL', 0),
    /*
    |--------------------------------------------------------------------------
    | Correlation IDs (feature 6)
    |--------------------------------------------------------------------------
    | header: name of header injected; if null disables.
    */
    'correlation' => [
        'header' => env('XPRESSWALLET_CORRELATION_HEADER', 'X-Correlation-ID'),
    ],
    /*
    |--------------------------------------------------------------------------
    | Circuit Breaker (feature 8)
    |--------------------------------------------------------------------------
    | failure_threshold: consecutive failures to open breaker.
    | cool_down_seconds: time breaker stays open before half-open trial.
    */
    'circuit_breaker' => [
        'enabled' => env('XPRESSWALLET_CB_ENABLED', true),
        'failure_threshold' => env('XPRESSWALLET_CB_FAILURES', 5),
        'cool_down_seconds' => env('XPRESSWALLET_CB_COOLDOWN', 30),
        'cache_key' => 'xpresswallet:circuit_breaker',
    ],
    /*
    |--------------------------------------------------------------------------
    | Idempotency (feature 12)
    |--------------------------------------------------------------------------
    */
    'idempotency' => [
        'auto' => env('XPRESSWALLET_IDEMPOTENCY_AUTO', true),
        'header' => env('XPRESSWALLET_IDEMPOTENCY_HEADER', 'Idempotency-Key'),
    ],
    /*
    |--------------------------------------------------------------------------
    | OpenTelemetry (feature 14)
    |--------------------------------------------------------------------------
    | Attempts to start a span for each API call if OTEL SDK available.
    */
    'opentelemetry' => [
        'enabled' => env('XPRESSWALLET_OTEL_ENABLED', true),
        'service_name' => env('XPRESSWALLET_OTEL_SERVICE', 'xpresswallet-sdk'),
    ],
];