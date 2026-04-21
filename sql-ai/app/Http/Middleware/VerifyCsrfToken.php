<?php
namespace App\Http\Middleware;

use App\Models\BlockedIp;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        //
    ];

    public function handle($request, \Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (\Illuminate\Session\TokenMismatchException $e) {
            $ip       = $request->ip();
            $abuseKey = "csrf_fail:{$ip}";
            $hits     = Cache::get($abuseKey, 0) + 1;

            Cache::put($abuseKey, $hits, now()->addHour());

            Log::warning('CSRF token mismatch', [
                'ip'         => $ip,
                'url'        => $request->fullUrl(),
                'violations' => $hits,
            ]);

            if ($hits >= 5) {
                BlockedIp::block($ip, 'Auto-blocked: CSRF abuse', 'system', 1440);
                Cache::forget("ip_blocked:{$ip}");
                Log::critical('IP auto-blocked: CSRF abuse', ['ip' => $ip]);
            }

            throw $e; // rethrow so Laravel still returns 419
        }
    }

    protected function tokensMatch($request): bool
    {
        Log::info('tokensMatch called', ['ip' => $request->ip()]);
        $match = parent::tokensMatch($request);

        if (!$match) {
            Log::warning('CSRF mismatch detected', ['ip' => $request->ip()]);
            $ip       = $request->ip();
            $abuseKey = "csrf_fail:{$ip}";
            $hits     = Cache::get($abuseKey, 0) + 1;

            Cache::put($abuseKey, $hits, now()->addHour());

            Log::warning('CSRF token mismatch', [
                'ip'         => $ip,
                'url'        => $request->fullUrl(),
                'method'     => $request->method(),
                'user_agent' => $request->userAgent(),
                'violations' => $hits,
            ]);

            if ($hits >= 5) {
                BlockedIp::block($ip, 'Auto-blocked: CSRF abuse', 'system', 1440);
                Cache::forget("ip_blocked:{$ip}");
                Log::critical('IP auto-blocked: CSRF abuse', ['ip' => $ip]);
            }
        }

        return $match;
    }
}