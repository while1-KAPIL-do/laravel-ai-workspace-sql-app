/* =========================================================
   SQL AI Voice Assistant — JavaScript
   public/js/sql-assistant.js
   =========================================================
   NOTE: AI_DATA and CSRF token are injected by the Blade
   layout via inline <script> tags before this file loads.
   See the @section('scripts') block in the main view.
   ========================================================= */

// ── Theme ─────────────────────────────────────────────────────
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


// ── Mode switcher ─────────────────────────────────────────────
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


// ── Schema panel ──────────────────────────────────────────────
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


// ── Execute SQL ───────────────────────────────────────────────
const executeBtn = document.getElementById('executeSqlBtn');
if (executeBtn) {
    executeBtn.addEventListener('click', async () => {
        const sql = document.getElementById('sqlQuery').innerText.trim();
        const resultDiv = document.getElementById('executionResult');
        resultDiv.innerHTML = `<div class="flex items-center gap-3 text-cyan-400 text-sm py-3"><i class="fas fa-circle-notch fa-spin"></i><span>Executing query…</span></div>`;
        try {
            const res  = await fetch('/schema/execute-sql', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.CSRF_TOKEN
                },
                body: JSON.stringify({ sql })
            });
            const data = await res.json();
            if (!res.ok) {
                resultDiv.innerHTML = `<div class="flex items-center gap-3 bg-red-500/10 border border-red-500/20 text-red-400 px-5 py-4 rounded-2xl text-sm"><i class="fas fa-circle-xmark"></i>${data.error || 'SQL execution failed'}</div>`;
                return;
            }
            renderTable(data.data || [], resultDiv);
        } catch (err) {
            resultDiv.innerHTML = `<div class="flex items-center gap-3 bg-red-500/10 border border-red-500/20 text-red-400 px-5 py-4 rounded-2xl text-sm"><i class="fas fa-circle-xmark"></i>Network error</div>`;
        }
    });
}

function renderTable(rows, container) {
    if (!rows.length) {
        container.innerHTML = `<div class="text-center text-slate-400 py-8 text-sm">No data returned</div>`;
        return;
    }
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


// ── Recording + form submit ───────────────────────────────────
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
                    if (fileNameDisplay) {
                        fileNameDisplay.textContent = '🎙️ voice-query.webm · ready to send';
                        fileNameDisplay.classList.remove('hidden');
                    }
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
            if (btn && !btn.disabled) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-1.5"></i> Processing…';
                btn.style.opacity = '0.75';
            }
        });
    });
});


// ══════════════════════════════════════════════════════════════
// AI PROVIDER + MODEL SELECTOR
// ══════════════════════════════════════════════════════════════
// NOTE: window.AI_DATA is set by the Blade view before this
// script loads. See @section('scripts') in the main view.

const PANELS = ['text', 'mic', 'upload'];

let activeProvider = Object.keys(window.AI_DATA)[0] || 'openai';
let activeModel    = '';

// ── Sync the two global hidden inputs (what gets POSTed) ──────
function syncHidden() {
    document.getElementById('globalProvider').value = activeProvider;
    document.getElementById('globalModel').value    = activeModel;
}

// ── Update every panel's button labels simultaneously ─────────
function syncLabels() {
    document.querySelectorAll('.provider-label').forEach(el => {
        el.textContent = capitalize(activeProvider);
    });
    document.querySelectorAll('.model-label').forEach(el => {
        el.textContent = activeModel || 'Select model';
    });
}

// ── Rebuild model dropdown list for every panel ───────────────
function rebuildModelDropdowns() {
    const models = window.AI_DATA[activeProvider] || {};

    const modelEntries = Array.isArray(models)
        ? models.map(m => [m, {}])
        : Object.entries(models);

    PANELS.forEach(panel => {
        const ul = document.getElementById(`md-${panel}`);
        if (!ul) return;
        ul.innerHTML = '';

        modelEntries.forEach(([modelId, meta]) => {
            console.log(modelId, meta, meta.badge);
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

// ── Rebuild provider dropdown list for every panel ────────────
function rebuildProviderDropdowns() {
    PANELS.forEach(panel => {
        const ul = document.getElementById(`pd-${panel}`);
        if (!ul) return;
        ul.innerHTML = '';

        Object.keys(window.AI_DATA).forEach(providerKey => {
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

// ── Pick provider — updates everything ────────────────────────
function pickProvider(providerKey) {
    activeProvider = providerKey;
    const models = window.AI_DATA[providerKey] || {};
    activeModel = Array.isArray(models)
        ? (models[0] || '')
        : (Object.keys(models)[0] || '');
    syncHidden();
    syncLabels();
    rebuildProviderDropdowns();
    rebuildModelDropdowns();
    closeAllDropdowns();
}

// ── Pick model — updates everything ──────────────────────────
function pickModel(modelId) {
    activeModel = modelId;
    syncHidden();
    syncLabels();
    rebuildModelDropdowns();
    closeAllDropdowns();
}

// ── Toggle a specific dropdown open/closed ────────────────────
window.aiToggle = function(type, panel) {
    const ddId      = (type === 'p') ? `pd-${panel}` : `md-${panel}`;
    const triggerId = (type === 'p') ? `pb-${panel}` : `mb-${panel}`;
    const dd        = document.getElementById(ddId);
    const isOpen    = !dd.classList.contains('hidden');
    closeAllDropdowns();
    if (!isOpen) {
        dd.classList.remove('hidden');
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

// ── Boot ──────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    pickProvider(activeProvider);
});


// ── Schema auto-upload ────────────────────────────────────────
async function handleAutoUpload() {
    const fileInput     = document.getElementById('schemaFileInput');
    const uploadBtnText = document.getElementById('uploadBtnText');
    const form          = document.getElementById('uploadSchemaForm');

    if (!fileInput.files.length) return;

    const originalText = uploadBtnText.innerText;
    uploadBtnText.innerText = 'Uploading...';

    const formData = new FormData(form);

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': window.CSRF_TOKEN
            }
        });

        const result = await response.json();

        if (response.ok) {
            uploadBtnText.innerText = 'Success!';
            setTimeout(() => { location.reload(); }, 800);
        } else {
            alert(result.message || 'Upload failed');
            uploadBtnText.innerText = originalText;
        }
    } catch (error) {
        console.error('Upload error:', error);
        uploadBtnText.innerText = 'Error';
        setTimeout(() => { uploadBtnText.innerText = originalText; }, 2000);
    }
}

// Expose to HTML inline onchange attribute
window.handleAutoUpload = handleAutoUpload;


// ── SQL | Copy to clipboard ────────────────────
document.getElementById('copySqlBtn').addEventListener('click', function () {
    const sqlText = document.getElementById('sqlQuery').innerText.trim();

    navigator.clipboard.writeText(sqlText).then(() => {
        const btn = this;
        btn.innerHTML = '<i class="fas fa-check text-xs text-emerald-400"></i>';

        setTimeout(() => {
            btn.innerHTML = '<i class="fas fa-copy text-xs"></i>';
        }, 1500);
    });
});
