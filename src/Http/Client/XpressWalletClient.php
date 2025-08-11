<?php

namespace Atanunu\XpressWallet\Http\Client;

use Atanunu\XpressWallet\Contracts\XpressWalletClientContract;
use Atanunu\XpressWallet\Events\CircuitBreakerOpened;
use Atanunu\XpressWallet\Events\LoginSucceeded;
use Atanunu\XpressWallet\Events\RateLimited;
use Atanunu\XpressWallet\Events\TokensRefreshed;
use Atanunu\XpressWallet\Exceptions\ApiException;
use Atanunu\XpressWallet\Exceptions\AuthException;
use Atanunu\XpressWallet\Exceptions\CircuitBreakerOpenException;
use Atanunu\XpressWallet\Exceptions\PasswordChangeException;
use Atanunu\XpressWallet\Exceptions\PasswordResetException;
use Atanunu\XpressWallet\Exceptions\RateLimitException;
use Atanunu\XpressWallet\Exceptions\VerificationException;
use Atanunu\XpressWallet\Http\Client\Endpoints\Cards;
use Atanunu\XpressWallet\Http\Client\Endpoints\Customers;
use Atanunu\XpressWallet\Http\Client\Endpoints\Merchant;
use Atanunu\XpressWallet\Http\Client\Endpoints\Team;
use Atanunu\XpressWallet\Http\Client\Endpoints\Transactions;
use Atanunu\XpressWallet\Http\Client\Endpoints\Transfers;
use Atanunu\XpressWallet\Http\Client\Endpoints\User;
use Atanunu\XpressWallet\Http\Client\Endpoints\Wallets;
use Atanunu\XpressWallet\Services\TokenStore;
use Atanunu\XpressWallet\Traits\LogsApiCalls;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

/**
 * Core HTTP client encapsulating all resilience & cross-cutting concerns when calling Xpress Wallet.
 *
 * Features implemented (mapped to numbered design notes):
 * 1. Retries with exponential backoff + full jitter (network / 5xx) & rate-limit adaptive waits.
 * 2. Simple pagination helper (paginate()).
 * 3. Domain specific exception mapping (auth, rate limit, password change/reset, verification).
 * 4. Structured API call logging persisted via LogsApiCalls trait (DB + optional body logging).
 * 5. Response caching for GET requests.
 * 6. Automatic correlation ID header injection.
 * 8. Circuit breaker with half‑open trial after cool down.
 * 10. Full jitter strategy for both generic retries and 429 retry-after windows.
 * 11. Webhook secret rotation supported elsewhere (verification layer) – client remains agnostic.
 * 12. Idempotency key header added automatically for unsafe methods.
 * 14. Optional OpenTelemetry spans if OTEL SDK present.
 *
 * Public surface intentionally returns decoded associative arrays for flexibility.
 */
class XpressWalletClient implements XpressWalletClientContract
{
    use LogsApiCalls;

    /**
     * @param  array<string,mixed>  $config
     */
    public function __construct(
        protected Guzzle $http,
        protected TokenStore $tokens,
        protected array $config,
        protected LoggerInterface $logger,
    ) {}

    /** Return self for fluent style (mainly for facade contract parity). */
    public function client(): self
    {
        return $this;
    }

    /** @return array<string,mixed> */
    /**
     * Perform initial login exchange capturing access & refresh tokens from response headers.
     * Accepts optional runtime overrides; falls back to configured credentials.
     */
    public function login(?string $email = null, ?string $password = null): array
    {
        $email = $email ?? $this->config['email'] ?? null;
        $password = $password ?? $this->config['password'] ?? null;

        if (! $email || ! $password) {
            throw new \InvalidArgumentException('Email/password not configured.');
        }

        $encode = function (string $value): string {
            // Guard: if looks like base64 (length mod 4 == 0 and charset) assume already encoded
            return (strlen($value) % 4 === 0 && preg_match('/^[A-Za-z0-9+\/=]+$/', $value)) ? $value : base64_encode($value);
        };
        $body = [
            'email' => $encode($email),
            'password' => $encode($password),
        ];

        $started = microtime(true);
        try {
            $res = $this->http->post('auth/login', [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => $body,
            ]);

            $access = $res->getHeaderLine('X-Access-Token');
            $refresh = $res->getHeaderLine('X-Refresh-Token');
            if (! $access || ! $refresh) {
                throw new \RuntimeException('Missing tokens in response headers.');
            }

            $this->tokens->put($access, $refresh);
            $rawBody = (string) $res->getBody();
            $payload = json_decode($rawBody, true);
            $this->logApiCall([
                'method' => 'POST',
                'url' => 'auth/login',
                'request_headers' => json_encode(['Content-Type' => 'application/json']),
                'request_body' => json_encode($body),
                'response_status' => $res->getStatusCode(),
                'response_headers' => json_encode($res->getHeaders()),
                'response_body' => config('xpresswallet.log_bodies') ? $rawBody : null,
                'duration_ms' => (int) ((microtime(true) - $started) * 1000),
                'succeeded' => true,
            ]);

            event(new LoginSucceeded($payload ?? []));

            return $payload ?? ['status' => true];
        } catch (RequestException $e) {
            $this->logApiCall([
                'method' => 'POST',
                'url' => 'auth/login',
                'request_headers' => json_encode(['Content-Type' => 'application/json']),
                'request_body' => json_encode($body),
                'response_status' => $e->getResponse()?->getStatusCode(),
                'response_headers' => json_encode($e->getResponse()?->getHeaders() ?? []),
                'response_body' => (string) ($e->getResponse()?->getBody() ?? ''),
                'duration_ms' => (int) ((microtime(true) - $started) * 1000),
                'succeeded' => false,
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /** @return array<string,mixed> */
    /** Refresh access token using stored refresh token and persist new pair. */
    public function refresh(): array
    {
        $refresh = $this->tokens->refresh();
        if (! $refresh) {
            throw new \RuntimeException('No refresh token found. Call login() first.');
        }

        $started = microtime(true);
        try {
            $res = $this->http->post('auth/refresh/token', [
                'headers' => [
                    'X-Refresh-Token' => $refresh,
                ],
            ]);

            $access = $res->getHeaderLine('X-Access-Token');
            $newRefresh = $res->getHeaderLine('X-Refresh-Token');
            if ($access && $newRefresh) {
                $this->tokens->put($access, $newRefresh);
            }

            $rawBody = (string) $res->getBody();
            $payload = json_decode($rawBody, true);

            $this->logApiCall([
                'method' => 'POST',
                'url' => 'auth/refresh/token',
                'request_headers' => json_encode(['X-Refresh-Token' => '***']),
                'request_body' => null,
                'response_status' => $res->getStatusCode(),
                'response_headers' => json_encode($res->getHeaders()),
                'response_body' => config('xpresswallet.log_bodies') ? $rawBody : null,
                'duration_ms' => (int) ((microtime(true) - $started) * 1000),
                'succeeded' => true,
            ]);

            event(new TokensRefreshed($payload ?? []));

            return $payload ?? ['status' => true];
        } catch (RequestException $e) {
            $this->logApiCall([
                'method' => 'POST',
                'url' => 'auth/refresh/token',
                'request_headers' => json_encode(['X-Refresh-Token' => '***']),
                'request_body' => null,
                'response_status' => $e->getResponse()?->getStatusCode(),
                'response_headers' => json_encode($e->getResponse()?->getHeaders() ?? []),
                'response_body' => (string) ($e->getResponse()?->getBody() ?? ''),
                'duration_ms' => (int) ((microtime(true) - $started) * 1000),
                'succeeded' => false,
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /** @return array<string,mixed> */
    /** Invalidate current session tokens server-side (client still retains stored values). */
    public function logout(): array
    {
        $started = microtime(true);
        try {
            $res = $this->http->post('auth/logout', ['headers' => $this->headers()]);
            $raw = (string) $res->getBody();
            $payload = json_decode($raw, true) ?? [];
            $this->logApiCall([
                'method' => 'POST', 'url' => 'auth/logout',
                'request_headers' => json_encode($this->scrubHeaders($this->headers())),
                'request_body' => null,
                'response_status' => $res->getStatusCode(),
                'response_headers' => json_encode($res->getHeaders()),
                'response_body' => config('xpresswallet.log_bodies') ? $raw : null,
                'duration_ms' => (int) ((microtime(true) - $started) * 1000),
                'succeeded' => true,
            ]);

            return $payload;
        } catch (RequestException $e) {
            $this->logApiCall([
                'method' => 'POST', 'url' => 'auth/logout',
                'request_headers' => json_encode($this->scrubHeaders($this->headers())),
                'request_body' => null,
                'response_status' => $e->getResponse()?->getStatusCode(),
                'response_headers' => json_encode($e->getResponse()?->getHeaders() ?? []),
                'response_body' => (string) ($e->getResponse()?->getBody() ?? ''),
                'duration_ms' => (int) ((microtime(true) - $started) * 1000),
                'succeeded' => false,
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /** @return array<string,mixed> */
    public function requestPasswordReset(string $email): array
    {
        return $this->post('auth/password/forget', ['email' => $email]);
    }

    /** @return array<string,mixed> */
    public function resetPassword(string $resetCode, string $password): array
    {
        return $this->post('auth/password/reset', ['resetCode' => $resetCode, 'password' => $password]);
    }

    /** @return array<string,string> */
    /** Build auth + content headers or throw if tokens missing. */
    protected function headers(): array
    {
        $access = $this->tokens->access();
        $refresh = $this->tokens->refresh();
        if (! $access || ! $refresh) {
            throw new \RuntimeException('Missing tokens. Call login() first.');
        }

        return [
            'X-Access-Token' => $access,
            'X-Refresh-Token' => $refresh,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * @param  array<string,mixed>  $headers
     * @return array<string,mixed>
     */
    /** Mask sensitive token headers in logged output unless masking disabled. */
    protected function scrubHeaders(array $headers): array
    {
        if (! config('xpresswallet.mask_tokens', true)) {
            return $headers;
        }
        foreach (['X-Access-Token', 'X-Refresh-Token'] as $k) {
            if (isset($headers[$k])) {
                $val = is_array($headers[$k]) ? ($headers[$k][0] ?? '') : $headers[$k];
                $headers[$k] = $val ? substr($val, 0, 4).'***MASKED***' : '***MASKED***';
            }
        }

        return $headers;
    }

    public function customers(): Customers
    {
        return new Customers($this, $this->http);
    }

    public function wallets(): Wallets
    {
        return new Wallets($this, $this->http);
    }

    public function transactions(): Transactions
    {
        return new Transactions($this, $this->http);
    }

    public function user(): User
    {
        return new User($this, $this->http);
    }

    public function merchant(): Merchant
    {
        return new Merchant($this, $this->http);
    }

    public function transfers(): Transfers
    {
        return new Transfers($this, $this->http);
    }

    public function cards(): Cards
    {
        return new Cards($this, $this->http);
    }

    public function team(): Team
    {
        return new Team($this, $this->http);
    }

    // Generic helpers used by endpoints
    /**
     * @param  array<string,mixed>  $query
     * @return array<string,mixed>
     */
    /** Perform a GET request with optional query parameters. */
    public function get(string $uri, array $query = []): array
    {
        return $this->request('GET', $uri, ['query' => $query]);
    }

    /**
     * Simple pagination helper (feature 2) expecting API supports page & per_page.
     * Returns ['data'=>[...], 'meta'=>['page'=>int,'per_page'=>int,'next_page'=>?int]]
     *
     * @param  array<string,mixed>  $query
     * @return array<string,mixed>
     */
    public function paginate(string $uri, array $query = [], int $page = 1, int $perPage = 50): array
    {
        $query = array_merge($query, ['page' => $page, 'per_page' => $perPage]);
        $resp = $this->get($uri, $query);
        $data = $resp['data'] ?? $resp['items'] ?? [];
        $hasMore = isset($resp['meta']['has_more']) ? (bool) $resp['meta']['has_more'] : (count($data) === $perPage);

        return [
            'data' => $data,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'next_page' => $hasMore ? $page + 1 : null,
            ],
            'raw' => $resp,
        ];
    }

    /**
     * @param  array<string,mixed>  $payload
     * @return array<string,mixed>
     */
    /** POST helper forwarding JSON payload. */
    public function post(string $uri, array $payload = []): array
    {
        return $this->request('POST', $uri, ['json' => $payload], $payload);
    }

    /**
     * @param  array<string,mixed>  $payload
     * @return array<string,mixed>
     */
    /** PUT helper forwarding JSON payload. */
    public function put(string $uri, array $payload = []): array
    {
        return $this->request('PUT', $uri, ['json' => $payload], $payload);
    }

    /**
     * @param  array<string,mixed>  $payload
     * @return array<string,mixed>
     */
    /** PATCH helper forwarding JSON payload. */
    public function patch(string $uri, array $payload = []): array
    {
        return $this->request('PATCH', $uri, ['json' => $payload], $payload);
    }

    /**
     * @param  array<string,mixed>  $options
     * @param  array<string,mixed>|null  $loggedPayload
     * @return array<string,mixed>
     */
    /**
     * Low-level request pipeline applying:
     * - Auth header injection (non auth/* endpoints)
     * - Correlation / idempotency headers
     * - Circuit breaker guard
     * - Response caching (GET)
     * - OpenTelemetry span (if enabled)
     * - Structured logging
     * - Retry / rate-limit backoff logic & circuit breaker failure counting
     */
    protected function request(string $method, string $uri, array $options = [], ?array $loggedPayload = null, int $attempt = 1): array
    {
        $started = microtime(true);
        $headers = $options['headers'] ?? [];
        // Attach auth headers automatically except for auth endpoints
        if (! str_starts_with($uri, 'auth/')) {
            $headers = array_merge($this->headers(), $headers);
        }

        // Correlation ID header (feature 6)
        $corrHeader = $this->config['correlation']['header'] ?? null;
        if ($corrHeader) {
            $headers[$corrHeader] = $headers[$corrHeader] ?? (Str::uuid()->toString());
        }

        // Idempotency header for unsafe methods (feature 12)
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE']) && ($this->config['idempotency']['auto'] ?? true)) {
            $idempotencyHeader = $this->config['idempotency']['header'] ?? 'Idempotency-Key';
            $headers[$idempotencyHeader] = $headers[$idempotencyHeader] ?? Str::uuid()->toString();
        }
        $options['headers'] = $headers;

        // Circuit breaker check (feature 8)
        if (($this->config['circuit_breaker']['enabled'] ?? false) && ! str_starts_with($uri, 'auth/')) {
            $cb = $this->config['circuit_breaker'];
            $key = $cb['cache_key'] ?? 'xpresswallet:circuit_breaker';
            $state = Cache::get($key, ['failures' => 0, 'opened_at' => null]);
            if ($state['opened_at']) {
                $coolDown = (int) ($cb['cool_down_seconds'] ?? 30);
                if (time() - $state['opened_at'] < $coolDown) {
                    throw new CircuitBreakerOpenException;
                }
                // half-open: allow single trial (reset opened_at so next failure reopens quickly)
                $state['opened_at'] = null;
                Cache::put($key, $state, $coolDown);
            }
        }

        // Response caching for GET (feature 5)
        $cacheTtl = (int) ($this->config['cache_get_ttl'] ?? 0);
        $cacheKey = null;
        if ($cacheTtl > 0 && $method === 'GET') {
            $cacheKey = 'xpresswallet:get:'.md5($uri.'|'.serialize($options['query'] ?? []));
            $cached = Cache::get($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }
        }

        // OpenTelemetry span (feature 14) guarded
        $span = null;
        if (($this->config['opentelemetry']['enabled'] ?? false)) {
            try {
                if (class_exists('OpenTelemetry\\API\\Globals')) {
                    $tracer = \OpenTelemetry\API\Globals::tracerProvider()->getTracer('xpresswallet');
                    $span = $tracer->spanBuilder('XpressWallet '.$method.' '.$uri)->startSpan();
                    $span->setAttribute('http.method', $method);
                    $span->setAttribute('http.url', $uri);
                }
            } catch (\Throwable) {
                $span = null; // swallow
            }
        }

        try {
            $res = $this->http->request($method, $uri, $options);
            $raw = (string) $res->getBody();
            $this->logApiCall([
                'method' => $method,
                'url' => $uri,
                'request_headers' => json_encode($this->scrubHeaders($headers)),
                'request_body' => $loggedPayload ? json_encode($loggedPayload) : null,
                'response_status' => $res->getStatusCode(),
                'response_headers' => json_encode($res->getHeaders()),
                'response_body' => config('xpresswallet.log_bodies') ? $raw : null,
                'duration_ms' => (int) ((microtime(true) - $started) * 1000),
                'succeeded' => true,
            ]);
            $decoded = json_decode($raw, true) ?? [];
            if ($span) {
                try {
                    $span->setAttribute('http.status_code', $res->getStatusCode());
                    $span->end();
                } catch (\Throwable) {
                }
            }
            if ($cacheKey) {
                Cache::put($cacheKey, $decoded, $cacheTtl);
            }
            // Reset circuit breaker on success
            if (($this->config['circuit_breaker']['enabled'] ?? false) && ! str_starts_with($uri, 'auth/')) {
                $cb = $this->config['circuit_breaker'];
                $key = $cb['cache_key'] ?? 'xpresswallet:circuit_breaker';
                Cache::forget($key);
            }

            return $decoded;
        } catch (RequestException $e) {
            $status = $e->getResponse()?->getStatusCode();
            $isAuthFail = $status === 401 && ! str_starts_with($uri, 'auth/');
            $cfgRetries = $this->config['retries'] ?? [];
            $maxAttempts = (int) ($cfgRetries['max_attempts'] ?? 2);
            $initialDelay = (int) ($cfgRetries['initial_delay_ms'] ?? 200);
            $maxDelay = (int) ($cfgRetries['max_delay_ms'] ?? 2000);
            $fullJitter = (bool) ($cfgRetries['full_jitter'] ?? true);
            $rateLimitMax = (int) ($cfgRetries['rate_limit_max_attempts'] ?? 5);

            // Auto refresh path
            if ($isAuthFail && config('xpresswallet.auto_refresh_on_401', true) && $attempt === 1) {
                try {
                    $this->refresh();

                    return $this->request($method, $uri, $options, $loggedPayload, $attempt + 1);
                } catch (\Throwable) {
                    // fall through to normal logging if refresh fails
                }
            }

            // Rate limiting (feature 1) with Retry-After + jitter (feature 10)
            if ($status === 429) {
                $retryAfterHeader = $e->getResponse()?->getHeaderLine('Retry-After');
                $retryAfter = (int) ($retryAfterHeader ?: 0);
                event(new RateLimited($method, $uri, $attempt, $retryAfter ?: null));
            }
            if ($status === 429 && $attempt < $rateLimitMax) {
                $retryAfter = (int) ($e->getResponse()?->getHeaderLine('Retry-After') ?: 0);
                $base = $retryAfter > 0 ? $retryAfter * 1000 : min($maxDelay, $initialDelay * (2 ** ($attempt - 1)));
                $delay = $fullJitter ? random_int(0, $base) : $base;
                usleep($delay * 1000);

                return $this->request($method, $uri, $options, $loggedPayload, $attempt + 1);
            }

            // Retry on network (no response) or 5xx (features 1 & 10 extended jitter)
            $shouldRetry = ($status === null || ($status >= 500 && $status < 600)) && $attempt < $maxAttempts;
            if ($shouldRetry) {
                $backoff = min($maxDelay, $initialDelay * (2 ** ($attempt - 1)));
                $delay = $fullJitter ? random_int(0, $backoff) : $backoff;
                usleep($delay * 1000);

                return $this->request($method, $uri, $options, $loggedPayload, $attempt + 1);
            }

            $this->logApiCall([
                'method' => $method,
                'url' => $uri,
                'request_headers' => json_encode($this->scrubHeaders($headers)),
                'request_body' => $loggedPayload ? json_encode($loggedPayload) : null,
                'response_status' => $status,
                'response_headers' => json_encode($e->getResponse()?->getHeaders() ?? []),
                'response_body' => (string) ($e->getResponse()?->getBody() ?? ''),
                'duration_ms' => (int) ((microtime(true) - $started) * 1000),
                'succeeded' => false,
                'error_message' => $e->getMessage(),
            ]);
            // Circuit breaker failure counting
            if (($this->config['circuit_breaker']['enabled'] ?? false) && ! str_starts_with($uri, 'auth/')) {
                $cb = $this->config['circuit_breaker'];
                $key = $cb['cache_key'] ?? 'xpresswallet:circuit_breaker';
                $state = Cache::get($key, ['failures' => 0, 'opened_at' => null]);
                $state['failures']++;
                $threshold = (int) ($cb['failure_threshold'] ?? 5);
                if ($state['failures'] >= $threshold && ! $state['opened_at']) {
                    $state['opened_at'] = time();
                    event(new CircuitBreakerOpened($uri));
                }
                Cache::put($key, $state, (int) ($cb['cool_down_seconds'] ?? 30));
                // If breaker just opened, throw immediately instead of proceeding to map exception
                if ($state['opened_at'] && (time() - $state['opened_at']) < (int) ($cb['cool_down_seconds'] ?? 30)) {
                    throw new CircuitBreakerOpenException;
                }
            }

            // Throw domain-specific exceptions (feature 3/4 via mapping)
            if ($span) {
                try {
                    $span->setAttribute('error', true);
                    $span->setAttribute('error.message', $e->getMessage());
                    $span->end();
                } catch (\Throwable) {
                }
            }
            if ($status === 401) {
                throw new AuthException('Authentication failed', 401, []);
            }
            if ($status === 429) {
                $retryAfter = (int) ($e->getResponse()?->getHeaderLine('Retry-After') ?: 0);
                throw new RateLimitException('Rate limited', $retryAfter, []);
            }
            if ($status && $status >= 400) {
                $body = (string) ($e->getResponse()?->getBody() ?? '');
                $decoded = json_decode($body, true) ?: [];
                $message = $decoded['message'] ?? $decoded['error'] ?? 'API error (status '.$status.')';
                // Endpoint specific mapping
                if (str_contains($uri, 'user/password')) {
                    throw new PasswordChangeException($message, $status, $decoded);
                }
                if (str_contains($uri, 'auth/password')) {
                    throw new PasswordResetException($message, $status, $decoded);
                }
                if (str_contains($uri, 'verify')) {
                    throw new VerificationException($message, $status, $decoded);
                }
                throw new ApiException($message, $status, $decoded);
            }
            throw new ApiException($e->getMessage(), null, []);
        }
    }
}
