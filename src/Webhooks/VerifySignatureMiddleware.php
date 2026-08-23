<?php

namespace Kstmostofa\LaravelWhatsApp\Webhooks;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Kstmostofa\LaravelWhatsApp\Models\WaCloudAccount;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifies Meta's X-Hub-Signature-256 header against the raw request body.
 * Skips verification entirely when `verify_signature` is disabled (useful
 * for local testing with ngrok).
 *
 * Multi-account: each `wa_cloud_accounts` row can have its own app_secret
 * (a separate Meta app/WABA), and Meta's webhook payload itself doesn't say
 * which account it's for until AFTER the body is parsed — so instead of
 * picking one secret up front, this tries the legacy `.env` secret plus
 * every registered account's secret and accepts the first match. Account
 * counts here are expected to be small (a handful, not thousands), so the
 * O(n) HMAC comparisons are cheap.
 *
 * Fail-closed: if verification is enabled but no secret (legacy or
 * per-account) is configured at all, we return 503 rather than letting
 * unverified payloads through. The 503 also triggers Meta to retry once a
 * secret is set.
 */
class VerifySignatureMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $config = config('laravel-whatsapp.webhook');

        if (! ($config['verify_signature'] ?? true)) {
            return $next($request);
        }

        $secrets = array_filter([
            $config['app_secret'] ?? null,
            ...WaCloudAccount::query()->pluck('app_secret')->all(),
        ]);

        if (empty($secrets)) {
            Log::error('laravel-whatsapp: webhook signature verification enabled but no app_secret is configured (neither WHATSAPP_APP_SECRET nor any wa_cloud_accounts row) — rejecting inbound webhook');

            return new \Illuminate\Http\Response('service misconfigured: no app_secret configured', 503);
        }

        $header = $request->header('X-Hub-Signature-256', '');
        if (! str_starts_with($header, 'sha256=')) {
            return new \Illuminate\Http\Response('missing signature', 401);
        }

        $expected = substr($header, 7);
        $body = $request->getContent();

        foreach ($secrets as $secret) {
            if (hash_equals($expected, hash_hmac('sha256', $body, $secret))) {
                return $next($request);
            }
        }

        return new \Illuminate\Http\Response('invalid signature', 401);
    }
}
