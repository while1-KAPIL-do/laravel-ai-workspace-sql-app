<?php

use App\Http\Middleware\TokenMiddleware;
use App\Providers\RateLimiterServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
         $middleware->alias([
            'token.limit' => TokenMiddleware::class,
        ]);

        if (env('APP_ENV') === 'production') {
            $middleware->prependToGroup('web',  \App\Http\Middleware\BlockedIpMiddleware::class);
            $middleware->appendToGroup('web',   \App\Http\Middleware\SessionIntegrityMiddleware::class);
            $middleware->appendToGroup('web',   \App\Http\Middleware\DeviceFingerprintMiddleware::class);
        }

        $middleware->validateCsrfTokens();
        
    })
    ->withProviders([
        RateLimiterServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (
        \Symfony\Component\HttpKernel\Exception\HttpException $e,
        \Illuminate\Http\Request $request
    ) {
        // Only handle CSRF failures (419)
        if ($e->getStatusCode() !== 419) {
            return null; // let Laravel handle everything else normally
        }

        if (env('APP_ENV') === 'local') {
            return response()->json(['error' => 'CSRF token mismatch.'], 419);
        }

        $ip       = $request->ip();
        $abuseKey = "csrf_fail:{$ip}";
        $hits     = \Illuminate\Support\Facades\Cache::get($abuseKey, 0) + 1;

        \Illuminate\Support\Facades\Cache::put($abuseKey, $hits, now()->addHour());

        \Illuminate\Support\Facades\Log::warning('CSRF token mismatch', [
            'ip'         => $ip,
            'url'        => $request->fullUrl(),
            'method'     => $request->method(),
            'user_agent' => $request->userAgent(),
            'violations' => $hits,
        ]);

        if ($hits >= 5) {
            \App\Models\BlockedIp::block($ip, 'Auto-blocked: CSRF abuse', 'system', 1440);
            \Illuminate\Support\Facades\Cache::forget("ip_blocked:{$ip}");
            \Illuminate\Support\Facades\Log::critical('IP auto-blocked: CSRF abuse', ['ip' => $ip]);
        }

        return response()->json([
            'error'      => 'CSRF token mismatch.',
            'violations' => "{$hits} of 5 before auto-block",
        ], 419);
    });
})->create();
