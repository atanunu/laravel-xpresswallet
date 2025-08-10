<?php

namespace Atanunu\XpressWallet\Services;

use Atanunu\XpressWallet\Models\XpressToken;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\DatabaseManager as DB;

/** @internal */
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
