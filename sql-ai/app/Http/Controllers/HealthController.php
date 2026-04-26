<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;

class HealthController extends Controller
{
    public function getHealth(Request $request)
    {
        $checks = [];
        // 1. Database connection
        try {
            DB::connection()->getPdo();
            $checks['database'] = 'ok';
        } catch (\Exception $e) {
            $checks['database'] = 'fail';
        }

        // 2. Cache read/write
        try {
            Cache::put('health_check', 'ok', 5);
            $checks['cache'] = Cache::get('health_check') === 'ok' ? 'ok' : 'fail';
        } catch (\Exception $e) {
            $checks['cache'] = 'fail';
        }

        // 3. LLM service reachability (your Python container on port 5000)
        try {
            $response = Http::timeout(3)->get('http://llm-service:8000/health');
            $checks['llm'] = $response->ok() ? 'ok' : 'fail';
        } catch (\Exception $e) {
            $checks['llm'] = 'fail';
        }

        // Only return 200 if ALL checks pass
        $allOk = !in_array('fail', $checks);

         return response()->json([
            'status' => $allOk ? 'ok' : 'degraded',
            'checks' => $checks,
        ], $allOk ? 200 : 503)
        ->header('Access-Control-Allow-Origin', 'https://while1-kapil-do.github.io');        
        
    }

}
