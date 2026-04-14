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

        /* Schema panel slide */
        .schema-body {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .schema-body.open { max-height: 600px; }

        .chevron-icon { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .chevron-icon.open { transform: rotate(180deg); }

        /* Table chips */
        .table-chip { transition: all 0.18s; cursor: pointer; }
        .table-chip:hover { border-color: #22d3ee !important; }
        .table-chip.active { border-color: #22d3ee !important; background-color: rgba(34,211,238,0.06) !important; }
        .table-chip.active .chip-name { color: #22d3ee !important; }

        /* Composer focus ring */
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

        /* Mic recording state */
        .mic-btn { transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); }
        .mic-btn.recording {
            animation: pulse-rec 1.5s infinite;
            background-color: #ef4444 !important;
            border-color: #ef4444 !important;
            color: #fff !important;
        }
        @keyframes pulse-rec {
            0%,100% { box-shadow: 0 0 0 0 rgba(239,68,68,0.4); }
            50%      { box-shadow: 0 0 0 8px rgba(239,68,68,0); }
        }

        /* Fade in */
        .fade-in { animation: fadeIn 0.4s cubic-bezier(0.4,0,0.2,1); }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* SQL block font */
        pre#sqlQuery {
            font-family: 'JetBrains Mono','Fira Code','Courier New',monospace;
            font-size: 0.82rem;
            line-height: 1.8;
            white-space: pre-wrap;
            word-break: break-word;
        }

        /* Column rows inside schema */
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

                    <!-- Loading -->
                    <div id="schemaLoading" class="flex items-center gap-2 text-slate-400 text-sm py-2">
                        <i class="fas fa-circle-notch fa-spin text-cyan-400"></i>
                        Loading schema…
                    </div>

                    <!-- Table chips -->
                    <div id="tableChips" class="hidden flex flex-wrap gap-2 mb-4"></div>

                    <!-- Column detail -->
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

                    <!-- Error -->
                    <div id="schemaError" class="hidden text-sm text-red-400 py-2 flex items-center gap-2">
                        <i class="fas fa-circle-exclamation"></i>
                        Could not load schema. Check the /ai/analytics/schema endpoint.
                    </div>

                </div>
            </div>
        </div>

        <!-- ── Input Card ── -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl shadow-sm px-6 py-5 mb-4">

            <form action="{{ route('ai-sql-assitance') }}" method="POST" enctype="multipart/form-data" id="voiceForm">
                @csrf
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Your query</p>

                <!-- Unified composer bar -->
                <div class="composer-box flex items-end gap-3 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 py-3">

                    <textarea id="queryText"
                              name="text_query"
                              rows="2"
                              placeholder="e.g. Show total tokens used by each user last month…"
                              class="flex-1 text-sm text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 leading-relaxed">{{ old('text_query') }}</textarea>

                    <div class="flex items-center gap-2 pb-0.5 flex-shrink-0">

                        <!-- Mic button — id & class kept for JS -->
                        <button type="button" id="recordBtn"
                                title="Record voice"
                                class="mic-btn w-9 h-9 flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:border-cyan-400 hover:text-cyan-500 transition-all">
                            <i class="fas fa-microphone text-sm"></i>
                        </button>

                        <!-- Upload label — keeps hidden input with id="audioFile" -->
                        <label title="Upload audio file"
                               class="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:border-cyan-400 hover:text-cyan-500 transition-all cursor-pointer">
                            <i class="fas fa-arrow-up-from-bracket text-sm"></i>
                            <input type="file" name="audio_file" id="audioFile" accept="audio/*" class="hidden">
                        </label>

                        <!-- Submit — id kept for JS -->
                        <button type="submit" id="submitBtn"
                                class="h-9 px-4 rounded-xl text-xs font-semibold text-slate-900 transition-all hover:opacity-90 flex items-center gap-1.5"
                                style="background-color:#22d3ee;">
                            Send <i class="fas fa-paper-plane text-xs"></i>
                        </button>

                    </div>
                </div>

                <!-- Status row -->
                <div class="flex items-center justify-between mt-2 min-h-[18px]">
                    <div class="flex items-center gap-3">
                        <!-- id="recordingStatus" kept for JS -->
                        <p id="recordingStatus" class="hidden text-xs text-red-400 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse inline-block"></span>
                            Recording…
                        </p>
                        <!-- id="fileNameDisplay" kept for JS -->
                        <p id="fileNameDisplay" class="hidden text-xs text-slate-400"></p>
                    </div>
                    <p class="text-xs text-slate-400 dark:text-slate-500">Type · mic · or upload audio</p>
                </div>

            </form>
        </div>

        <!-- ── Result Card ── -->
        @if(session('result'))
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

            <!-- User question -->
            <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3 mb-4">
                <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Your question</p>
                <p class="text-sm text-slate-700 dark:text-slate-300">"{{ session('result')['user_question'] }}"</p>
            </div>

            <!-- SQL block — id="sqlQuery" kept for JS -->
            <div class="relative">
                <span class="absolute top-3 right-3 z-10 text-xs font-semibold px-2 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-400 tracking-wider uppercase">SQL</span>
                <pre id="sqlQuery" class="bg-slate-950 text-emerald-400 px-5 pt-5 pb-5 rounded-2xl border border-slate-800 overflow-x-auto">{{ session('result')['generated_sql'] }}</pre>
            </div>

            <!-- Execute button — id="executeSqlBtn" kept for JS -->
            <div class="mt-5">
                <button id="executeSqlBtn"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-2xl text-sm font-semibold text-white transition-all hover:opacity-90"
                        style="background:linear-gradient(135deg,#10b981,#059669);">
                    <i class="fas fa-play text-xs"></i>
                    Execute SQL
                </button>
            </div>

            <!-- Execution result — id="executionResult" kept for JS -->
            <div id="executionResult" class="mt-5"></div>

        </div>
        @endif

        <p class="mt-8 text-center text-slate-500 dark:text-slate-500 text-xs">
            Powered by AI · Results are auto-generated and may need review
        </p>

    </div>


    <!-- ═══════════════ JAVASCRIPT ═══════════════ -->
    <script>

    // ── Theme ──────────────────────────────────────────────────────
    function toggleTheme() {
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
    }
    (function () {
        const saved = localStorage.getItem('theme');
        if (saved === 'light') {
            document.documentElement.classList.remove('dark');
            const icon = document.getElementById('themeIcon');
            if (icon) icon.classList.replace('fa-moon', 'fa-sun');
        }
    })();


    // ── Schema panel ───────────────────────────────────────────────
    let schemaLoaded = false;
    let schemaOpen   = false;
    let schemaData   = [];
    // Expected API shape from /ai/analytics/schema:
    // [ { name: "users", row_count: 1200, columns: [ { name: "id", type: "INT", is_primary: true }, … ] } ]

    function toggleSchema() {
        schemaOpen = !schemaOpen;
        document.getElementById('schemaBody').classList.toggle('open', schemaOpen);
        document.getElementById('schemaChevron').classList.toggle('open', schemaOpen);
        document.getElementById('schemaSubtitle').textContent = schemaOpen
            ? 'Click to collapse'
            : 'Click to explore tables & columns';

        if (schemaOpen && !schemaLoaded) loadSchema();
    }

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
        const submitBtn       = document.getElementById('submitBtn');
        const voiceForm       = document.getElementById('voiceForm');
        const audioFileInput  = document.getElementById('audioFile');
        const fileNameDisplay = document.getElementById('fileNameDisplay');

        let mediaRecorder;
        let audioChunks = [];
        let isRecording = false;

        if (audioFileInput && fileNameDisplay) {
            audioFileInput.addEventListener('change', () => {
                if (audioFileInput.files.length) {
                    fileNameDisplay.textContent = '📎 ' + audioFileInput.files[0].name;
                    fileNameDisplay.classList.remove('hidden');
                }
            });
        }

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
                        recordBtn.innerHTML = '<i class="fas fa-check text-emerald-500 text-sm"></i>';

                        fileNameDisplay.textContent = '🎙️ voice-query.webm';
                        fileNameDisplay.classList.remove('hidden');
                    };

                    mediaRecorder.start();
                    isRecording = true;
                    recordingStatus.classList.remove('hidden');
                    recordBtn.classList.add('recording');
                    recordBtn.innerHTML = '<i class="fas fa-stop text-sm"></i>';

                } catch (err) {
                    alert('Microphone access denied.');
                }
            } else {
                if (mediaRecorder) mediaRecorder.stop();
                isRecording = false;
            }
        });

        voiceForm.addEventListener('submit', () => {
            submitBtn.disabled      = true;
            submitBtn.innerHTML     = '<i class="fas fa-circle-notch fa-spin mr-1.5"></i> Processing…';
            submitBtn.style.opacity = '0.75';
        });

    });

    </script>

</body>
</html>