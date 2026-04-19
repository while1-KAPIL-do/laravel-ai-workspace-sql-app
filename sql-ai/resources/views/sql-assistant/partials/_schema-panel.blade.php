{{-- ============================================================
     Partial: _schema-panel.blade.php
     Collapsible database schema explorer with table chips,
     column detail view, and schema file upload.
     Variables used: none (data loaded via JS fetch to /schema/analytics/schema)
     ============================================================ --}}

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl shadow-sm mb-4 overflow-hidden">

    {{-- ── Panel Header ── --}}
    <div class="w-full flex items-center justify-between px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">

        <div class="flex items-center gap-3 cursor-pointer flex-grow" onclick="toggleSchema()">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center bg-cyan-100 dark:bg-cyan-500/10">
                <i class="fas fa-database text-sm text-cyan-500"></i>
            </div>
            <div class="text-left">
                <p class="text-sm font-semibold leading-none">Database Schema</p>
                <p id="schemaSubtitle" class="text-xs text-slate-400 mt-1">Click to explore tables & columns</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            {{-- Schema file upload form --}}
            <form id="uploadSchemaForm" action="{{ route('schema.upload') }}" method="POST" enctype="multipart/form-data" class="flex items-center">
                @csrf
                <input type="file" name="schema" id="schemaFileInput" class="hidden" accept=".sql,.txt" onchange="handleAutoUpload()">
                <label for="schemaFileInput" class="cursor-pointer flex items-center gap-2 text-xs font-medium px-3 py-1.5 rounded-full bg-slate-100 dark:bg-slate-800 hover:bg-cyan-500 hover:text-white transition-all text-slate-600 dark:text-slate-400">
                    <i class="fas fa-upload text-[10px]"></i>
                    <span id="uploadBtnText">Upload Your DB Schema</span>
                </label>
            </form>

            <span id="tableCountBadge" class="hidden text-xs font-medium px-3 py-1 rounded-full bg-cyan-100 dark:bg-cyan-500/10 text-cyan-600 dark:text-cyan-400"></span>

            <button type="button" onclick="toggleSchema()" class="p-1 focus:outline-none">
                <i id="schemaChevron" class="fas fa-chevron-down text-slate-400 chevron-icon"></i>
            </button>
        </div>
    </div>

    {{-- ── Collapsible Body ── --}}
    <div class="schema-body" id="schemaBody">
        <div class="border-t border-slate-200 dark:border-slate-700 px-6 py-5">

            {{-- Loading state --}}
            <div id="schemaLoading" class="flex items-center gap-2 text-slate-400 text-sm py-2">
                <i class="fas fa-circle-notch fa-spin text-cyan-400"></i> Loading schema…
            </div>

            {{-- Table chips --}}
            <div id="tableChips" class="hidden flex flex-wrap gap-2 mb-4"></div>

            {{-- Column detail card --}}
            <div id="columnDetail" class="hidden">
                <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-2xl p-4">
                    <div class="flex items-center justify-between mb-3">
                        <p id="colDetailTitle" class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"></p>
                        <span id="colDetailRowCount" class="text-xs font-medium px-2 py-0.5 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300"></span>
                    </div>
                    <div id="colRows"></div>
                </div>
            </div>

            {{-- Error state --}}
            <div id="schemaError" class="hidden text-sm text-red-400 py-2 flex items-center gap-2">
                <i class="fas fa-circle-exclamation"></i> Could not load schema.
            </div>

        </div>
    </div>

</div>
