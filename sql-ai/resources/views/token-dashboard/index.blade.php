<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Token Dashboard</title>

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', content: [], theme: { extend: {} } }
    </script>

    {{-- Chart.js & Icons --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- App stylesheet --}}
    <link rel="stylesheet" href="{{ asset('css/token-dashboard.css') }}">
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-950 dark:to-slate-900 text-slate-900 dark:text-slate-200 min-h-screen transition-colors duration-300">

    <div class="max-w-7xl mx-auto px-6 py-8">

        {{-- ── Page Header ── --}}
        @include('token-dashboard.partials._header')

        {{-- ── Provider & Model Filters ── --}}
        @include('token-dashboard.partials._filters')

        {{-- ── Summary KPI Cards ── --}}
        @include('token-dashboard.partials._summary-cards')

        {{-- ── Daily Usage Chart ── --}}
        @include('token-dashboard.partials._chart')

        <div id="footer-refresh-text" class="mt-8 text-center text-slate-500 dark:text-slate-500 text-sm">
            Data refreshes automatically every 30 seconds
        </div>

    </div>

    {{-- ── Inject server-side data for JS (before app script) ── --}}
    <script>
        // APP_ENV from Laravel — used by token-dashboard.js to set refresh interval
        window.APP_ENV = "{{ env('APP_ENV') }}";
    </script>

    {{-- ── App JavaScript ── --}}
    <script src="{{ asset('js/token-dashboard.js') }}"></script>

</body>
</html>
