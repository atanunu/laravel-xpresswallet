# Configuration

The publishable config is `config/xpresswallet.php`:

- **base_url**: API base URL.
- **email/password**: Credentials in raw form. We base64-encode them in the login call.
- **cache**: Keys and TTL for tokens.
- **log_bodies**: Whether to store raw response bodies (beware of PII).
- **max_body_length**: Truncation limit for request/response body logs.
- **retention_days**: Days to keep API call logs and webhook events.
 - **mask_tokens**: Mask sensitive token headers in stored logs (default true).
 - **auto_refresh_on_401**: Automatically call refresh() once when a 401 is encountered, then retry request.
 - **retries**: Array controlling transient retry behavior (`max_attempts`, `initial_delay_ms`, `max_delay_ms`).
 - **max_tokens**: Maximum number of historical token rows retained (older pruned by `xpress:prune`).
 - **webhook.secret**: Shared secret for webhook signature verification.
 - **webhook.signature_header**: Header carrying `timestamp.signature` value.
 - **webhook.tolerance_seconds**: Allowed timestamp skew.

> Tip: Use Laravel's `config:cache` in production.