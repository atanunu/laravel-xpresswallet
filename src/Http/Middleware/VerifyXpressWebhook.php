<?php

namespace Atanunu\XpressWallet\Http\Middleware;

use Atanunu\XpressWallet\Models\WebhookEvent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyXpressWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('xpresswallet.webhook.secret');
        if (! $secret) {
            return $next($request); // skip if not configured
        }

        $header = config('xpresswallet.webhook.signature_header', 'X-Xpress-Signature');
        $rawHeader = $request->header($header, '');
        if (is_array($rawHeader)) {
            /** @var array<int,string|null> $rawHeader */
            $sig = '';
            foreach ($rawHeader as $candidate) {
                if ($candidate !== null) {
                    $sig = $candidate;
                    break;
                }
            }
        } else {
            $sig = $rawHeader;
        }
        if (! $sig || ! str_contains($sig, '.')) {
            return response('Invalid signature', 400);
        }
        [$timestamp, $signature] = explode('.', $sig, 2);
        if (! ctype_digit($timestamp)) {
            return response('Invalid signature timestamp', 400);
        }
        $tolerance = (int) config('xpresswallet.webhook.tolerance_seconds', 300);
        if (abs(time() - (int) $timestamp) > $tolerance) {
            return response('Signature timestamp expired', 400);
        }

        $payload = $request->getContent();
        $computed = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
        if (! hash_equals($computed, $signature)) {
            return response('Signature mismatch', 400);
        }

        // Store webhook event
        WebhookEvent::query()->create([
            'event' => $request->input('event') ?? 'unknown',
            'signature' => $sig,
            'payload' => $payload,
            'received_at' => now(),
        ]);

        return $next($request);
    }
}
