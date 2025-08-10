<?php

namespace Atanunu\XpressWallet\Traits;

use Atanunu\XpressWallet\Models\ApiCallLog;
use Illuminate\Support\Str;

trait LogsApiCalls
{
    /** @param array<string,mixed> $data */
    protected function logApiCall(array $data): void
    {
        // Truncate bodies if configured
        $max = config('xpresswallet.max_body_length', 4000);
        foreach (['request_body', 'response_body'] as $k) {
            if (isset($data[$k]) && is_string($data[$k]) && mb_strlen($data[$k]) > $max) {
                $data[$k] = mb_substr($data[$k], 0, $max).'…';
            }
        }

        // Mask tokens if configured
        if (config('xpresswallet.mask_tokens', true)) {
            foreach (['request_headers', 'response_headers'] as $hdrKey) {
                if (! empty($data[$hdrKey]) && is_string($data[$hdrKey])) {
                    $decoded = json_decode($data[$hdrKey], true);
                    if (is_array($decoded)) {
                        foreach (['X-Access-Token', 'X-Refresh-Token'] as $sensitive) {
                            if (isset($decoded[$sensitive])) {
                                $val = $decoded[$sensitive];
                                // If header is array take first string for masking logic
                                $valStr = is_array($val) ? ($val[0] ?? '') : $val;
                                $decoded[$sensitive] = $valStr ? substr($valStr, 0, 4).'***MASKED***' : '***MASKED***';
                            }
                        }
                        $data[$hdrKey] = json_encode($decoded);
                    }
                }
            }
            // Also mask explicit error_message if it inadvertently contains tokens
            if (! empty($data['error_message']) && is_string($data['error_message'])) {
                $data['error_message'] = preg_replace('/(X-(?:Access|Refresh)-Token: )[A-Za-z0-9+\/=_-]+/i', '$1***MASKED***', $data['error_message']);
            }
        }

        ApiCallLog::query()->create(array_merge([
            'idempotency_key' => Str::uuid()->toString(),
        ], $data));
    }
}
