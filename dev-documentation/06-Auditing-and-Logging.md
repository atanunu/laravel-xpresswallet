# Auditing & Logging

Every request is persisted in `api_call_logs` with timing and status. Bodies are **optional** (disabled by default) and truncated to `max_body_length` to reduce risk and storage.

- Toggle body logging via `XPRESSWALLET_LOG_BODIES=true|false`.
- Set truncation length `XPRESSWALLET_MAX_BODY` (default 4000).
- Recommended: do not log bodies in production or mask PII before logging.

## What is logged
- HTTP method and URL
- Request/Response headers
- Request/Response body (optional)
- Duration in milliseconds
- Success flag and error message (if thrown)

## Retention
Configure `XPRESSWALLET_RETENTION_DAYS` and add the scheduler snippet from the **Data Model** doc.