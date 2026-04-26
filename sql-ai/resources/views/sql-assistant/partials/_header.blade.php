{{-- ============================================================
     Partial: _header.blade.php
     Renders the page title and dark/light theme toggle button.
     Variables used: none (standalone)
     ============================================================ --}}

<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-4xl font-bold tracking-tight">SQL AI Voice Assistant - Dev</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Speak, type, or upload audio to query your database</p>
    </div>
    <button onclick="toggleTheme()" id="themeToggle"
            class="w-11 h-11 flex items-center justify-center bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-2xl transition-all shadow-sm">
        <i id="themeIcon" class="fas fa-moon text-xl text-slate-700 dark:text-slate-300"></i>
    </button>
</div>
