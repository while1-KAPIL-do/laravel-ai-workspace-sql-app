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

        $middleware->prependToGroup(
            'web',
            \App\Http\Middleware\BlockedIpMiddleware::class
        );

        $middleware->appendToGroup(
            'web', 
            \App\Http\Middleware\SessionIntegrityMiddleware::class
        );
        
    })
    ->withProviders([
        RateLimiterServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
