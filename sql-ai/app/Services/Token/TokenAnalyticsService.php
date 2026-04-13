<?php

namespace App\Services\Token;

use App\Models\TokenUsage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TokenAnalyticsService
{
    private function applyFilters($query, $provider = null, $model = null)
    {
        if ($provider && $provider !== 'all') {
            $query->where('provider', $provider);
        }
        if ($model && $model !== 'all') {
            $query->where('model', $model);
        }
        return $query;
    }

    public function summary($provider = null, $model = null)
    {
        $today = Carbon::today()->toDateString();

        // Base query for all-time stats
        $baseQuery = TokenUsage::query();
        $baseQuery = $this->applyFilters($baseQuery, $provider, $model);

        // Today query
        $todayQuery = TokenUsage::where('date', $today);
        $todayQuery = $this->applyFilters($todayQuery, $provider, $model);

        return [
            'today_tokens'  => $todayQuery->sum('total_tokens'),
            'total_tokens'  => $baseQuery->sum('total_tokens'),
            'today_cost'    => $todayQuery->sum('cost'),
            'total_cost'    => $baseQuery->sum('cost'),
            'unique_users'  => $baseQuery->distinct('ip')->count('ip'),   // or use user_id if available
        ];
    }

    public function dailyUsage($days = 30, $provider = null, $model = null)
    {
        $query = TokenUsage::select(
            'date',
            DB::raw('SUM(input_tokens) as input_tokens'),
            DB::raw('SUM(output_tokens) as output_tokens'),
            DB::raw('SUM(total_tokens) as total_tokens'),
            DB::raw('SUM(cost) as total_cost')
        )
        ->where('date', '>=', Carbon::now()->subDays($days));

        $query = $this->applyFilters($query, $provider, $model);

        return $query->groupBy('date')
                     ->orderBy('date')
                     ->get();
    }

    public function topIps($limit = 10, $provider = null, $model = null)
    {
        $query = TokenUsage::select(
            'ip',
            DB::raw('SUM(total_tokens) as total_tokens'),
            DB::raw('SUM(cost) as total_cost'),
            DB::raw('COUNT(*) as request_count')
        );

        $query = $this->applyFilters($query, $provider, $model);

        return $query->groupBy('ip')
                     ->orderByDesc('total_tokens')
                     ->limit($limit)
                     ->get();
    }

    public function topUsers($limit = 10, $provider = null, $model = null)
    {
        $query = TokenUsage::select(
            'ip',                       // TODO: change to user_id when available
            DB::raw('SUM(total_tokens) as total_tokens'),
            DB::raw('SUM(cost) as total_cost'),
            DB::raw('COUNT(*) as request_count')
        );

        $query = $this->applyFilters($query, $provider, $model);

        return $query->groupBy('ip')
                     ->orderByDesc('total_tokens')
                     ->limit($limit)
                     ->get();
    }

    public function estimatedCost($provider = null, $model = null)
    {
        $query = TokenUsage::query();
        $query = $this->applyFilters($query, $provider, $model);

        return [
            'tokens' => $query->sum('total_tokens'),
            'cost'   => round($query->sum('cost'), 6),
        ];
    }

    public function getFilters()
    {
        return [
            'providers' => TokenUsage::distinct()->pluck('provider'),
            'models'    => TokenUsage::distinct()->pluck('model'),
        ];
    }

    public function costBreakdown($days = 7)
    {
        return TokenUsage::select(
            'date',
            DB::raw('SUM(cost) as total_cost')
        )
        ->where('date', '>=', Carbon::now()->subDays($days))
        ->groupBy('date')
        ->orderBy('date')
        ->get();
    }
}