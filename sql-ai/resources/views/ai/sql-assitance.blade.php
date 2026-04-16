<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL AI Voice Assistant</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', content: [], theme: { extend: {} } }
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }

        /* ── Schema panel ── */
        .schema-body {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .schema-body.open { max-height: 600px; }
        .chevron-icon { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .chevron-icon.open { transform: rotate(180deg); }

        /* ── Table chips ── */
        .table-chip { transition: all 0.18s; cursor: pointer; }
        .table-chip:hover { border-color: #22d3ee !important; }
        .table-chip.active { border-color: #22d3ee !important; background-color: rgba(34,211,238,0.06) !important; }
        .table-chip.active .chip-name { color: #22d3ee !important; }

        /* ── Mode cards ── */
        .mode-card {
            transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }
        .mode-card:hover { border-color: #22d3ee !important; }
        .mode-card.active {
            border-color: #22d3ee !important;
            border-width: 1.5px !important;
            background-color: rgba(34,211,238,0.05) !important;
        }
        .mode-card.active .mode-icon-wrap {
            background-color: rgba(34,211,238,0.15) !important;
        }
        .mode-card.active .mode-label { color: #22d3ee !important; }

        /* ── Input panels ── */
        .input-panel { display: none; }
        .input-panel.active { display: block; }

        .composer-box { transition: border-color 0.2s; }
        .composer-box:focus-within { border-color: #22d3ee !important; }

        #queryText {
            resize: none;
            background: transparent;
            border: none;
            outline: none;
            width: 100%;
            font-family: 'Inter', system-ui, sans-serif;
        }

        /* ── Mic ── */
        .mic-btn { transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); }
        .mic-btn.recording {
            animation: pulse-rec 1.5s infinite;
            background-color: #ef4444 !important;
            border-color: #ef4444 !important;
            color: #fff !important;
        }
        @keyframes pulse-rec {
            0%,100% { box-shadow: 0 0 0 0 rgba(239,68,68,0.4); }
            50%      { box-shadow: 0 0 0 12px rgba(239,68,68,0); }
        }

        /* ── Upload zone ── */
        .upload-zone { transition: border-color 0.2s, background-color 0.2s; }
        .upload-zone:hover {
            border-color: #22d3ee !important;
            background-color: rgba(34,211,238,0.03) !important;
        }
        .upload-zone.has-file {
            border-style: solid !important;
            border-color: #22d3ee !important;
        }

        /* ── Send button disabled ── */
        .send-btn:disabled {
            opacity: 0.4 !important;
            cursor: not-allowed !important;
        }

        /* ── Fade in ── */
        .fade-in { animation: fadeIn 0.4s cubic-bezier(0.4,0,0.2,1); }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── SQL pre ── */
        pre#sqlQuery {
            font-family: 'JetBrains Mono','Fira Code','Courier New',monospace;
            font-size: 0.82rem;
            line-height: 1.8;
            white-space: pre-wrap;
            word-break: break-word;
        }

        /* ── Column rows ── */
        .col-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid;
        }
        .col-row:last-child { border-bottom: none; }
    </style>
</head>

<body class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-950 dark:to-slate-900 text-slate-900 dark:text-slate-200 min-h-screen transition-colors duration-300">

    <div class="max-w-7xl mx-auto px-6 py-8">

        <!-- ── Header ── -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-4xl font-bold tracking-tight">SQL AI Voice Assistant</h1>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Speak, type, or upload audio to query your database</p>
            </div>
            <button onclick="toggleTheme()" id="themeToggle"
                    class="w-11 h-11 flex items-center justify-center bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-2xl transition-all shadow-sm">
                <i id="themeIcon" class="fas fa-moon text-xl text-slate-700 dark:text-slate-300"></i>
            </button>
        </div>

        <!-- ── DB Schema Collapsible Panel ── -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl shadow-sm mb-4 overflow-hidden">

            <button type="button" onclick="toggleSchema()"
                    class="w-full flex items-center justify-between px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center bg-cyan-100 dark:bg-cyan-500/10">
                        <i class="fas fa-database text-sm text-cyan-500"></i>
                    </div>
                    <div class="text-left">
                        <p class="text-sm font-semibold leading-none">Database Schema</p>
                        <p id="schemaSubtitle" class="text-xs text-slate-400 mt-1">Click to explore tables &amp; columns</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span id="tableCountBadge"
                          class="hidden text-xs font-medium px-3 py-1 rounded-full bg-cyan-100 dark:bg-cyan-500/10 text-cyan-600 dark:text-cyan-400">
                    </span>
                    <i id="schemaChevron" class="fas fa-chevron-down text-slate-400 chevron-icon"></i>
                </div>
            </button>

            <div class="schema-body" id="schemaBody">
                <div class="border-t border-slate-200 dark:border-slate-700 px-6 py-5">

                    <div id="schemaLoading" class="flex items-center gap-2 text-slate-400 text-sm py-2">
                        <i class="fas fa-circle-notch fa-spin text-cyan-400"></i>
                        Loading schema…
                    </div>

                    <div id="tableChips" class="hidden flex flex-wrap gap-2 mb-4"></div>

                    <div id="columnDetail" class="hidden">
                        <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-2xl p-4">
                            <div class="flex items-center justify-between mb-3">
                                <p id="colDetailTitle" class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"></p>
                                <span id="colDetailRowCount"
                                      class="text-xs font-medium px-2 py-0.5 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                </span>
                            </div>
                            <div id="colRows"></div>
                        </div>
                    </div>

                    <div id="schemaError" class="hidden text-sm text-red-400 py-2 flex items-center gap-2">
                        <i class="fas fa-circle-exclamation"></i>
                        Could not load schema. Check the /ai/analytics/schema endpoint.
                    </div>

                </div>
            </div>
        </div>

        <!-- ── Input Card ── -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl shadow-sm px-6 py-6 mb-4">

            <form action="{{ route('ai-sql-assitance') }}" method="POST" enctype="multipart/form-data" id="voiceForm">
                @csrf

                <!-- Hidden input to track active mode — read by backend -->
                <input type="hidden" name="input_mode" id="inputMode" value="text">

                <!-- ── Mode Selection Cards ── -->
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Choose your input method</p>

                <div class="grid grid-cols-3 gap-3 mb-5">

                    <!-- Type card -->
                    <div class="mode-card active border border-slate-200 dark:border-slate-700 rounded-2xl p-4 text-center bg-white dark:bg-slate-800"
                         onclick="switchMode('text')">
                        <div class="mode-icon-wrap w-10 h-10 rounded-xl flex items-center justify-center mx-auto mb-3 bg-slate-100 dark:bg-slate-700 transition-colors">
                            <i class="fas fa-keyboard text-slate-500 dark:text-slate-400" style="font-size:16px;"></i>
                        </div>
                        <p class="mode-label text-sm font-semibold text-slate-700 dark:text-slate-200 transition-colors">Type</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Write your query</p>
                    </div>

                    <!-- Record card -->
                    <div class="mode-card border border-slate-200 dark:border-slate-700 rounded-2xl p-4 text-center bg-white dark:bg-slate-800"
                         onclick="switchMode('mic')">
                        <div class="mode-icon-wrap w-10 h-10 rounded-xl flex items-center justify-center mx-auto mb-3 bg-slate-100 dark:bg-slate-700 transition-colors">
                            <i class="fas fa-microphone text-slate-500 dark:text-slate-400" style="font-size:16px;"></i>
                        </div>
                        <p class="mode-label text-sm font-semibold text-slate-700 dark:text-slate-200 transition-colors">Record</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Speak live</p>
                    </div>

                    <!-- Upload card -->
                    <div class="mode-card border border-slate-200 dark:border-slate-700 rounded-2xl p-4 text-center bg-white dark:bg-slate-800"
                         onclick="switchMode('upload')">
                        <div class="mode-icon-wrap w-10 h-10 rounded-xl flex items-center justify-center mx-auto mb-3 bg-slate-100 dark:bg-slate-700 transition-colors">
                            <i class="fas fa-arrow-up-from-bracket text-slate-500 dark:text-slate-400" style="font-size:16px;"></i>
                        </div>
                        <p class="mode-label text-sm font-semibold text-slate-700 dark:text-slate-200 transition-colors">Upload</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">MP3 / WAV file</p>
                    </div>

                </div>

                <!-- ── Panel: TYPE ── -->
                <div class="input-panel active" id="panel-text">
                    <div class="composer-box flex items-end gap-3 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 py-3">
                        <textarea id="queryText"
                                  name="text_query"
                                  rows="3"
                                  placeholder="e.g. Show total tokens used by each user last month…"
                                  class="flex-1 text-sm text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 leading-relaxed">{{ old('text_query') }}</textarea>
                        <button type="submit" id="submitBtn"
                                class="send-btn h-9 px-4 rounded-xl text-xs font-semibold text-slate-900 transition-all hover:opacity-90 flex items-center gap-1.5 flex-shrink-0"
                                style="background-color:#22d3ee;">
                            Send <i class="fas fa-paper-plane text-xs"></i>
                        </button>
                    </div>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-2 text-right">Ask in plain English — AI will generate the SQL</p>
                </div>

                <!-- ── Panel: MIC (record) ── -->
                <div class="input-panel" id="panel-mic">
                    <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-2xl px-6 py-6">
                        <div class="flex flex-col items-center gap-4">

                            <!-- Big mic button — id="recordBtn" & class kept for original JS -->
                            <button type="button" id="recordBtn"
                                    class="mic-btn w-16 h-16 flex items-center justify-center rounded-2xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:border-cyan-400 hover:text-cyan-500 transition-all">
                                <i class="fas fa-microphone text-2xl"></i>
                            </button>

                            <!-- id="recordingStatus" kept for original JS -->
                            <p id="recordingStatus" class="hidden text-sm text-red-400 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse inline-block"></span>
                                Recording in progress…
                            </p>

                            <p class="text-xs text-slate-400 dark:text-slate-500">Click to start · click again to stop</p>
                        </div>

                        <!-- id="fileNameDisplay" kept for original JS — shows after recording stops -->
                        <p id="fileNameDisplay" class="hidden text-xs text-slate-400 mt-4 text-center"></p>

                        <div class="flex justify-end mt-4">
                            <button type="submit" id="submitBtnMic"
                                    class="send-btn h-9 px-4 rounded-xl text-xs font-semibold text-slate-900 flex items-center gap-1.5"
                                    style="background-color:#22d3ee;" disabled>
                                Send <i class="fas fa-paper-plane text-xs"></i>
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-2 text-right">Recording will be transcribed then converted to SQL</p>
                </div>

                <!-- ── Panel: UPLOAD ── -->
                <div class="input-panel" id="panel-upload">
                    <label class="upload-zone block border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-2xl p-8 text-center cursor-pointer"
                           id="uploadZone">
                        <div id="uploadPrompt">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-3 bg-slate-100 dark:bg-slate-800">
                                <i class="fas fa-cloud-arrow-up text-2xl text-slate-400"></i>
                            </div>
                            <p class="text-sm font-medium text-slate-600 dark:text-slate-300">Drop your audio file here</p>
                            <p class="text-xs text-slate-400 mt-1">MP3 or WAV · click to browse</p>
                        </div>

                        <!-- File selected state (hidden by default) -->
                        <div id="uploadSelected" class="hidden flex items-center justify-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-cyan-100 dark:bg-cyan-500/10 flex-shrink-0">
                                <i class="fas fa-music text-cyan-500"></i>
                            </div>
                            <div class="text-left">
                                <p id="uploadFileName" class="text-sm font-medium text-slate-700 dark:text-slate-200"></p>
                                <p id="uploadFileSize" class="text-xs text-slate-400 mt-0.5"></p>
                            </div>
                        </div>

                        <!-- Hidden file input — name="audio_file" & id="audioFile" kept for original JS -->
                        <input type="file" name="audio_file" id="audioFile" accept="audio/*" class="hidden">
                    </label>

                    <div class="flex justify-end mt-3">
                        <button type="submit" id="submitBtnUpload"
                                class="send-btn h-9 px-4 rounded-xl text-xs font-semibold text-slate-900 flex items-center gap-1.5"
                                style="background-color:#22d3ee;" disabled>
                            Send <i class="fas fa-paper-plane text-xs"></i>
                        </button>
                    </div>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-2 text-right">Audio will be transcribed then converted to SQL</p>
                </div>

            </form>
        </div>

        <!-- ── Result Card ── -->
        <!-- ── Error Card ── -->
@if(session('error'))
    @php $err = session('error'); @endphp
    @if(!empty($err))
    <div class="bg-white dark:bg-slate-900 border border-red-200 dark:border-red-500/30 rounded-3xl shadow-sm px-6 py-6 fade-in">

        <div class="flex items-center gap-3 mb-5">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-red-100 dark:bg-red-500/10">
                <i class="fas fa-circle-exclamation text-red-500 text-sm"></i>
            </div>
            <div>
                <p class="text-sm font-semibold leading-none">Something went wrong</p>
                <p class="text-xs text-slate-400 mt-1">The AI could not process your request</p>
            </div>
        </div>

        <!-- User question if available -->
        @if(!empty($err['user_question']))
        <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3 mb-4">
            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Your question</p>
            <p class="text-sm text-slate-700 dark:text-slate-300">"{{ $err['user_question'] }}"</p>
        </div>
        @endif

        <!-- Error message -->
        <div class="flex items-start gap-3 bg-red-500/10 border border-red-500/20 text-red-400 px-5 py-4 rounded-2xl text-sm">
            <i class="fas fa-circle-xmark mt-0.5 flex-shrink-0"></i>
            <div>
                <p class="font-medium mb-0.5">Error</p>
                <p class="text-red-300">{{ $err['error'] ?? 'Unknown error' }}</p>
            </div>
        </div>

        <!-- SQL attempted if available -->
        @if(!empty($err['generated_sql']))
        <div class="mt-4">
            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-2">SQL attempted</p>
            <pre class="bg-slate-950 text-red-400 px-5 py-4 rounded-2xl border border-slate-800 overflow-x-auto text-xs font-mono">{{ $err['generated_sql'] }}</pre>
        </div>
        @endif

    </div>
    @endif
@endif

<!-- ── Success Card ── -->
@php $result = session('result'); @endphp
@if(!empty($result['success']))
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl shadow-sm px-6 py-6 fade-in">

        <div class="flex items-center gap-3 mb-5">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-emerald-100 dark:bg-emerald-500/10">
                <i class="fas fa-check text-emerald-500 text-sm"></i>
            </div>
            <div>
                <p class="text-sm font-semibold leading-none">Generated SQL</p>
                <p class="text-xs text-slate-400 mt-1">Based on your query</p>
            </div>
        </div>

        <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3 mb-4">
            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Your question</p>
            <p class="text-sm text-slate-700 dark:text-slate-300">"{{ $result['user_question'] ?? '' }}"</p>
        </div>

        <div class="relative">
            <span class="absolute top-3 right-3 z-10 text-xs font-semibold px-2 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-400 tracking-wider uppercase">SQL</span>
            <pre id="sqlQuery" class="bg-slate-950 text-emerald-400 px-5 pt-5 pb-5 rounded-2xl border border-slate-800 overflow-x-auto">{{ $result['generated_sql'] ?? '' }}</pre>
        </div>

        <div class="mt-5">
            <button id="executeSqlBtn"
                    class="flex items-center gap-2 px-5 py-2.5 rounded-2xl text-sm font-semibold text-white transition-all hover:opacity-90"
                    style="background:linear-gradient(135deg,#10b981,#059669);">
                <i class="fas fa-play text-xs"></i>
                Execute SQL
            </button>
        </div>

        <div id="executionResult" class="mt-5"></div>
    </div>
@endif

        <p class="mt-8 text-center text-slate-500 dark:text-slate-500 text-xs">
            Powered by AI · Results are auto-generated and may need review
        </p>

    </div>


    <!-- ═══════════════════════════════════════
         JAVASCRIPT
         All original logic preserved exactly.
         New: switchMode() manages mode cards
         and enables/disables send buttons.
    ════════════════════════════════════════ -->
    <script>

    // ── Theme ──────────────────────────────────────────────────────
    window.toggleTheme = function () {
        const html = document.documentElement;
        const icon = document.getElementById('themeIcon');
        if (html.classList.contains('dark')) {
            html.classList.remove('dark');
            icon.classList.replace('fa-moon', 'fa-sun');
            localStorage.setItem('theme', 'light');
        } else {
            html.classList.add('dark');
            icon.classList.replace('fa-sun', 'fa-moon');
            localStorage.setItem('theme', 'dark');
        }
    };
    (function () {
        const saved = localStorage.getItem('theme');
        if (saved === 'light') {
            document.documentElement.classList.remove('dark');
            const icon = document.getElementById('themeIcon');
            if (icon) icon.classList.replace('fa-moon', 'fa-sun');
        }
    })();


    // ── Mode switcher ──────────────────────────────────────────────
    let currentMode = 'text';

    window.switchMode = function (mode) {
        if (currentMode === mode) return;
        currentMode = mode;

        // Update hidden input so backend knows which mode was used
        document.getElementById('inputMode').value = mode;

        // Update card active states
        document.querySelectorAll('.mode-card').forEach(card => {
            const isActive = card.getAttribute('onclick') === `switchMode('${mode}')`;
            card.classList.toggle('active', isActive);
        });

        // Show correct input panel
        document.querySelectorAll('.input-panel').forEach(panel => {
            panel.classList.toggle('active', panel.id === `panel-${mode}`);
        });

        // Clear opposite inputs so backend only receives one
        if (mode !== 'text') {
            const qt = document.getElementById('queryText');
            if (qt) qt.value = '';
        }
        if (mode !== 'mic' && mode !== 'upload') {
            const af = document.getElementById('audioFile');
            if (af) af.value = '';
        }
    };


    // ── Schema panel ───────────────────────────────────────────────
    let schemaLoaded = false;
    let schemaOpen   = false;
    let schemaData   = [];

    window.toggleSchema = function () {
        schemaOpen = !schemaOpen;
        document.getElementById('schemaBody').classList.toggle('open', schemaOpen);
        document.getElementById('schemaChevron').classList.toggle('open', schemaOpen);
        document.getElementById('schemaSubtitle').textContent = schemaOpen
            ? 'Click to collapse'
            : 'Click to explore tables & columns';
        if (schemaOpen && !schemaLoaded) loadSchema();
    };

    async function loadSchema() {
        try {
            const res  = await fetch('/ai/analytics/schema');
            const data = await res.json();
            schemaData = data;

            document.getElementById('schemaLoading').classList.add('hidden');

            const badge = document.getElementById('tableCountBadge');
            badge.textContent = `${data.length} tables`;
            badge.classList.remove('hidden');

            renderTableChips(data);
            schemaLoaded = true;

            if (data.length) selectTable(data[0].name);

        } catch (e) {
            document.getElementById('schemaLoading').classList.add('hidden');
            document.getElementById('schemaError').classList.remove('hidden');
        }
    }

    function renderTableChips(tables) {
        const container = document.getElementById('tableChips');
        container.innerHTML = '';
        tables.forEach(t => {
            const chip = document.createElement('button');
            chip.type  = 'button';
            chip.dataset.table = t.name;
            chip.className = 'table-chip flex items-center gap-2 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-600 dark:text-slate-300';
            chip.innerHTML = `
                <i class="fas fa-table-cells text-slate-400" style="font-size:10px;"></i>
                <span class="chip-name font-medium">${t.name}</span>
                <span class="px-1.5 py-0.5 rounded-md text-slate-400 dark:text-slate-500" style="font-size:10px;background:rgba(100,116,139,0.1);">
                    ${Number(t.row_count || 0).toLocaleString()} rows
                </span>`;
            chip.addEventListener('click', () => selectTable(t.name));
            container.appendChild(chip);
        });
        container.classList.remove('hidden');
    }

    function selectTable(tableName) {
        document.querySelectorAll('.table-chip').forEach(c => {
            c.classList.toggle('active', c.dataset.table === tableName);
        });

        const table = schemaData.find(t => t.name === tableName);
        if (!table) return;

        document.getElementById('colDetailTitle').textContent    = `${table.name} · columns`;
        document.getElementById('colDetailRowCount').textContent = `${Number(table.row_count || 0).toLocaleString()} rows`;

        const colRows = document.getElementById('colRows');
        colRows.innerHTML = '';
        (table.columns || []).forEach(col => {
            const isKey = col.is_primary || col.key === 'PRI';
            const row   = document.createElement('div');
            row.className = 'col-row border-slate-100 dark:border-slate-700';
            row.innerHTML = `
                <span class="text-sm text-slate-700 dark:text-slate-300">${col.name}</span>
                <span class="text-xs font-medium px-2 py-0.5 rounded-lg ${
                    isKey
                    ? 'bg-cyan-100 dark:bg-cyan-500/10 text-cyan-600 dark:text-cyan-400'
                    : 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400'
                }">${col.type}${isKey ? ' · PK' : ''}</span>`;
            colRows.appendChild(row);
        });

        document.getElementById('columnDetail').classList.remove('hidden');
    }


    // ── Execute SQL (original logic — unchanged) ───────────────────
    const executeBtn = document.getElementById('executeSqlBtn');

    if (executeBtn) {
        executeBtn.addEventListener('click', async () => {
            const sql       = document.getElementById('sqlQuery').innerText.trim();
            const resultDiv = document.getElementById('executionResult');

            resultDiv.innerHTML = `
                <div class="flex items-center gap-3 text-cyan-400 text-sm py-3">
                    <i class="fas fa-circle-notch fa-spin"></i>
                    <span>Executing query…</span>
                </div>`;

            try {
                const res = await fetch('/ai/execute-sql', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ sql })
                });

                const data = await res.json();

                if (!res.ok) {
                    resultDiv.innerHTML = `
                        <div class="flex items-center gap-3 bg-red-500/10 border border-red-500/20 text-red-400 px-5 py-4 rounded-2xl text-sm">
                            <i class="fas fa-circle-xmark"></i>
                            ${data.error || 'SQL execution failed'}
                        </div>`;
                    return;
                }

                renderTable(data.data || [], resultDiv);

            } catch (err) {
                resultDiv.innerHTML = `
                    <div class="flex items-center gap-3 bg-red-500/10 border border-red-500/20 text-red-400 px-5 py-4 rounded-2xl text-sm">
                        <i class="fas fa-circle-xmark"></i>
                        Network error
                    </div>`;
            }
        });
    }

    function renderTable(rows, container) {
        if (!rows.length) {
            container.innerHTML = `<div class="text-center text-slate-400 py-8 text-sm">No data returned</div>`;
            return;
        }
        const columns = Object.keys(rows[0]);
        let html = `
            <div class="overflow-x-auto mt-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <table class="min-w-full text-sm text-left">
                    <thead class="bg-slate-50 dark:bg-slate-800 sticky top-0">
                        <tr>${columns.map(col => `
                            <th class="px-5 py-3 font-semibold text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                ${col.replace(/_/g, ' ')}
                            </th>`).join('')}
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">`;

        rows.forEach((row, index) => {
            html += `<tr class="${index % 2 === 0
                ? 'bg-white dark:bg-slate-900'
                : 'bg-slate-50 dark:bg-slate-800/50'} hover:bg-cyan-50 dark:hover:bg-slate-700/50 transition-colors">`;
            columns.forEach(col => {
                html += `<td class="px-5 py-3 text-slate-700 dark:text-slate-300 whitespace-nowrap">${formatCell(row[col])}</td>`;
            });
            html += `</tr>`;
        });

        html += `</tbody></table></div>`;
        container.innerHTML = html;
    }

    function formatCell(value) {
        if (value === null) return `<span class="text-slate-400 italic">null</span>`;
        if (typeof value === 'string' && /\d{4}-\d{2}-\d{2}/.test(value)) {
            return `<span class="text-cyan-500">${value}</span>`;
        }
        return value;
    }


    // ── Recording + form submit (original logic — unchanged) ───────
    document.addEventListener('DOMContentLoaded', function () {

        const recordBtn       = document.getElementById('recordBtn');
        const recordingStatus = document.getElementById('recordingStatus');
        const voiceForm       = document.getElementById('voiceForm');
        const audioFileInput  = document.getElementById('audioFile');
        const fileNameDisplay = document.getElementById('fileNameDisplay');
        const submitBtnMic    = document.getElementById('submitBtnMic');
        const submitBtnUpload = document.getElementById('submitBtnUpload');
        const submitBtn       = document.getElementById('submitBtn');

        let mediaRecorder;
        let audioChunks = [];
        let isRecording = false;

        // ── Upload file selection ──
        if (audioFileInput) {
            audioFileInput.addEventListener('change', () => {
                if (!audioFileInput.files.length) return;

                const file    = audioFileInput.files[0];
                const sizeKB  = (file.size / 1024).toFixed(0);

                // Update upload zone UI
                document.getElementById('uploadPrompt').classList.add('hidden');
                document.getElementById('uploadSelected').classList.remove('hidden');
                document.getElementById('uploadFileName').textContent = file.name;
                document.getElementById('uploadFileSize').textContent = `${sizeKB} KB · ready to send`;
                document.getElementById('uploadZone').classList.add('has-file');

                // Enable upload send button
                if (submitBtnUpload) {
                    submitBtnUpload.disabled = false;
                }
            });
        }

        // ── Recording ── (original logic, unchanged)
        if (!recordBtn) return;

        recordBtn.addEventListener('click', async () => {
            if (!isRecording) {
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    mediaRecorder = new MediaRecorder(stream);
                    audioChunks   = [];

                    mediaRecorder.ondataavailable = e => audioChunks.push(e.data);

                    mediaRecorder.onstop = () => {
                        const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                        const file      = new File([audioBlob], 'voice-query.webm', { type: 'audio/webm' });

                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        audioFileInput.files = dataTransfer.files;

                        recordingStatus.classList.add('hidden');
                        recordBtn.classList.remove('recording');
                        recordBtn.innerHTML = '<i class="fas fa-check text-emerald-500 text-2xl"></i>';

                        if (fileNameDisplay) {
                            fileNameDisplay.textContent = '🎙️ voice-query.webm · ready to send';
                            fileNameDisplay.classList.remove('hidden');
                        }

                        // Enable mic send button once recording is done
                        if (submitBtnMic) {
                            submitBtnMic.disabled = false;
                        }
                    };

                    mediaRecorder.start();
                    isRecording = true;
                    recordingStatus.classList.remove('hidden');
                    recordBtn.classList.add('recording');
                    recordBtn.innerHTML = '<i class="fas fa-stop text-2xl"></i>';

                } catch (err) {
                    alert('Microphone access denied.');
                }
            } else {
                if (mediaRecorder) mediaRecorder.stop();
                isRecording = false;
            }
        });

        // ── Form submit loading state ── (original logic, unchanged)
        voiceForm.addEventListener('submit', () => {
            // Disable whichever send button is visible
            [submitBtn, submitBtnMic, submitBtnUpload].forEach(btn => {
                if (btn && !btn.disabled) {
                    btn.disabled    = true;
                    btn.innerHTML   = '<i class="fas fa-circle-notch fa-spin mr-1.5"></i> Processing…';
                    btn.style.opacity = '0.75';
                }
            });
        });

    });

    </script>

</body>
</html>