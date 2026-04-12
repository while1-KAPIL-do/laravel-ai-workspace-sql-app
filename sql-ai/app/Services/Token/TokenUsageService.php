<?php

namespace App\Services\Token;

use App\Models\TokenUsage;
use Carbon\Carbon;

class TokenUsageService
{
    public function addUsage(string $ip, int $tokens): void
    {
        $today = Carbon::today()->toDateString();

        $usage = TokenUsage::firstOrCreate(
            ['ip' => $ip, 'date' => $today],
            ['tokens_used' => 0]
        );

        $usage->increment('tokens_used', $tokens);
    }

    public function getUsage(string $ip): int
    {
        $today = Carbon::today()->toDateString();

        return TokenUsage::where('ip', $ip)
            ->where('date', $today)
            ->value('tokens_used') ?? 0;
    }

    public function isExceeded(string $ip): bool
    {
        return $this->getUsage($ip) > config('tokens.daily_limit_per_ip');
    }
}