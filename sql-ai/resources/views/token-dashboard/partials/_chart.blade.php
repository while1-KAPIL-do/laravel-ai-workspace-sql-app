{{-- ============================================================
     Partial: _chart.blade.php
     Daily token usage chart with Line/Bar toggle.
     Chart is rendered dynamically by JS (token-dashboard.js).
     Variables used: none
     ============================================================ --}}

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl p-8 shadow-sm">

    {{-- Chart header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-semibold">Daily Tokens Usage</h2>
            <p class="text-slate-500 dark:text-slate-400 text-sm">Last 30 days</p>
        </div>
        <div class="flex gap-2">
            <button onclick="changeChartType('line')"
                    class="px-5 py-2 rounded-2xl text-sm font-medium bg-cyan-500 text-white"
                    id="btn-line">
                Line Chart
            </button>
            <button onclick="changeChartType('bar')"
                    class="px-5 py-2 rounded-2xl text-sm font-medium bg-slate-200 dark:bg-slate-700"
                    id="btn-bar">
                Bar Chart
            </button>
        </div>
    </div>

    {{-- Chart canvas --}}
    <div class="chart-container">
        <canvas id="chart"></canvas>
    </div>

</div>
