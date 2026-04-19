{{-- ============================================================
     Partial: _result-error.blade.php
     Displays the error card when the session contains an 'error'
     key. Shown only when $err is non-empty.
     Variables used: session('error') — resolved internally.
     ============================================================ --}}

@if(session('error'))
    @php $err = session('error'); @endphp
    @if(!empty($err))
    <div class="bg-white dark:bg-slate-900 border border-red-200 dark:border-red-500/30 rounded-3xl shadow-sm px-6 py-6 fade-in">

        {{-- Card header --}}
        <div class="flex items-center gap-3 mb-5">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-red-100 dark:bg-red-500/10">
                <i class="fas fa-circle-exclamation text-red-500 text-sm"></i>
            </div>
            <div>
                <p class="text-sm font-semibold leading-none">Something went wrong</p>
                <p class="text-xs text-slate-400 mt-1">The AI could not process your request</p>
            </div>
        </div>

        {{-- Original question --}}
        @if(!empty($err['user_question']))
        <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3 mb-4">
            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Your question</p>
            <p class="text-sm text-slate-700 dark:text-slate-300">"{{ $err['user_question'] }}"</p>
        </div>
        @endif

        {{-- Error message --}}
        <div class="flex items-start gap-3 bg-red-500/10 border border-red-500/20 text-red-400 px-5 py-4 rounded-2xl text-sm">
            <i class="fas fa-circle-xmark mt-0.5 flex-shrink-0"></i>
            <div>
                <p class="font-medium mb-0.5">Error</p>
                <p class="text-red-300">{{ $err['error'] ?? 'Unknown error' }}</p>
            </div>
        </div>

        {{-- SQL attempted --}}
        @if(!empty($err['generated_sql']))
        <div class="mt-4">
            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-2">SQL attempted</p>
            <pre class="bg-slate-950 text-red-400 px-5 py-4 rounded-2xl border border-slate-800 overflow-x-auto text-xs font-mono">{{ $err['generated_sql'] }}</pre>
        </div>
        @endif

    </div>
    @endif
@endif
