<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Token Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            content: [],
            theme: { extend: {} }
        }
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', system-ui, sans-serif; }
        
        .chart-container {
            position: relative;
            height: 420px;
            width: 100%;
        }
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-4px);
        }
        .filter-btn {
            transition: all 0.2s;
        }
        .filter-btn.active {
            background-color: #22d3ee !important;
            color: #0f172a !important;
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-950 dark:to-slate-900 text-slate-900 dark:text-slate-200 min-h-screen transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-6 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-10">
            <div>
                <h1 class="text-4xl font-bold tracking-tight flex items-center gap-3">
                    Token Analytics
                </h1>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Real-time overview of token usage</p>
            </div>
            <div class="flex items-center gap-4">
                <button onclick="toggleTheme()" id="theme-toggle"
                        class="w-11 h-11 flex items-center justify-center bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-2xl transition-all shadow-sm">
                    <i id="theme-icon" class="fas fa-moon text-xl text-slate-700 dark:text-slate-300"></i>
                </button>
                <button onclick="loadData()" 
                        class="px-5 py-2 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-2xl flex items-center gap-2 transition-colors">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Filters -->
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

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl p-6 card-hover shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <i class="fas fa-bolt text-3xl text-emerald-500"></i>
                    <span class="text-xs font-medium px-3 py-1 bg-emerald-100 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-full">Today</span>
                </div>
                <p class="text-5xl font-semibold tracking-tighter" id="today">0</p>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Tokens Used Today</p>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl p-6 card-hover shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <i class="fas fa-database text-3xl text-blue-500"></i>
                    <span class="text-xs font-medium px-3 py-1 bg-blue-100 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-full">All Time</span>
                </div>
                <p class="text-5xl font-semibold tracking-tighter" id="total">0</p>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Total Tokens</p>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl p-6 card-hover shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <i class="fas fa-users text-3xl text-violet-500"></i>
                    <span class="text-xs font-medium px-3 py-1 bg-violet-100 dark:bg-violet-500/10 text-violet-600 dark:text-violet-400 rounded-full">Active</span>
                </div>
                <p class="text-5xl font-semibold tracking-tighter" id="users">0</p>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Unique IPs</p>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl p-6 card-hover shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <i class="fas fa-dollar-sign text-3xl text-rose-500"></i>
                    <span class="text-xs font-medium px-3 py-1 bg-rose-100 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 rounded-full">Cost</span>
                </div>
                <p class="text-5xl font-semibold tracking-tighter" id="cost">$0</p>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Total Cost</p>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-3xl p-8 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-semibold">Daily Tokens Usage Trend</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm">Last 30 days</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="changeChartType('line')" class="px-5 py-2 rounded-2xl text-sm font-medium bg-cyan-500 text-white" id="btn-line">Line Chart</button>
                    <button onclick="changeChartType('bar')" class="px-5 py-2 rounded-2xl text-sm font-medium bg-slate-200 dark:bg-slate-700" id="btn-bar">Bar Chart</button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="chart"></canvas>
            </div>
        </div>

        <div id="footer-refresh-text" class="mt-8 text-center text-slate-500 dark:text-slate-500 text-sm">
            Data refreshes automatically every 30 seconds
        </div>
    </div>

    <script>
    let selectedProvider = 'all';
    let selectedModel = 'all';
    let currentChart = null;
    let chartType = 'line';
    let refreshInterval = null;
    const appEnv = "{{ env('APP_ENV') }}";

    // Model mapping based on provider
    const modelMapping = {
        'openai': ['gpt-4', 'gpt-3.5'],
        'anthropic': ['claude-3-sonnet'],
        // Add more providers here if needed
    };

    function getRefreshInterval() {
        const isLocal = (appEnv === 'local' || appEnv === 'development');
        const intervalMs = isLocal ? 30000 : 86400000;
        const intervalText = isLocal ? '30 seconds' : '24 hours';

        document.getElementById('footer-refresh-text').innerHTML =
            `Data refreshes automatically every ${intervalText} • <span class="cursor-pointer underline" onclick="loadData()">Refresh Now</span>`;
        return intervalMs;
    }

    async function loadFilters() {
        try {
            const res = await fetch('/analytics/filters');
            const data = await res.json();
            
            renderProviderFilter(data.providers || []);
            // Initial model render (will be updated when provider changes)
            renderModelFilter([]);
        } catch (e) {
            console.error("Failed to load filters", e);
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
        const providerLabel = document.getElementById('modelLabel');
        
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
        selectedModel = 'all';   // Reset model when provider changes

        // Update active provider button
        document.querySelectorAll('[id^="provider-"]').forEach(btn => {
            btn.classList.remove('active', 'bg-cyan-500', 'text-white', 'shadow-sm');
            if (btn.id === `provider-${provider}`) {
                btn.classList.add('active', 'bg-cyan-500', 'text-white', 'shadow-sm');
            }
        });

        // Update model filter based on selected provider
        const models = modelMapping[provider] || [];
        renderModelFilter(models);

        loadData();
    }

    function setModel(model) {
        selectedModel = model;

        // Update active model button
        document.querySelectorAll('[id^="model-"]').forEach(btn => {
            btn.classList.remove('active', 'bg-cyan-500', 'text-white', 'shadow-sm');
            if (btn.id === `model-${model}`) {
                btn.classList.add('active', 'bg-cyan-500', 'text-white', 'shadow-sm');
            }
        });

        loadData();
    }

    function toggleTheme() {
        const html = document.documentElement;
        const icon = document.getElementById('theme-icon');
        if (html.classList.contains('dark')) {
            html.classList.remove('dark');
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        } else {
            html.classList.add('dark');
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
        }
        setTimeout(loadData, 50);
    }

    async function loadData() {
        try {
            const query = `?provider=${encodeURIComponent(selectedProvider)}&model=${encodeURIComponent(selectedModel)}`;

            const [summaryRes, dailyRes] = await Promise.all([
                fetch('/analytics/summary' + query),
                fetch('/analytics/daily' + query)
            ]);

            const summary = await summaryRes.json();
            const daily = await dailyRes.json();

            document.getElementById('today').innerText = Number(summary.today_tokens || 0).toLocaleString();
            document.getElementById('total').innerText = Number(summary.total_tokens || 0).toLocaleString();
            document.getElementById('users').innerText = Number(summary.unique_users || 0).toLocaleString();
            document.getElementById('cost').innerText = "$" + Number(summary.total_cost || 0).toFixed(4);

            const labels = daily.map(d => d.date);
            const inputData = daily.map(d => Number(d.input_tokens || 0));
            const outputData = daily.map(d => Number(d.output_tokens || 0));
            const totalData = daily.map(d => Number(d.total_tokens || 0));

            renderChart(labels, inputData, outputData, totalData);
        } catch (e) {
            console.error("Error loading data:", e);
        }
    }

    function renderChart(labels, inputData, outputData, totalData) {
        const ctx = document.getElementById('chart').getContext('2d');
        if (currentChart) currentChart.destroy();

        const isDark = document.documentElement.classList.contains('dark');

        currentChart = new Chart(ctx, {
            type: chartType,
            data: {
                labels: labels,
                datasets: [
                    { label: 'Input Tokens', data: inputData, borderColor: isDark ? '#67e8f9' : '#0891b2', borderWidth: 2, tension: 0.3 },
                    { label: 'Output Tokens', data: outputData, borderColor: isDark ? '#a78bfa' : '#7c3aed', borderWidth: 2, tension: 0.3 },
                    { label: 'Total Tokens', data: totalData, borderColor: isDark ? '#f472b6' : '#db2777', borderWidth: 3, tension: 0.4 }
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

    function changeChartType(type) {
        chartType = type;
        document.getElementById('btn-line').classList.toggle('bg-cyan-500', type === 'line');
        document.getElementById('btn-line').classList.toggle('text-white', type === 'line');
        document.getElementById('btn-bar').classList.toggle('bg-cyan-500', type === 'bar');
        document.getElementById('btn-bar').classList.toggle('text-white', type === 'bar');
        loadData();
    }

    function startAutoRefresh() {
        if (refreshInterval) clearInterval(refreshInterval);
        const intervalMs = getRefreshInterval();
        refreshInterval = setInterval(loadData, intervalMs);
    }

    // Initial load
    window.onload = () => {
        loadFilters();
        loadData();
        startAutoRefresh();
    };
</script>
</body>
</html>