<?php

namespace App\Services\Token;

use App\Models\TokenUsage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TokenAnalyticsService
{
    public function summary()
    {
        $today = Carbon::today()->toDateString();

        return [
            'today_tokens' => TokenUsage::where('date', $today)->sum('tokens_used'),
            'total_tokens' => TokenUsage::sum('tokens_used'),
            'unique_users' => TokenUsage::distinct('ip')->count('ip'),
        ];
    }

    public function dailyUsage($days = 7)
    {
        return TokenUsage::select('date', DB::raw('SUM(tokens_used) as total'))
            ->where('date', '>=', Carbon::now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    public function topIps($limit = 5)
    {
        return TokenUsage::select('ip', DB::raw('SUM(tokens_used) as total'))
            ->groupBy('ip')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
    }

    public function topUsers($limit = 5)
    {
        return TokenUsage::select('ip', DB::raw('SUM(tokens_used) as total'))
            ->groupBy('ip') // TODO 'user_id' - Need to add
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
    }

    public function estimatedCost($costPer1kTokens = 0.002) // adjust later
    {
        $totalTokens = TokenUsage::sum('tokens_used');

        return [
            'tokens' => $totalTokens,
            'cost' => round(($totalTokens / 1000) * $costPer1kTokens, 4)
        ];
    }
}
