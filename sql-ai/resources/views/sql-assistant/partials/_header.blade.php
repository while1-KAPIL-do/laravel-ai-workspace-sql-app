{{-- ============================================================
     Partial: _header.blade.php
     Renders the page title and dark/light theme toggle button.
     Variables used: none (standalone)
     ============================================================ --}}
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-4xl font-bold tracking-tight">SQL AI Voice Assistant</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Speak, type, or upload audio to query your database</p>
    </div>

    <div class="flex items-center gap-3">

        {{-- IP Label --}}
        <div class="flex items-center gap-2 px-3 py-1.5
                    bg-slate-100 dark:bg-slate-800/80
                    border border-slate-200 dark:border-cyan-400/20
                    rounded-xl shadow-sm">
            <span class="text-[10px] font-semibold uppercase tracking-widest 
                        text-cyan-600 dark:text-cyan-400">IP</span>
            <span class="w-px h-3 bg-slate-300 dark:bg-slate-600"></span>
            <span id="userIpLabel" class="text-xs font-mono 
                                        text-slate-700 dark:text-slate-200 tracking-wide">—</span>
        </div>

        {{-- Token Usage Label --}}
        <div class="flex items-center gap-2 px-3 py-1.5
                    bg-slate-100 dark:bg-slate-800/80
                    border border-slate-200 dark:border-amber-400/20
                    rounded-xl shadow-sm">
            <span class="text-[10px] font-semibold uppercase tracking-widest 
                        text-amber-500 dark:text-amber-300">Tokens</span>
            <span class="w-px h-3 bg-slate-300 dark:bg-slate-600"></span>
            <span id="tokenUsageLabel" class="text-xs font-mono 
                                            text-slate-700 dark:text-slate-200 tracking-wide">—</span>
        </div>

        {{-- Theme Toggle --}}
        <button onclick="toggleTheme()" id="themeToggle"
            class="w-11 h-11 flex items-center justify-center bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-2xl transition-all shadow-sm">
            <i id="themeIcon" class="fas fa-moon text-xl text-slate-700 dark:text-slate-300"></i>
        </button>
    </div>
</div>
