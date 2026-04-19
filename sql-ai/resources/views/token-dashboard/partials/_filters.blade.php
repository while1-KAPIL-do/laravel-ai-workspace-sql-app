{{-- ============================================================
     Partial: _filters.blade.php
     Provider and model filter button rows.
     Both rows are populated dynamically by JS (token-dashboard.js).
     Variables used: none
     ============================================================ --}}

<div class="mb-8 flex gap-8 flex-wrap">
    <div>
        <p class="text-sm mb-2 text-slate-500 dark:text-slate-400">Provider</p>
        <div id="providerFilters" class="flex gap-2 flex-wrap"></div>
    </div>
    <div>
        <p class="text-sm mb-2 text-slate-500 dark:text-slate-400" id="modelLabel">Model</p>
        <div id="modelFilters" class="flex gap-2 flex-wrap"></div>
    </div>
</div>
