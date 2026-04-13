<?php

namespace App\Http\Controllers;

use App\Services\Token\TokenAnalyticsService;
use Illuminate\Http\Request;

class TokenAnalyticsController extends Controller
{
    public function __construct(
        protected TokenAnalyticsService $service
    ) {}

    public function summary(Request $request)
    {
        return response()->json(
            $this->service->summary(
                $request->query('provider'),
                $request->query('model')
            )
        );
    }

    public function daily(Request $request)
    {
        return response()->json(
            $this->service->dailyUsage(
                30, 
                $request->query('provider'),
                $request->query('model')
            )
        );
    }

    public function filters()
    {
        return response()->json($this->service->getFilters());
    }

    public function topUsers(Request $request)
    {
        return response()->json(
            $this->service->topUsers(
                30, 
                $request->query('provider'),
                $request->query('model')
            )
        );
    }

    public function topIps(Request $request)
    {
        return response()->json(
            $this->service->topIps(
                30, 
                $request->query('provider'),
                $request->query('model')
            )
        );
    }

    public function cost(Request $request)
    {
        return response()->json(
            $this->service->estimatedCost(
                $request->query('provider'),
                $request->query('model')
            )
        );
    }

    public function costBreakdown()
    {
        return response()->json($this->service->costBreakdown(30));
    }
}