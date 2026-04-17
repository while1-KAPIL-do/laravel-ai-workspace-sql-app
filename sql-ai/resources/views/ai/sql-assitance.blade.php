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

        /* ── AI Selector dropdown animation ── */
        .ai-dropdown {
            transform-origin: top left;
            transition: opacity 0.18s ease, transform 0.18s cubic-bezier(0.4,0,0.2,1);
        }
        .ai-dropdown.hidden {
            opacity: 0;
            transform: scale(0.96) translateY(-4px);
            pointer-events: none;
        }
        .ai-dropdown:not(.hidden) {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
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

    <div class="schema-body" id="schemaBody">
        <div class="border-t border-slate-200 dark:border-slate-700 px-6 py-5">
            <div id="schemaLoading" class="flex items-center gap-2 text-slate-400 text-sm py-2">
                <i class="fas fa-circle-notch fa-spin text-cyan-400"></i> Loading schema…
            </div>
            <div id="tableChips" class="hidden flex flex-wrap gap-2 mb-4"></div>
            <div id="columnDetail" class="hidden">
                <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-2xl p-4">
                    <div class="flex items-center justify-between mb-3">
                        <p id="colDetailTitle" class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"></p>
                        <span id="colDetailRowCount" class="text-xs font-medium px-2 py-0.5 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300"></span>
                    </div>
                    <div id="colRows"></div>
                </div>
            </div>
            <div id="schemaError" class="hidden text-sm text-red-400 py-2 flex items-center gap-2">
                <i class="fas fa-circle-exclamation"></i> Could not load schema.
            </div>
        </div>
    </div>
</div>

        <!-- ── Input Card ── -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl shadow-sm px-6 py-6 mb-4">

            <form action="{{ route('ai-sql-assitance') }}" method="POST" enctype="multipart/form-data" id="voiceForm">
                @csrf

                <!-- ══════════════════════════════════════════════════════
                     SINGLE SOURCE OF TRUTH — only these are ever submitted.
                     All 3 panel selectors update these on every change.
                ═══════════════════════════════════════════════════════ -->
                <input type="hidden" name="input_mode" id="inputMode"         value="text">
                <input type="hidden" name="provider"   id="globalProvider"    value="openai">
                <input type="hidden" name="model"      id="globalModel"       value="">

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

                    <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-2xl p-4 mb-4 composer-box transition-colors duration-200">
                        <textarea name="text_query" id="queryText" rows="3"
                                placeholder="e.g., Show me all active users who joined in the last 30 days..."
                                class="w-full bg-transparent border-none outline-none text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 resize-none font-sans"></textarea>
                    </div>

                    <!-- Toolbar: selectors + send -->
                    <div class="flex items-center gap-2 pt-2 border-t border-slate-200 dark:border-slate-700 flex-wrap">
                        <!-- Provider -->
                        <div class="relative" id="pw-text">
                            <button type="button" onclick="aiToggle('p','text')" id="pb-text"
                                    class="ai-trigger flex items-center gap-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 hover:border-cyan-400 dark:hover:border-cyan-500 rounded-xl px-3 h-10 transition-all focus:outline-none focus:ring-2 focus:ring-cyan-500/20">
                                <i class="fas fa-microchip text-slate-400 dark:text-slate-500 text-xs"></i>
                                <span class="provider-label text-sm font-semibold text-slate-700 dark:text-slate-200">OpenAI</span>
                                <i class="fas fa-chevron-down text-slate-400 text-xs ai-chevron transition-transform duration-200"></i>
                            </button>
                            <ul class="ai-dropdown hidden absolute left-0 top-full mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl z-50 py-1.5 min-w-[160px] overflow-hidden" id="pd-text"></ul>
                        </div>

                        <!-- Model -->
                        <div class="relative" id="mw-text">
                            <button type="button" onclick="aiToggle('m','text')" id="mb-text"
                                    class="ai-trigger flex items-center gap-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 hover:border-cyan-400 dark:hover:border-cyan-500 rounded-xl px-3 h-10 transition-all focus:outline-none focus:ring-2 focus:ring-cyan-500/20">
                                <i class="fas fa-cube text-slate-400 dark:text-slate-500 text-xs"></i>
                                <span class="model-label text-sm font-semibold text-slate-700 dark:text-slate-200">Select model</span>
                                <i class="fas fa-chevron-down text-slate-400 text-xs ai-chevron transition-transform duration-200"></i>
                            </button>
                            <ul class="ai-dropdown hidden absolute left-0 top-full mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl z-50 py-1.5 min-w-[250px] max-h-60 overflow-y-auto" id="md-text"></ul>
                        </div>

                        <div class="flex-1"></div>

                        <button type="submit" id="submitBtn"
                                class="send-btn h-10 px-5 rounded-xl text-sm font-semibold text-slate-900 flex items-center gap-2 hover:opacity-90 transition-opacity"
                                style="background-color:#22d3ee;">
                            Send <i class="fas fa-paper-plane text-xs"></i>
                        </button>
                    </div>
                </div>


                <!-- ── Panel: MIC ── -->
                <div class="input-panel" id="panel-mic">
                    <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-2xl px-6 py-6">
                        <div class="flex flex-col items-center gap-4">
                            <button type="button" id="recordBtn"
                                    class="mic-btn w-16 h-16 flex items-center justify-center rounded-2xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:border-cyan-400 hover:text-cyan-500 transition-all">
                                <i class="fas fa-microphone text-2xl"></i>
                            </button>
                            <p id="recordingStatus" class="hidden text-sm text-red-400 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse inline-block"></span>
                                Recording in progress…
                            </p>
                            <p class="text-xs text-slate-400 dark:text-slate-500">Click to start · click again to stop</p>
                        </div>

                        <p id="fileNameDisplay" class="hidden text-xs text-slate-400 mt-4 text-center"></p>

                        <!-- Toolbar: selectors + send -->
                        <div class="flex items-center gap-2 mt-5 pt-4 border-t border-slate-200 dark:border-slate-700 flex-wrap">
                            <!-- Provider -->
                            <div class="relative" id="pw-mic">
                                <button type="button" onclick="aiToggle('p','mic')" id="pb-mic"
                                        class="ai-trigger flex items-center gap-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 hover:border-cyan-400 dark:hover:border-cyan-500 rounded-xl px-3 h-10 transition-all focus:outline-none focus:ring-2 focus:ring-cyan-500/20">
                                    <i class="fas fa-microchip text-slate-400 dark:text-slate-500 text-xs"></i>
                                    <span class="provider-label text-sm font-semibold text-slate-700 dark:text-slate-200">OpenAI</span>
                                    <i class="fas fa-chevron-down text-slate-400 text-xs ai-chevron transition-transform duration-200"></i>
                                </button>
                                <ul class="ai-dropdown hidden absolute left-0 top-full mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl z-50 py-1.5 min-w-[160px] overflow-hidden" id="pd-mic"></ul>
                            </div>

                            <!-- Model -->
                            <div class="relative" id="mw-mic">
                                <button type="button" onclick="aiToggle('m','mic')" id="mb-mic"
                                        class="ai-trigger flex items-center gap-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 hover:border-cyan-400 dark:hover:border-cyan-500 rounded-xl px-3 h-10 transition-all focus:outline-none focus:ring-2 focus:ring-cyan-500/20">
                                    <i class="fas fa-cube text-slate-400 dark:text-slate-500 text-xs"></i>
                                    <span class="model-label text-sm font-semibold text-slate-700 dark:text-slate-200">Select model</span>
                                    <i class="fas fa-chevron-down text-slate-400 text-xs ai-chevron transition-transform duration-200"></i>
                                </button>
                                <ul class="ai-dropdown hidden absolute left-0 top-full mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl z-50 py-1.5 min-w-[250px] max-h-60 overflow-y-auto" id="md-mic"></ul>
                            </div>

                            <div class="flex-1"></div>

                            <button type="submit" id="submitBtnMic"
                                    class="send-btn h-10 px-5 rounded-xl text-sm font-semibold text-slate-900 flex items-center gap-2"
                                    style="background-color:#22d3ee;" disabled>
                                Send <i class="fas fa-paper-plane text-xs"></i>
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-2 text-right">Recording will be transcribed then converted to SQL</p>
                </div>


                <!-- ── Panel: UPLOAD ── -->
                <div class="input-panel" id="panel-upload">
                    <label class="upload-zone block border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-2xl p-8 text-center cursor-pointer" id="uploadZone">
                        <div id="uploadPrompt">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-3 bg-slate-100 dark:bg-slate-800">
                                <i class="fas fa-cloud-arrow-up text-2xl text-slate-400"></i>
                            </div>
                            <p class="text-sm font-medium text-slate-600 dark:text-slate-300">Drop your audio file here</p>
                            <p class="text-xs text-slate-400 mt-1">MP3 or WAV · click to browse</p>
                        </div>

                        <div id="uploadSelected" class="hidden flex items-center justify-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-cyan-100 dark:bg-cyan-500/10 flex-shrink-0">
                                <i class="fas fa-music text-cyan-500"></i>
                            </div>
                            <div class="text-left">
                                <p id="uploadFileName" class="text-sm font-medium text-slate-700 dark:text-slate-200"></p>
                                <p id="uploadFileSize" class="text-xs text-slate-400 mt-0.5"></p>
                            </div>
                        </div>

                        <input type="file" name="audio_file" id="audioFile" accept="audio/*" class="hidden">
                    </label>

                    <!-- Toolbar: selectors + send -->
                    <div class="flex items-center gap-2 mt-3 pt-3 border-t border-slate-200 dark:border-slate-700 flex-wrap">
                        <!-- Provider -->
                        <div class="relative" id="pw-upload">
                            <button type="button" onclick="aiToggle('p','upload')" id="pb-upload"
                                    class="ai-trigger flex items-center gap-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 hover:border-cyan-400 dark:hover:border-cyan-500 rounded-xl px-3 h-10 transition-all focus:outline-none focus:ring-2 focus:ring-cyan-500/20">
                                <i class="fas fa-microchip text-slate-400 dark:text-slate-500 text-xs"></i>
                                <span class="provider-label text-sm font-semibold text-slate-700 dark:text-slate-200">OpenAI</span>
                                <i class="fas fa-chevron-down text-slate-400 text-xs ai-chevron transition-transform duration-200"></i>
                            </button>
                            <ul class="ai-dropdown hidden absolute left-0 top-full mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl z-50 py-1.5 min-w-[160px] overflow-hidden" id="pd-upload"></ul>
                        </div>

                        <!-- Model -->
                        <div class="relative" id="mw-upload">
                            <button type="button" onclick="aiToggle('m','upload')" id="mb-upload"
                                    class="ai-trigger flex items-center gap-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 hover:border-cyan-400 dark:hover:border-cyan-500 rounded-xl px-3 h-10 transition-all focus:outline-none focus:ring-2 focus:ring-cyan-500/20">
                                <i class="fas fa-cube text-slate-400 dark:text-slate-500 text-xs"></i>
                                <span class="model-label text-sm font-semibold text-slate-700 dark:text-slate-200">Select model</span>
                                <i class="fas fa-chevron-down text-slate-400 text-xs ai-chevron transition-transform duration-200"></i>
                            </button>
                            <ul class="ai-dropdown hidden absolute left-0 top-full mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl z-50 py-1.5 min-w-[250px] max-h-60 overflow-y-auto" id="md-upload"></ul>
                        </div>

                        <div class="flex-1"></div>

                        <button type="submit" id="submitBtnUpload"
                                class="send-btn h-10 px-5 rounded-xl text-sm font-semibold text-slate-900 flex items-center gap-2"
                                style="background-color:#22d3ee;" disabled>
                            Send <i class="fas fa-paper-plane text-xs"></i>
                        </button>
                    </div>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-2 text-right">Audio will be transcribed then converted to SQL</p>
                </div>

            </form>
        </div>

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
                @if(!empty($err['user_question']))
                <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3 mb-4">
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Your question</p>
                    <p class="text-sm text-slate-700 dark:text-slate-300">"{{ $err['user_question'] }}"</p>
                </div>
                @endif
                <div class="flex items-start gap-3 bg-red-500/10 border border-red-500/20 text-red-400 px-5 py-4 rounded-2xl text-sm">
                    <i class="fas fa-circle-xmark mt-0.5 flex-shrink-0"></i>
                    <div>
                        <p class="font-medium mb-0.5">Error</p>
                        <p class="text-red-300">{{ $err['error'] ?? 'Unknown error' }}</p>
                    </div>
                </div>
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
        document.getElementById('inputMode').value = mode;
        document.querySelectorAll('.mode-card').forEach(card => {
            card.classList.toggle('active', card.getAttribute('onclick') === `switchMode('${mode}')`);
        });
        document.querySelectorAll('.input-panel').forEach(panel => {
            panel.classList.toggle('active', panel.id === `panel-${mode}`);
        });
        if (mode !== 'text') { const qt = document.getElementById('queryText'); if (qt) qt.value = ''; }
        if (mode !== 'mic' && mode !== 'upload') { const af = document.getElementById('audioFile'); if (af) af.value = ''; }
    };


    // ── Schema panel ───────────────────────────────────────────────
    let schemaLoaded = false, schemaOpen = false, schemaData = [];

    window.toggleSchema = function () {
        schemaOpen = !schemaOpen;
        document.getElementById('schemaBody').classList.toggle('open', schemaOpen);
        document.getElementById('schemaChevron').classList.toggle('open', schemaOpen);
        document.getElementById('schemaSubtitle').textContent = schemaOpen ? 'Click to collapse' : 'Click to explore tables & columns';
        if (schemaOpen && !schemaLoaded) loadSchema();
    };

    async function loadSchema() {
        try {
            const res  = await fetch('/schema/analytics/schema');
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
            chip.type = 'button';
            chip.dataset.table = t.name;
            chip.className = 'table-chip flex items-center gap-2 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-600 dark:text-slate-300';
            chip.innerHTML = `<i class="fas fa-table-cells text-slate-400" style="font-size:10px;"></i><span class="chip-name font-medium">${t.name}</span>`;
            chip.addEventListener('click', () => selectTable(t.name));
            container.appendChild(chip);
        });
        container.classList.remove('hidden');
    }

    function selectTable(tableName) {
        document.querySelectorAll('.table-chip').forEach(c => c.classList.toggle('active', c.dataset.table === tableName));
        const table = schemaData.find(t => t.name === tableName);
        if (!table) return;
        document.getElementById('colDetailTitle').textContent    = `${table.name} · columns`;
        document.getElementById('colDetailRowCount').textContent = '';
        const colRows = document.getElementById('colRows');
        colRows.innerHTML = '';
        (table.columns || []).forEach(col => {
            const isKey = col.is_primary || col.key === 'PRI';
            const row = document.createElement('div');
            row.className = 'col-row border-slate-100 dark:border-slate-700';
            row.innerHTML = `<span class="text-sm text-slate-700 dark:text-slate-300">${col.name}</span><span class="text-xs font-medium px-2 py-0.5 rounded-lg ${isKey ? 'bg-cyan-100 dark:bg-cyan-500/10 text-cyan-600 dark:text-cyan-400' : 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400'}">${col.type}${isKey ? ' · PK' : ''}</span>`;
            colRows.appendChild(row);
        });
        document.getElementById('columnDetail').classList.remove('hidden');
    }


    // ── Execute SQL ────────────────────────────────────────────────
    const executeBtn = document.getElementById('executeSqlBtn');
    if (executeBtn) {
        executeBtn.addEventListener('click', async () => {
            const sql = document.getElementById('sqlQuery').innerText.trim();
            const resultDiv = document.getElementById('executionResult');
            resultDiv.innerHTML = `<div class="flex items-center gap-3 text-cyan-400 text-sm py-3"><i class="fas fa-circle-notch fa-spin"></i><span>Executing query…</span></div>`;
            try {
                const res  = await fetch('/schema/execute-sql', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ sql }) });
                const data = await res.json();
                if (!res.ok) { resultDiv.innerHTML = `<div class="flex items-center gap-3 bg-red-500/10 border border-red-500/20 text-red-400 px-5 py-4 rounded-2xl text-sm"><i class="fas fa-circle-xmark"></i>${data.error || 'SQL execution failed'}</div>`; return; }
                renderTable(data.data || [], resultDiv);
            } catch (err) {
                resultDiv.innerHTML = `<div class="flex items-center gap-3 bg-red-500/10 border border-red-500/20 text-red-400 px-5 py-4 rounded-2xl text-sm"><i class="fas fa-circle-xmark"></i>Network error</div>`;
            }
        });
    }

    function renderTable(rows, container) {
        if (!rows.length) { container.innerHTML = `<div class="text-center text-slate-400 py-8 text-sm">No data returned</div>`; return; }
        const columns = Object.keys(rows[0]);
        let html = `<div class="overflow-x-auto mt-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm"><table class="min-w-full text-sm text-left"><thead class="bg-slate-50 dark:bg-slate-800 sticky top-0"><tr>${columns.map(col => `<th class="px-5 py-3 font-semibold text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">${col.replace(/_/g, ' ')}</th>`).join('')}</tr></thead><tbody class="divide-y divide-slate-100 dark:divide-slate-800">`;
        rows.forEach((row, i) => {
            html += `<tr class="${i % 2 === 0 ? 'bg-white dark:bg-slate-900' : 'bg-slate-50 dark:bg-slate-800/50'} hover:bg-cyan-50 dark:hover:bg-slate-700/50 transition-colors">`;
            columns.forEach(col => { html += `<td class="px-5 py-3 text-slate-700 dark:text-slate-300 whitespace-nowrap">${formatCell(row[col])}</td>`; });
            html += `</tr>`;
        });
        html += `</tbody></table></div>`;
        container.innerHTML = html;
    }

    function formatCell(value) {
        if (value === null) return `<span class="text-slate-400 italic">null</span>`;
        if (typeof value === 'string' && /\d{4}-\d{2}-\d{2}/.test(value)) return `<span class="text-cyan-500">${value}</span>`;
        return value;
    }


    // ── Recording + form submit ────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        const recordBtn       = document.getElementById('recordBtn');
        const recordingStatus = document.getElementById('recordingStatus');
        const voiceForm       = document.getElementById('voiceForm');
        const audioFileInput  = document.getElementById('audioFile');
        const fileNameDisplay = document.getElementById('fileNameDisplay');
        const submitBtnMic    = document.getElementById('submitBtnMic');
        const submitBtnUpload = document.getElementById('submitBtnUpload');
        const submitBtn       = document.getElementById('submitBtn');

        let mediaRecorder, audioChunks = [], isRecording = false;

        if (audioFileInput) {
            audioFileInput.addEventListener('change', () => {
                if (!audioFileInput.files.length) return;
                const file = audioFileInput.files[0];
                document.getElementById('uploadPrompt').classList.add('hidden');
                document.getElementById('uploadSelected').classList.remove('hidden');
                document.getElementById('uploadFileName').textContent = file.name;
                document.getElementById('uploadFileSize').textContent = `${(file.size / 1024).toFixed(0)} KB · ready to send`;
                document.getElementById('uploadZone').classList.add('has-file');
                if (submitBtnUpload) submitBtnUpload.disabled = false;
            });
        }

        if (!recordBtn) return;

        recordBtn.addEventListener('click', async () => {
            if (!isRecording) {
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    mediaRecorder = new MediaRecorder(stream);
                    audioChunks = [];
                    mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
                    mediaRecorder.onstop = () => {
                        const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                        const file = new File([audioBlob], 'voice-query.webm', { type: 'audio/webm' });
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        audioFileInput.files = dt.files;
                        recordingStatus.classList.add('hidden');
                        recordBtn.classList.remove('recording');
                        recordBtn.innerHTML = '<i class="fas fa-check text-emerald-500 text-2xl"></i>';
                        if (fileNameDisplay) { fileNameDisplay.textContent = '🎙️ voice-query.webm · ready to send'; fileNameDisplay.classList.remove('hidden'); }
                        if (submitBtnMic) submitBtnMic.disabled = false;
                    };
                    mediaRecorder.start();
                    isRecording = true;
                    recordingStatus.classList.remove('hidden');
                    recordBtn.classList.add('recording');
                    recordBtn.innerHTML = '<i class="fas fa-stop text-2xl"></i>';
                } catch (err) { alert('Microphone access denied.'); }
            } else {
                if (mediaRecorder) mediaRecorder.stop();
                isRecording = false;
            }
        });

        voiceForm.addEventListener('submit', () => {
            [submitBtn, submitBtnMic, submitBtnUpload].forEach(btn => {
                if (btn && !btn.disabled) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-1.5"></i> Processing…'; btn.style.opacity = '0.75'; }
            });
        });
    });


    // ══════════════════════════════════════════════════════════════
    // AI PROVIDER + MODEL SELECTOR
    // ══════════════════════════════════════════════════════════════

    // Provider data from Laravel controller
    const AI_DATA = @json($aiProviders);

    const PANELS = ['text', 'mic', 'upload'];

    // Global state — single source of truth
    let activeProvider = Object.keys(AI_DATA)[0] || 'openai';
    let activeModel    = '';

    // ── Sync the two global hidden inputs (what gets POSTed) ───────
    function syncHidden() {
        document.getElementById('globalProvider').value = activeProvider;
        document.getElementById('globalModel').value    = activeModel;
    }

    // ── Update every panel's button labels simultaneously ──────────
    function syncLabels() {
        document.querySelectorAll('.provider-label').forEach(el => {
            el.textContent = capitalize(activeProvider);
        });
        document.querySelectorAll('.model-label').forEach(el => {
            el.textContent = activeModel || 'Select model';
        });
    }

    // ── Rebuild model dropdown list for every panel ────────────────
    function rebuildModelDropdowns() {
        const models = AI_DATA[activeProvider] || {};

        // Handle both array ['gpt-4o-mini', ...] and object {'gpt-4o-mini': {badge:...}}
        const modelEntries = Array.isArray(models)
            ? models.map(m => [m, {}])           // plain array → no badge
            : Object.entries(models);            // object → with badge

        PANELS.forEach(panel => {
            const ul = document.getElementById(`md-${panel}`);
            if (!ul) return;
            ul.innerHTML = '';

            modelEntries.forEach(([modelId, meta]) => {

                console.log(modelId, meta, meta.badge)

                const badge      = (meta && meta.badge) ? meta.badge : '';
                const isSelected = modelId === activeModel;
                const li = document.createElement('li');
                li.className = [
                    'flex items-center justify-between px-4 py-2.5 cursor-pointer transition-colors text-sm',
                    'hover:bg-cyan-50 dark:hover:bg-cyan-500/10 hover:text-cyan-600 dark:hover:text-cyan-400',
                    isSelected
                        ? 'text-cyan-600 dark:text-cyan-400 bg-cyan-50 dark:bg-cyan-500/10 font-semibold'
                        : 'text-slate-700 dark:text-slate-300'
                ].join(' ');
                li.innerHTML = `
                    <div class="flex items-center gap-2.5">
                        <i class="fas fa-check text-cyan-400 text-xs" style="opacity:${isSelected ? 1 : 0};"></i>
                        <span>${modelId}</span>
                    </div>
                    ${badge ? `<span class="text-xs font-semibold px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 ml-3">${badge}</span>` : ''}
                `;
                li.onclick = () => pickModel(modelId);
                ul.appendChild(li);
            });
        });
    }

    // ── Rebuild provider dropdown list for every panel ─────────────
    function rebuildProviderDropdowns() {
        PANELS.forEach(panel => {
            const ul = document.getElementById(`pd-${panel}`);
            if (!ul) return;
            ul.innerHTML = '';

            Object.keys(AI_DATA).forEach(providerKey => {
                const isSelected = providerKey === activeProvider;
                const li = document.createElement('li');
                li.className = [
                    'flex items-center justify-between px-4 py-2.5 cursor-pointer transition-colors text-sm capitalize',
                    'hover:bg-cyan-50 dark:hover:bg-cyan-500/10 hover:text-cyan-600 dark:hover:text-cyan-400',
                    isSelected
                        ? 'text-cyan-600 dark:text-cyan-400 bg-cyan-50 dark:bg-cyan-500/10 font-semibold'
                        : 'text-slate-700 dark:text-slate-300'
                ].join(' ');
                li.innerHTML = `
                    <span>${capitalize(providerKey)}</span>
                    <i class="fas fa-check text-cyan-400 text-xs" style="opacity:${isSelected ? 1 : 0};"></i>
                `;
                li.onclick = () => pickProvider(providerKey);
                ul.appendChild(li);
            });
        });
    }

    // ── Pick provider — updates everything ─────────────────────────
    function pickProvider(providerKey) {
        activeProvider = providerKey;
        const models = AI_DATA[providerKey] || {};

        // Handle both array and object
        activeModel = Array.isArray(models)
            ? (models[0] || '')
            : (Object.keys(models)[0] || '');

        syncHidden();
        syncLabels();
        rebuildProviderDropdowns();
        rebuildModelDropdowns();
        closeAllDropdowns();
    }

    // ── Pick model — updates everything ───────────────────────────
    function pickModel(modelId) {
        activeModel = modelId;
        syncHidden();
        syncLabels();
        rebuildModelDropdowns();
        closeAllDropdowns();
    }

    // ── Toggle a specific dropdown open/closed ─────────────────────
    // type: 'p' = provider, 'm' = model   |  panel: 'text','mic','upload'
    window.aiToggle = function(type, panel) {
        const ddId      = (type === 'p') ? `pd-${panel}` : `md-${panel}`;
        const triggerId = (type === 'p') ? `pb-${panel}` : `mb-${panel}`;
        const dd        = document.getElementById(ddId);
        const isOpen    = !dd.classList.contains('hidden');

        closeAllDropdowns();

        if (!isOpen) {
            dd.classList.remove('hidden');
            // Rotate chevron on the trigger button
            const chevron = document.getElementById(triggerId)?.querySelector('.ai-chevron');
            if (chevron) chevron.style.transform = 'rotate(180deg)';
        }
    };

    function closeAllDropdowns() {
        document.querySelectorAll('.ai-dropdown').forEach(dd => dd.classList.add('hidden'));
        document.querySelectorAll('.ai-chevron').forEach(ch => ch.style.transform = '');
    }

    // Close on outside click
    document.addEventListener('click', e => {
        if (!e.target.closest('[id^="pw-"], [id^="mw-"]')) closeAllDropdowns();
    });

    function capitalize(str) {
        return str ? str.charAt(0).toUpperCase() + str.slice(1) : str;
    }

    // ── Boot ───────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        pickProvider(activeProvider); // sets first provider + first model, renders all dropdowns
    });

    async function handleAutoUpload() {
        const fileInput = document.getElementById('schemaFileInput');
        const uploadBtnText = document.getElementById('uploadBtnText');
        const form = document.getElementById('uploadSchemaForm');

        if (!fileInput.files.length) return;

        // UI Feedback
        const originalText = uploadBtnText.innerText;
        uploadBtnText.innerText = "Uploading...";
        
        const formData = new FormData(form);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                }
            });

            const result = await response.json();

            if (response.ok) {
                uploadBtnText.innerText = "Success!";
                // Small delay to show success before refreshing the schema view
                setTimeout(() => {
                    location.reload(); // Simplest way to refresh all UI components
                }, 800);
            } else {
                alert(result.message || 'Upload failed');
                uploadBtnText.innerText = originalText;
            }
        } catch (error) {
            console.error('Upload error:', error);
            uploadBtnText.innerText = "Error";
            setTimeout(() => { uploadBtnText.innerText = originalText; }, 2000);
        }
    }

    </script>

</body>
</html>