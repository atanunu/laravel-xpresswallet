<?php

namespace Atanunu\XpressWallet\Services;

use Atanunu\XpressWallet\Models\XpressToken;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\DatabaseManager as DB;

/**
 * TokenStore provides a simple persistence + cache layer for access & refresh tokens.
 *
 * Responsibilities:
 * - Persist every token pair to the database for audit / recovery purposes.
 * - Serve hot tokens from cache for fast header injection by the HTTP client.
 * - Fallback to the latest database record when cache is cold / expired.
 * - Keep cache TTL shorter for access token to naturally expire while keeping refresh token longer.
 *
 * No encryption is applied here; rely on application / infrastructure level secrets management
 * and restricted DB column visibility. Consider encrypting columns if threat model requires.
 *
 * This class is intentionally framework-light: it expects a Cache repository & DatabaseManager.
 * All keys / TTL values are provided via the xpresswallet config array.
 *
 * @internal Public API stability not guaranteed; facade / client should be used instead.
 */
class TokenStore
{
    /**
     * @param  array<string,mixed>  $config
     */
    public function __construct(
        protected CacheRepository $cache,
        protected DB $db,
        protected array $config,
    ) {}

    /**
     * Persist and cache the latest access & refresh tokens.
     *
     * Writes a new row for every rotation (append-only) enabling historical analysis / pruning.
     * Cache:
     *  - access token cached for configured ttl
     *  - refresh token cached for 2x ttl to reduce DB reads during refresh loops
     */
    public function put(string $access, string $refresh): void
    {
        $this->db->transaction(function () use ($access, $refresh) {
            XpressToken::query()->create([
                'access_token' => $access,
                'refresh_token' => $refresh,
            ]);
        });

        $ttl = (int) ($this->config['cache']['ttl'] ?? 3300);
        $this->cache->put($this->config['cache']['access_key'], $access, $ttl);
        $this->cache->put($this->config['cache']['refresh_key'], $refresh, $ttl * 2);
    }

    /**
     * Retrieve the current access token.
     * Order of resolution:
     * 1. Cache (fast path)
     * 2. Latest DB record (then repopulate cache)
     */
    public function access(): ?string
    {
        $key = $this->config['cache']['access_key'];
        $acc = $this->cache->get($key);
        if ($acc) {
            return $acc;
        }

        $rec = XpressToken::query()->latest('id')->first();
        if (! $rec) {
            return null;
        }

        $this->cache->put($key, $rec->access_token, (int) ($this->config['cache']['ttl'] ?? 3300));

        return $rec->access_token;
    }

    /**
     * Retrieve the current refresh token using same strategy as access().
     */
    public function refresh(): ?string
    {
        $key = $this->config['cache']['refresh_key'];
        $ref = $this->cache->get($key);
        if ($ref) {
            return $ref;
        }

        $rec = XpressToken::query()->latest('id')->first();
        if (! $rec) {
            return null;
        }

        $this->cache->put($key, $rec->refresh_token, (int) ($this->config['cache']['ttl'] ?? 3300) * 2);

        return $rec->refresh_token;
    }
}
