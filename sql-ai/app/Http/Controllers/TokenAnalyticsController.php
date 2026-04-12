<?php

namespace App\Http\Controllers;

use App\Services\Token\TokenAnalyticsService;

class TokenAnalyticsController extends Controller
{
    public function __construct(
        protected TokenAnalyticsService $service
    ) {}

    public function summary()
    {
        return response()->json($this->service->summary());
    }

    public function daily()
    {
        return response()->json($this->service->dailyUsage(7));
    }

    public function topUsers()
    {
        return response()->json($this->service->topUsers());
    }

    public function topIps()
    {
        return response()->json($this->service->topIps());
    }

    public function cost()
    {
        return response()->json($this->service->estimatedCost());
    }
}