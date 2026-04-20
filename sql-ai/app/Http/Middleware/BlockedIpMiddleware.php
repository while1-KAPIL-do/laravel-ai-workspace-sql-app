<?php
namespace App\Http\Middleware;

use App\Models\BlockedIp;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BlockedIpMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();

        // Cache the DB check for 5 min — avoid DB hit on every request
        $isBlocked = Cache::remember(
            "ip_blocked:{$ip}",
            300,
            fn() => BlockedIp::isBlocked($ip)
        );

        if ($isBlocked) {
            BlockedIp::where('ip_address', $ip)->increment('hit_count');

            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}