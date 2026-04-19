{{-- ============================================================
     Partial: _ai-selector-toolbar.blade.php
     Reusable provider + model dropdown toolbar + send button.
     Include once per input panel, passing the panel identifier.

     Required variable:
       $panel  — string, one of: 'text' | 'mic' | 'upload'

     Optional variables:
       $submitId      — HTML id for the submit button (default: 'submitBtn')
       $submitDisabled — bool, renders the button as disabled (default: false)
       $borderTop     — bool, adds a top border (default: true)
     ============================================================ --}}

@php
    $panel          = $panel          ?? 'text';
    $submitId       = $submitId       ?? 'submitBtn';
    $submitDisabled = $submitDisabled ?? false;
    $borderTop      = $borderTop      ?? true;
@endphp

<div class="flex items-center gap-2 {{ $borderTop ? 'pt-2 border-t border-slate-200 dark:border-slate-700' : '' }} flex-wrap">

    {{-- Provider selector --}}
    <div class="relative" id="pw-{{ $panel }}">
        <button type="button" onclick="aiToggle('p','{{ $panel }}')" id="pb-{{ $panel }}"
                class="ai-trigger flex items-center gap-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 hover:border-cyan-400 dark:hover:border-cyan-500 rounded-xl px-3 h-10 transition-all focus:outline-none focus:ring-2 focus:ring-cyan-500/20">
            <i class="fas fa-microchip text-slate-400 dark:text-slate-500 text-xs"></i>
            <span class="provider-label text-sm font-semibold text-slate-700 dark:text-slate-200">OpenAI</span>
            <i class="fas fa-chevron-down text-slate-400 text-xs ai-chevron transition-transform duration-200"></i>
        </button>
        <ul class="ai-dropdown hidden absolute left-0 top-full mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl z-50 py-1.5 min-w-[160px] overflow-hidden"
            id="pd-{{ $panel }}"></ul>
    </div>

    {{-- Model selector --}}
    <div class="relative" id="mw-{{ $panel }}">
        <button type="button" onclick="aiToggle('m','{{ $panel }}')" id="mb-{{ $panel }}"
                class="ai-trigger flex items-center gap-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 hover:border-cyan-400 dark:hover:border-cyan-500 rounded-xl px-3 h-10 transition-all focus:outline-none focus:ring-2 focus:ring-cyan-500/20">
            <i class="fas fa-cube text-slate-400 dark:text-slate-500 text-xs"></i>
            <span class="model-label text-sm font-semibold text-slate-700 dark:text-slate-200">Select model</span>
            <i class="fas fa-chevron-down text-slate-400 text-xs ai-chevron transition-transform duration-200"></i>
        </button>
        <ul class="ai-dropdown hidden absolute left-0 top-full mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl z-50 py-1.5 min-w-[250px] max-h-60 overflow-y-auto"
            id="md-{{ $panel }}"></ul>
    </div>

    <div class="flex-1"></div>

    {{-- Send button --}}
    <button type="submit" id="{{ $submitId }}"
            class="send-btn h-10 px-5 rounded-xl text-sm font-semibold text-slate-900 flex items-center gap-2 hover:opacity-90 transition-opacity"
            style="background-color:#22d3ee;"
            {{ $submitDisabled ? 'disabled' : '' }}>
        Send <i class="fas fa-paper-plane text-xs"></i>
    </button>

</div>
