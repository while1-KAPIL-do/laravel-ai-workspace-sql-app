<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SessionIntegrityMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Skip for non-session routes (API, assets etc.)
        if (!$request->hasSession()) {
            return $next($request);
        }

        // ── 1. Bind session to IP on first request ─────────────────
        if (!session()->has('_bound_ip')) {
            session([
                '_bound_ip'        => $request->ip(),
                '_session_created' => time(),
            ]);
        }

        // ── 2. Regenerate session ID every 30 minutes ──────────────
        //    Prevents session fixation — attacker's stolen ID expires
        $lastRegen = session('_last_regenerated', 0);
        if ((time() - $lastRegen) > 1800) {
            session()->regenerate(false); // false = keep existing data
            session(['_last_regenerated' => time()]);
        }

        // ── 3. Detect suspicious IP change mid-session ─────────────
        if (session('_bound_ip') !== $request->ip()) {
            Log::warning('Session IP mismatch', [
                'session_id'  => session()->getId(),
                'original_ip' => session('_bound_ip'),
                'current_ip'  => $request->ip(),
                'url'         => $request->fullUrl(),
            ]);

            // Update to new IP — don't hard block
            // (mobile users legitimately change IPs)
            session(['_bound_ip' => $request->ip()]);
        }

        return $next($request);
    }
}