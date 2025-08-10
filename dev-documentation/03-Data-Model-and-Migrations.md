# Data Model & Migrations

## Tables

### 1) `xpress_tokens`
| column        | type  | notes                    |
|---------------|-------|--------------------------|
| id            | BIGPK |                          |
| access_token  | text  | current access token     |
| refresh_token | text  | current refresh token    |
| created_at    | ts    |                          |
| updated_at    | ts    |                          |

### 2) `api_call_logs`
Captures every request/response for audit.

| column            | type    | notes                                  |
|-------------------|---------|----------------------------------------|
| id                | BIGPK   |                                        |
| idempotency_key   | string  | generated UUID, indexed                |
| method            | string  | GET/POST/PUT…                          |
| url               | string  | relative API path                      |
| request_headers   | longtext| masked or raw                          |
| request_body      | longtext| optional (truncated by config)         |
| response_status   | int     | e.g., 200, 400                         |
| response_headers  | longtext|                                        |
| response_body     | longtext| optional (truncated by config)         |
| duration_ms       | int     | total time                             |
| succeeded         | boolean |                                        |
| error_message     | text    | captured on exceptions                 |
| created_at/updated_at | ts  |                                        |

### 3) `webhook_events`
| column      | type    | notes                     |
|-------------|---------|---------------------------|
| id          | BIGPK   |                           |
| event       | string  | event type                |
| signature   | string  | optional HMAC/secret      |
| payload     | longtext| full JSON payload         |
| received_at | ts      | when received             |
| created_at/updated_at | ts |                      |

## Retention
The config value `retention_days` defines how long to keep logs. Add a scheduler task in your **app** to purge old rows:

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule): void
{
    $schedule->call(function () {
        $days = (int) config('xpresswallet.retention_days', 90);
        \Atanunu\XpressWallet\Models\ApiCallLog::query()
            ->where('created_at', '<', now()->subDays($days))
            ->delete();
        \Atanunu\XpressWallet\Models\WebhookEvent::query()
            ->where('created_at', '<', now()->subDays($days))
            ->delete();
    })->daily();
}
```