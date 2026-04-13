<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Token\TokenManager;

class TokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $input = $request->input('prompt', '');
        $ip = $request->ip();

        $tokenManager = app(TokenManager::class);
        $result = $tokenManager->validate($input, $ip);

        if (!$result['allowed']) {
            return response()->json([
                'error' => 'Token limit exceeded',
                'reason' => $result['reason'] ?? null
            ], 429);
        }

        // Updated structure (future-proof)
        $request->attributes->set('token_meta', [
            'input_tokens' => $result['tokens'] ?? 0,
            'output_tokens' => 0, 
            'total_tokens' => $result['tokens'] ?? 0,
        ]);

        return $next($request);
    }
}