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
        // Enable dark mode support for Tailwind CDN
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
                <button onclick="toggleTheme()" 
                        id="theme-toggle"
                        class="w-11 h-11 flex items-center justify-center bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-2xl transition-all shadow-sm">
                    <i id="theme-icon" class="fas fa-moon text-xl text-slate-700 dark:text-slate-300"></i>
                </button>
                
                <button onclick="loadData()" 
                        class="px-5 py-2 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-2xl flex items-center gap-2 transition-colors">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
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
                <p class="text-slate-500 dark:text-slate-400 mt-1">Unique Users</p>
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
                    <button onclick="changeChartType('line')" 
                            class="px-5 py-2 rounded-2xl text-sm font-medium bg-cyan-500 text-white" 
                            id="btn-line">Line Chart</button>
                    <button onclick="changeChartType('bar')" 
                            class="px-5 py-2 rounded-2xl text-sm font-medium bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600" 
                            id="btn-bar">Bar Chart</button>
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
        let currentChart = null;
        let chartType = 'line';
        let refreshInterval = null;
        const appEnv = "{{ env('APP_ENV') }}";   // based on env for AWS usage

        function getRefreshInterval() {
            const isLocal = (appEnv === 'local' || appEnv === 'development');
            
            const intervalMs = isLocal ? 30000 : 86400000;           // 30 sec vs 24 hours
            const intervalText = isLocal ? '30 seconds' : '24 hours';

            document.getElementById('footer-refresh-text').innerHTML = 
                `Data refreshes automatically every ${intervalText} • <span class="cursor-pointer underline" onclick="loadData()">Refresh Now</span>`;
            return intervalMs;
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
            
            // Re-render chart with correct colors
            setTimeout(loadData, 10);
        }

        async function loadData() {
            try {
                const summaryRes = await fetch('/analytics/summary');
                const summary = await summaryRes.json();

                document.getElementById('today').innerText = Number(summary.today_tokens || 0).toLocaleString();
                document.getElementById('total').innerText = Number(summary.total_tokens || 0).toLocaleString();
                document.getElementById('users').innerText = Number(summary.unique_users || 0).toLocaleString();

                const dailyRes = await fetch('/analytics/daily');
                const daily = await dailyRes.json();

                const labels = daily.map(d => d.date);
                const data = daily.map(d => d.total);

                renderChart(labels, data);
            } catch (e) {
                console.error("Error loading data:", e);
            }
        }

        function renderChart(labels, data) {
            const ctx = document.getElementById('chart').getContext('2d');
            if (currentChart) currentChart.destroy();

            const isDark = document.documentElement.classList.contains('dark');
            const borderColor = isDark ? '#67e8f9' : '#0891b2';
            const bgColor = isDark ? 'rgba(103, 232, 249, 0.15)' : 'rgba(8, 145, 178, 0.15)';

            currentChart = new Chart(ctx, {
                type: chartType,
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Tokens Used',
                        data: data,
                        borderColor: borderColor,
                        backgroundColor: chartType === 'line' ? bgColor : borderColor,
                        tension: 0.35,
                        borderWidth: 3,
                        pointRadius: 3,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: isDark ? '#1e2937' : '#f8fafc',
                            titleColor: isDark ? '#e2e8f0' : '#0f172a',
                            bodyColor: isDark ? '#94a3b8' : '#475569',
                            padding: 12
                        }
                    },
                    scales: {
                        y: { grid: { color: isDark ? '#334155' : '#e2e8f0' }, ticks: { color: isDark ? '#94a3b8' : '#64748b' } },
                        x: { grid: { color: isDark ? '#334155' : '#e2e8f0' }, ticks: { color: isDark ? '#94a3b8' : '#64748b' } }
                    }
                }
            });
        }

        function changeChartType(type) {
            chartType = type;
            
            document.getElementById('btn-line').classList.toggle('bg-cyan-500', type === 'line');
            document.getElementById('btn-line').classList.toggle('text-white', type === 'line');
            document.getElementById('btn-line').classList.toggle('bg-slate-200', type !== 'line');
            document.getElementById('btn-line').classList.toggle('dark:bg-slate-700', type !== 'line');
            
            document.getElementById('btn-bar').classList.toggle('bg-cyan-500', type === 'bar');
            document.getElementById('btn-bar').classList.toggle('text-white', type === 'bar');
            document.getElementById('btn-bar').classList.toggle('bg-slate-200', type !== 'bar');
            document.getElementById('btn-bar').classList.toggle('dark:bg-slate-700', type !== 'bar');
            
            loadData();
        }

        // Auto refresh every 30 seconds
        function startAutoRefresh() {
            if (refreshInterval) clearInterval(refreshInterval);
            const intervalMs = getRefreshInterval();
            refreshInterval = setInterval(loadData, intervalMs);
        }

        // Initial load
        window.onload = () => {
            loadData();
            startAutoRefresh();
        };

    </script>
</body>
</html>