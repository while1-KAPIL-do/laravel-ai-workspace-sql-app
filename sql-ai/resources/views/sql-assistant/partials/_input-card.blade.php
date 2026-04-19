{{-- ============================================================
     Partial: _input-card.blade.php
     Main query input card: mode selector cards (Type / Record /
     Upload) and the three input panels with AI selector toolbars.
     Variables used: none (form posts to named route 'ai-sql-assitance')
     ============================================================ --}}

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl shadow-sm px-6 py-6 mb-4">

    <form action="{{ route('ai-sql-assitance') }}" method="POST" enctype="multipart/form-data" id="voiceForm">
        @csrf

        {{-- ── Single source of truth hidden inputs (POSTed) ── --}}
        <input type="hidden" name="input_mode" id="inputMode"      value="text">
        <input type="hidden" name="provider"   id="globalProvider" value="openai">
        <input type="hidden" name="model"      id="globalModel"    value="">

        {{-- ── Mode Selection Cards ── --}}
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Choose your input method</p>

        <div class="grid grid-cols-3 gap-3 mb-5">

            {{-- Type card --}}
            <div class="mode-card active border border-slate-200 dark:border-slate-700 rounded-2xl p-4 text-center bg-white dark:bg-slate-800"
                 onclick="switchMode('text')">
                <div class="mode-icon-wrap w-10 h-10 rounded-xl flex items-center justify-center mx-auto mb-3 bg-slate-100 dark:bg-slate-700 transition-colors">
                    <i class="fas fa-keyboard text-slate-500 dark:text-slate-400" style="font-size:16px;"></i>
                </div>
                <p class="mode-label text-sm font-semibold text-slate-700 dark:text-slate-200 transition-colors">Type</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Write your query</p>
            </div>

            {{-- Record card --}}
            <div class="mode-card border border-slate-200 dark:border-slate-700 rounded-2xl p-4 text-center bg-white dark:bg-slate-800"
                 onclick="switchMode('mic')">
                <div class="mode-icon-wrap w-10 h-10 rounded-xl flex items-center justify-center mx-auto mb-3 bg-slate-100 dark:bg-slate-700 transition-colors">
                    <i class="fas fa-microphone text-slate-500 dark:text-slate-400" style="font-size:16px;"></i>
                </div>
                <p class="mode-label text-sm font-semibold text-slate-700 dark:text-slate-200 transition-colors">Record</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Speak live</p>
            </div>

            {{-- Upload card --}}
            <div class="mode-card border border-slate-200 dark:border-slate-700 rounded-2xl p-4 text-center bg-white dark:bg-slate-800"
                 onclick="switchMode('upload')">
                <div class="mode-icon-wrap w-10 h-10 rounded-xl flex items-center justify-center mx-auto mb-3 bg-slate-100 dark:bg-slate-700 transition-colors">
                    <i class="fas fa-arrow-up-from-bracket text-slate-500 dark:text-slate-400" style="font-size:16px;"></i>
                </div>
                <p class="mode-label text-sm font-semibold text-slate-700 dark:text-slate-200 transition-colors">Upload</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">MP3 / WAV file</p>
            </div>

        </div>


        {{-- ════════════════════════════════════════════════════
             Panel: TYPE
        ═════════════════════════════════════════════════════ --}}
        <div class="input-panel active" id="panel-text">

            <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-2xl p-4 mb-4 composer-box transition-colors duration-200">
                <textarea name="text_query" id="queryText" rows="3"
                        placeholder="e.g., Show me all active users who joined in the last 30 days..."
                        class="w-full bg-transparent border-none outline-none text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 resize-none font-sans"></textarea>
            </div>

            @include('sql-assistant.partials._ai-selector-toolbar', [
                'panel'      => 'text',
                'submitId'   => 'submitBtn',
                'borderTop'  => true,
            ])
        </div>


        {{-- ════════════════════════════════════════════════════
             Panel: MIC
        ═════════════════════════════════════════════════════ --}}
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

                <div class="mt-5">
                    @include('sql-assistant.partials._ai-selector-toolbar', [
                        'panel'          => 'mic',
                        'submitId'       => 'submitBtnMic',
                        'submitDisabled' => true,
                        'borderTop'      => true,
                    ])
                </div>
            </div>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-2 text-right">Recording will be transcribed then converted to SQL</p>
        </div>


        {{-- ════════════════════════════════════════════════════
             Panel: UPLOAD
        ═════════════════════════════════════════════════════ --}}
        <div class="input-panel" id="panel-upload">

            <label class="upload-zone block border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-2xl p-8 text-center cursor-pointer" id="uploadZone">

                {{-- Default prompt --}}
                <div id="uploadPrompt">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-3 bg-slate-100 dark:bg-slate-800">
                        <i class="fas fa-cloud-arrow-up text-2xl text-slate-400"></i>
                    </div>
                    <p class="text-sm font-medium text-slate-600 dark:text-slate-300">Drop your audio file here</p>
                    <p class="text-xs text-slate-400 mt-1">MP3 or WAV · click to browse</p>
                </div>

                {{-- File selected state --}}
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

            <div class="mt-3">
                @include('sql-assistant.partials._ai-selector-toolbar', [
                    'panel'          => 'upload',
                    'submitId'       => 'submitBtnUpload',
                    'submitDisabled' => true,
                    'borderTop'      => true,
                ])
            </div>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-2 text-right">Audio will be transcribed then converted to SQL</p>
        </div>

    </form>
</div>
