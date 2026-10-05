<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Nmb\NmbObpClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * OAuth / callback helpers for NMB OBP consumer "Emish Store".
 * Redirect registered: https://emish.store/api/nmb/oauth/callback
 */
class NmbOAuthController extends Controller
{
    public function callback(Request $request)
    {
        // OBP may return oauth_token, oauth_verifier, or error
        $payload = [
            'received_at' => now()->toIso8601String(),
            'query' => $request->query(),
            'app' => config('nmb.app_name'),
            'consumer_id' => config('nmb.consumer_id'),
        ];

        Log::info('NMB OAuth callback', $payload);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'message' => 'NMB OAuth callback received',
                'data' => $payload,
            ]);
        }

        return response(
            '<html><body style="font-family:sans-serif;padding:2rem">'
            .'<h2>NMB OAuth callback</h2>'
            .'<p>Token parameters received. You can close this window and return to MkulimaHub / Emish Store.</p>'
            .'<pre>'.e(json_encode($payload, JSON_PRETTY_PRINT)).'</pre>'
            .'</body></html>',
            200,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }

    /**
     * Ping sandbox root (no auth) + report consumer config.
     */
    public function ping(NmbObpClient $nmb)
    {
        $root = null;
        $error = null;
        try {
            $res = Http::timeout(15)->get(rtrim(config('nmb.base_url'), '/').'/obp/'.config('nmb.api_version', 'v5.0.0').'/root');
            $root = $res->successful() ? $res->json() : ['status' => $res->status(), 'body' => $res->body()];
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return response()->json([
            'nmb' => $nmb->status(),
            'sandbox_root' => $root,
            'sandbox_error' => $error,
            'consumer' => [
                'id' => config('nmb.consumer_id'),
                'key_prefix' => substr((string) config('nmb.consumer_key'), 0, 6).'…',
                'app_name' => config('nmb.app_name'),
                'redirect_url' => config('nmb.redirect_url'),
            ],
        ]);
    }
}
