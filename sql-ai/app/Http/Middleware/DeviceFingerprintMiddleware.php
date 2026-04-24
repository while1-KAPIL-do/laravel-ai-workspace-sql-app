<?php
namespace App\Http\Middleware;

use App\Models\BlockedIp;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DeviceFingerprintMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!config('security.device_fingerprint')) {
            return $next($request);
        }

        // Skip if no session available
        if (!$request->hasSession()) {
            return $next($request);
        }

        $fingerprint = $this->generateFingerprint($request);

        // First visit — store fingerprint
        if (!session()->has('_device_fingerprint')) {
            session(['_device_fingerprint' => $fingerprint]);
            return $next($request);
        }

        // Subsequent requests — fingerprint must match
        if (session('_device_fingerprint') !== $fingerprint) {
            $ip       = $request->ip();
            $abuseKey = "fp_mismatch:{$ip}";
            $hits     = Cache::get($abuseKey, 0) + 1;

            Cache::put($abuseKey, $hits, now()->addHour());

            Log::warning('Device fingerprint mismatch', [
                'ip'                  => $ip,
                'url'                 => $request->fullUrl(),
                'stored_fingerprint'  => session('_device_fingerprint'),
                'current_fingerprint' => $fingerprint,
                'violations'          => $hits,
            ]);

            // Auto-block after 3 mismatches — stronger threshold than CSRF
            // because legitimate users almost never trigger this
            if ($hits >= 3) {
                BlockedIp::block($ip, 'Auto-blocked: device fingerprint mismatch', 'system', 720);
                Cache::forget("ip_blocked:{$ip}");
                Log::critical('IP auto-blocked: fingerprint mismatch', ['ip' => $ip]);
            }

            // Kill the suspicious session
            session()->invalidate();
            session()->regenerateToken();

            abort(403, 'Session integrity check failed.');
        }

        return $next($request);
    }

    private function generateFingerprint(Request $request): string
    {
        return hash('sha256', implode('|', [
            $request->ip(),
            $request->userAgent()          ?? 'unknown',
            $request->header('Accept')     ?? '',
            $request->header('Accept-Language') ?? '',
            $request->header('Accept-Encoding') ?? '',
        ]));
    }
}