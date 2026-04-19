{{-- ============================================================
     Partial: _header.blade.php
     Page title, subtitle, theme toggle, and refresh button.
     Variables used: none (standalone)
     ============================================================ --}}

<div class="flex justify-between items-center mb-10">
    <div>
        <h1 class="text-4xl font-bold tracking-tight flex items-center gap-3">
            Token Analytics
        </h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Real-time overview of token usage</p>
    </div>
    <div class="flex items-center gap-4">
        <button onclick="toggleTheme()" id="theme-toggle"
                class="w-11 h-11 flex items-center justify-center bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-2xl transition-all shadow-sm">
            <i id="theme-icon" class="fas fa-moon text-xl text-slate-700 dark:text-slate-300"></i>
        </button>
        <button onclick="loadData()"
                class="px-5 py-2 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-2xl flex items-center gap-2 transition-colors">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
    </div>
</div>
