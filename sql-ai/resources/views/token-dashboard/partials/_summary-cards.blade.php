{{-- ============================================================
     Partial: _summary-cards.blade.php
     Four KPI cards: Tokens Today, Total Tokens, Unique IPs, Cost.
     All values are populated dynamically by JS (token-dashboard.js).
     Variables used: none
     ============================================================ --}}

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">

    {{-- Tokens Today --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl p-6 card-hover shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <i class="fas fa-bolt text-3xl text-emerald-500"></i>
            <span class="text-xs font-medium px-3 py-1 bg-emerald-100 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-full">Today</span>
        </div>
        <p class="text-5xl font-semibold tracking-tighter" id="today">0</p>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Tokens Used Today</p>
    </div>

    {{-- Total Tokens --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl p-6 card-hover shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <i class="fas fa-database text-3xl text-blue-500"></i>
            <span class="text-xs font-medium px-3 py-1 bg-blue-100 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-full">All Time</span>
        </div>
        <p class="text-5xl font-semibold tracking-tighter" id="total">0</p>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Total Tokens</p>
    </div>

    {{-- Unique IPs --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl p-6 card-hover shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <i class="fas fa-users text-3xl text-violet-500"></i>
            <span class="text-xs font-medium px-3 py-1 bg-violet-100 dark:bg-violet-500/10 text-violet-600 dark:text-violet-400 rounded-full">Active</span>
        </div>
        <p class="text-5xl font-semibold tracking-tighter" id="users">0</p>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Unique IPs</p>
    </div>

    {{-- Total Cost --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl p-6 card-hover shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <i class="fas fa-dollar-sign text-3xl text-rose-500"></i>
            <span class="text-xs font-medium px-3 py-1 bg-rose-100 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 rounded-full">Cost</span>
        </div>
        <p class="text-5xl font-semibold tracking-tighter" id="cost">$0</p>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Total Cost</p>
    </div>

</div>
