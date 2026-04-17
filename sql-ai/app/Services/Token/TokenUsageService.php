<?php

namespace App\Services\Token;

use App\Models\TokenUsage;
use Carbon\Carbon;

class TokenUsageService
{
    
    public function addUsage(
        string $ip, 
        int $inputTokens, 
        int $outputTokens, 
        float $cost, 
        string $provider = 'openai',
        string $model = 'gpt-4o-mini'
    ): void
    {
        $today = now()->toDateString();

        $usage = \App\Models\TokenUsage::firstOrCreate(
            [
                'ip' => $ip, 
                'date' => $today,
                'model' => $model,
                'provider' => $provider
            ],
            [
                'input_tokens' => 0,
                'output_tokens' => 0,
                'total_tokens' => 0,
                'cost' => 0
            ]
        );

        $total = $inputTokens + $outputTokens;

        $usage->increment('input_tokens', $inputTokens);
        $usage->increment('output_tokens', $outputTokens);
        $usage->increment('total_tokens', $total);
        $usage->increment('cost', $cost);
    }

    public function getUsage(string $ip): int
    {
        $today = now()->toDateString();

        $usage = \App\Models\TokenUsage::where('ip', $ip)
            ->where('date', $today)
            ->first();

        return $usage?->total_tokens ?? 0;
    }

    public function isExceeded(string $ip): bool
    {
        return $this->getUsage($ip) > config('llm.tokens.daily_limit_per_ip');
    }
}