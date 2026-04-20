<?php
namespace App\Providers;

use App\Models\BlockedIp;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RateLimiterServiceProvider extends ServiceProvider
{
    const RATE_LIMIT_ON_WEB_OPERATIONS      = 60; 
    const RATE_LIMIT_ON_AI_OPERATIONS       = 10;
    const RATE_LIMIT_ON_SCHEMA_OPERATIONS   = 5;

    public function boot(): void
    {
        // ── General web routes — 60 requests per minute ───────────
        RateLimiter::for('web-general', function (Request $request) {
            return Limit::perMinute(self::RATE_LIMIT_ON_WEB_OPERATIONS)
                ->by($request->ip())
                ->response(fn() => response()->json([
                    'error' => 'Too many requests. Slow down.',
                ], 429));
        });

        // ── AI / SQL routes — 10 per minute, auto-block on abuse ──
        RateLimiter::for('ai', function (Request $request) {
            return Limit::perMinute(self::RATE_LIMIT_ON_AI_OPERATIONS)
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    $ip       = $request->ip();
                    $abuseKey = "ai_rate_abuse:{$ip}";
                    $hits     = Cache::get($abuseKey, 0) + 1;

                    Cache::put($abuseKey, $hits, now()->addHour());

                    // Auto-block after 5 violations in 1 hour
                    if ($hits >= 5) {
                        BlockedIp::block($ip, 'Auto-blocked: AI route abuse', 'system', 1440);
                        Cache::forget("ip_blocked:{$ip}");

                        return response()->json([
                            'error' => 'You have been blocked due to repeated abuse.',
                        ], 403);
                    }

                    return response()->json([
                        'error'       => 'Too many requests.',
                        'retry_after' => $headers['Retry-After'] ?? 60,
                        'violations'  => "{$hits} of 5 before auto-block",
                    ], 429);
                });
        });

        // ── Schema upload — 5 uploads per hour ────────────────────
        RateLimiter::for('schema-upload', function (Request $request) {
            return Limit::perHour(self::RATE_LIMIT_ON_SCHEMA_OPERATIONS)
                ->by($request->ip())
                ->response(fn() => response()->json([
                    'error' => 'Upload limit reached. Try again in an hour.',
                ], 429));
        });

    }
}