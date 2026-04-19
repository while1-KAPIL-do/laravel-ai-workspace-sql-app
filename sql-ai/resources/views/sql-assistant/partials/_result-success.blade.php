{{-- ============================================================
     Partial: _result-success.blade.php
     Displays the success card with generated SQL, an "Execute SQL"
     button, and a dynamic results table rendered by JS.
     Variables used: session('result') — resolved internally.
     ============================================================ --}}

@php $result = session('result'); @endphp
@if(!empty($result['success']))
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl shadow-sm px-6 py-6 fade-in">

    {{-- Card header --}}
    <div class="flex items-center gap-3 mb-5">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-emerald-100 dark:bg-emerald-500/10">
            <i class="fas fa-check text-emerald-500 text-sm"></i>
        </div>
        <div>
            <p class="text-sm font-semibold leading-none">Generated SQL</p>
            <p class="text-xs text-slate-400 mt-1">Based on your query</p>
        </div>
    </div>

    {{-- Original question --}}
    <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3 mb-4">
        <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Your question</p>
        <p class="text-sm text-slate-700 dark:text-slate-300">"{{ $result['user_question'] ?? '' }}"</p>
    </div>

    {{-- SQL code block --}}
    <div class="relative">

        <!-- SQL tag (TOP RIGHT) -->
        <span class="absolute top-3 right-3 z-10 text-xs font-semibold px-2 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-400 tracking-wider uppercase">
            SQL
        </span>

        <!-- Copy button (BOTTOM RIGHT) -->
        <button id="copySqlBtn"
                class="absolute bottom-3 right-3 z-10 p-2 rounded-lg bg-slate-800 hover:bg-slate-700 transition text-slate-300 hover:text-white"
                title="Copy SQL">
            <i class="fas fa-copy text-xs"></i>
        </button>

        <!-- SQL block -->
        <pre id="sqlQuery"
            class="bg-slate-950 text-emerald-400 px-5 pt-5 pb-12 rounded-2xl border border-slate-800 overflow-x-auto"
            >{{ $result['generated_sql'] ?? '' }}
        </pre>

    </div>

    {{-- Execute button --}}
    <!-- <div class="mt-5">
        <button id="executeSqlBtn"
                class="flex items-center gap-2 px-5 py-2.5 rounded-2xl text-sm font-semibold text-white transition-all hover:opacity-90"
                style="background:linear-gradient(135deg,#10b981,#059669);">
            <i class="fas fa-play text-xs"></i>
            Execute SQL
        </button>
    </div> -->

    {{-- JS-rendered results table injected here by sql-assistant.js --}}
    <div id="executionResult" class="mt-5"></div>

</div>
@endif
