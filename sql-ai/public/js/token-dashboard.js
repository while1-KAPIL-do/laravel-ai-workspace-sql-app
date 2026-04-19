/* =========================================================
   Token Dashboard — JavaScript
   public/js/token-dashboard.js
   =========================================================
   NOTE: window.APP_ENV is injected by the Blade view before
   this file loads. See the inline <script> block in index.blade.php.
   ========================================================= */

let selectedProvider = 'all';
let selectedModel    = 'all';
let currentChart     = null;
let chartType        = 'line';
let refreshInterval  = null;

// Model mapping based on provider
const modelMapping = {
    'openai':     ['gpt-4', 'gpt-3.5'],
    'anthropic':  ['claude-3-sonnet'],
    // Add more providers here if needed
};

// ── Refresh interval ──────────────────────────────────────────
function getRefreshInterval() {
    const isLocal    = (window.APP_ENV === 'local' || window.APP_ENV === 'development');
    const intervalMs = isLocal ? 30000 : 3600000;
    const intervalText = isLocal ? '30 seconds' : '1 hours';

    document.getElementById('footer-refresh-text').innerHTML =
        `Data refreshes automatically every ${intervalText} • <span class="cursor-pointer underline" onclick="loadData()">Refresh Now</span>`;
    return intervalMs;
}

// ── Filters ───────────────────────────────────────────────────
let filtersData = {};

async function loadFilters() {
    try {
        const res  = await fetch('/analytics/filters');
        const data = await res.json();

        filtersData = data;
        renderProviderFilter(data.providers || []);
        renderModelFilter([]);
    } catch (e) {
        console.error('Failed to load filters', e);
    }
}

function renderProviderFilter(providers) {
    const container = document.getElementById('providerFilters');
    let html = `
        <button onclick="setProvider('all')"
                class="filter-btn px-4 py-1.5 text-sm rounded-2xl transition-all active" id="provider-all">
            All
        </button>
    `;
    providers.forEach(provider => {
        html += `
            <button onclick="setProvider('${provider}')"
                    class="filter-btn px-4 py-1.5 text-sm rounded-2xl bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 transition-all"
                    id="provider-${provider}">
                ${provider}
            </button>
        `;
    });
    container.innerHTML = html;
}

function renderModelFilter(models) {
    const container = document.getElementById('modelFilters');
    if (!models || models.length === 0) {
        container.innerHTML = `<p class="text-sm text-slate-400">Select a provider to see models</p>`;
        return;
    }
    let html = `
        <button onclick="setModel('all')"
                class="filter-btn px-4 py-1.5 text-sm rounded-2xl transition-all active" id="model-all">
            All
        </button>
    `;
    models.forEach(model => {
        html += `
            <button onclick="setModel('${model}')"
                    class="filter-btn px-4 py-1.5 text-sm rounded-2xl bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 transition-all"
                    id="model-${model}">
                ${model}
            </button>
        `;
    });
    container.innerHTML = html;
}

function setProvider(provider) {
    selectedProvider = provider;
    selectedModel    = 'all';

    document.querySelectorAll('[id^="provider-"]').forEach(btn => {
        btn.classList.remove('active', 'bg-cyan-500', 'text-white', 'shadow-sm');
        if (btn.id === `provider-${provider}`) {
            btn.classList.add('active', 'bg-cyan-500', 'text-white', 'shadow-sm');
        }
    });
    
    // Fetch models dynamically from API response
    let models = [];

    if (provider !== 'all' && filtersData.models) {
        models = filtersData.models || [];
    }

    renderModelFilter(models);
    loadData();
}

function setModel(model) {
    selectedModel = model;

    document.querySelectorAll('[id^="model-"]').forEach(btn => {
        btn.classList.remove('active', 'bg-cyan-500', 'text-white', 'shadow-sm');
        if (btn.id === `model-${model}`) {
            btn.classList.add('active', 'bg-cyan-500', 'text-white', 'shadow-sm');
        }
    });

    loadData();
}

// ── Theme ─────────────────────────────────────────────────────
window.toggleTheme = function () {
    const html = document.documentElement;
    const icon = document.getElementById('theme-icon');
    if (html.classList.contains('dark')) {
        html.classList.remove('dark');
        icon.classList.replace('fa-moon', 'fa-sun');
    } else {
        html.classList.add('dark');
        icon.classList.replace('fa-sun', 'fa-moon');
    }
    setTimeout(loadData, 50);
};

// ── Data loading ──────────────────────────────────────────────
window.loadData = async function () {
    try {
        const query = `?provider=${encodeURIComponent(selectedProvider)}&model=${encodeURIComponent(selectedModel)}`;

        const [summaryRes, dailyRes] = await Promise.all([
            fetch('/analytics/summary' + query),
            fetch('/analytics/daily'   + query)
        ]);

        const summary = await summaryRes.json();
        const daily   = await dailyRes.json();

        document.getElementById('today').innerText = Number(summary.today_tokens || 0).toLocaleString();
        document.getElementById('total').innerText = Number(summary.total_tokens  || 0).toLocaleString();
        document.getElementById('users').innerText = Number(summary.unique_users  || 0).toLocaleString();
        document.getElementById('cost').innerText  = '$' + Number(summary.total_cost || 0).toFixed(4);

        const labels     = daily.map(d => d.date);
        const inputData  = daily.map(d => Number(d.input_tokens  || 0));
        const outputData = daily.map(d => Number(d.output_tokens || 0));
        const totalData  = daily.map(d => Number(d.total_tokens  || 0));

        renderChart(labels, inputData, outputData, totalData);
    } catch (e) {
        console.error('Error loading data:', e);
    }
};

// ── Chart ─────────────────────────────────────────────────────
function renderChart(labels, inputData, outputData, totalData) {
    const ctx = document.getElementById('chart').getContext('2d');
    if (currentChart) currentChart.destroy();

    const isDark = document.documentElement.classList.contains('dark');

    currentChart = new Chart(ctx, {
        type: chartType,
        data: {
            labels: labels,
            datasets: [
                { label: 'Input Tokens',  data: inputData,  borderColor: isDark ? '#67e8f9' : '#0891b2', borderWidth: 2, tension: 0.3 },
                { label: 'Output Tokens', data: outputData, borderColor: isDark ? '#a78bfa' : '#7c3aed', borderWidth: 2, tension: 0.3 },
                { label: 'Total Tokens',  data: totalData,  borderColor: isDark ? '#f472b6' : '#db2777', borderWidth: 3, tension: 0.4 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: true, position: 'top' } },
            scales: {
                y: { grid: { color: isDark ? '#334155' : '#e2e8f0' } },
                x: { grid: { color: isDark ? '#334155' : '#e2e8f0' } }
            }
        }
    });
}

window.changeChartType = function (type) {
    chartType = type;
    document.getElementById('btn-line').classList.toggle('bg-cyan-500', type === 'line');
    document.getElementById('btn-line').classList.toggle('text-white',  type === 'line');
    document.getElementById('btn-bar').classList.toggle('bg-cyan-500',  type === 'bar');
    document.getElementById('btn-bar').classList.toggle('text-white',   type === 'bar');
    loadData();
};

// ── Auto-refresh ──────────────────────────────────────────────
function startAutoRefresh() {
    if (refreshInterval) clearInterval(refreshInterval);
    const intervalMs = getRefreshInterval();
    refreshInterval  = setInterval(loadData, intervalMs);
}

// ── Boot ──────────────────────────────────────────────────────
window.onload = () => {
    loadFilters();
    loadData();
    startAutoRefresh();
};
