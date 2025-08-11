# Configuration

Publishable file: `config/xpresswallet.php`

Key groups:

Core:
- `base_url` (default `https://payment.xpress-wallet.com`)
- `email` / `password` (raw; auto base64 on login)

Token Cache:
- `cache.access_key`, `cache.refresh_key`, `cache.ttl`
- `max_tokens` (DB retention cap, older pruned)

Logging & Auditing:
- `log_bodies`, `max_body_length`, `mask_tokens`
- Structured entries persisted in `api_call_logs`

Retry & Resilience:
- `auto_refresh_on_401`
- `retries.max_attempts`, `retries.initial_delay_ms`, `retries.max_delay_ms`, `retries.full_jitter`, `retries.rate_limit_max_attempts`
- `circuit_breaker.enabled`, `failure_threshold`, `cool_down_seconds`

Caching & Correlation:
- `cache_get_ttl` (GET response caching)
- `correlation.header` (UUID header injection)

Idempotency & Telemetry:
- `idempotency.auto`, `idempotency.header`
- `opentelemetry.enabled`, `opentelemetry.service_name`

Webhooks:
- `webhook.secret` (legacy primary)
- `webhook.secrets` (array for rotation)
- `webhook.signature_header`, `webhook.tolerance_seconds`, `webhook.async`

Routes (optional auto proxy layer):
- `routes.enabled`, `routes.prefix`, `routes.middleware`

Retention:
- `retention_days`

Tip: Cache configuration in production (`php artisan config:cache`) after environment changes.