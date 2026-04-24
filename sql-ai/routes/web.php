<?php

use App\Http\Controllers\VoiceToSqlController;
use App\Http\Controllers\TokenAnalyticsController;
use App\Http\Controllers\SchemaController;
use Illuminate\Support\Facades\Route;

// ── Welcome ───────────────────────────────────────────────────
Route::get('/', function () {
    return view('welcome');
});

// ── Health Check ───────────────────────────────────────────────────
Route::get('/health', function () {
    return response()->json(['status' => 'ok'], 200);
});

// ── AI routes — 10 requests/min ───────────────────────────────
Route::middleware(['throttle:ai'])->prefix('ai')->group(function () {
    Route::post('/sql-assitance',   [VoiceToSqlController::class,       'process'])->name('ai-sql-assitance');
    Route::get('/sql-assitance',    [VoiceToSqlController::class,       'getPageData'])->name('ai-sql-assitance-index');
    Route::get('/token-dashboard',  [TokenAnalyticsController::class,   'getPageData'])->name('ai-token-dashboard');
});

// ── Schema routes — 5 uploads/hour ───────────────────────────
Route::middleware(['throttle:schema-upload'])->prefix('schema')->group(function () {
    Route::post('/upload',          [SchemaController::class, 'uploadSchema'])->name('schema.upload');
    Route::post('/execute-sql',     [SchemaController::class, 'executeSql']);
    Route::get('/analytics/schema', [SchemaController::class, 'dbSchema']);
});

// ── Analytics routes — 60 requests/min ───────────────────────
Route::middleware(['throttle:web-general'])->prefix('analytics')->group(function () {
    Route::get('/summary',        [TokenAnalyticsController::class, 'summary']);
    Route::get('/daily',          [TokenAnalyticsController::class, 'daily']);
    Route::get('/top-ips',        [TokenAnalyticsController::class, 'topIps']);
    Route::get('/top-users',      [TokenAnalyticsController::class, 'topUsers']);
    Route::get('/cost',           [TokenAnalyticsController::class, 'cost']);
    Route::get('/cost-breakdown', [TokenAnalyticsController::class, 'costBreakdown']);
    Route::get('/filters',        [TokenAnalyticsController::class, 'filters']);
});