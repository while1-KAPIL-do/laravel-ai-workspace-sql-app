<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL AI Voice Assistant</title>

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', content: [], theme: { extend: {} } }
    </script>

    {{-- Icons & Fonts --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- App stylesheet --}}
    <link rel="stylesheet" href="{{ asset('css/sql-assistant.css') }}">
</head>

<body class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-950 dark:to-slate-900 text-slate-900 dark:text-slate-200 min-h-screen transition-colors duration-300">

    <div class="max-w-7xl mx-auto px-6 py-8">

        {{-- ── Page Header ── --}}
        @include('sql-assistant.partials._header')

        {{-- ── Database Schema Panel ── --}}
        @include('sql-assistant.partials._schema-panel')

        {{-- ── Query Input Card (Type / Record / Upload) ── --}}
        @include('sql-assistant.partials._input-card')

        {{-- ── Result: Error ── --}}
        @include('sql-assistant.partials._result-error')

        {{-- ── Result: Success ── --}}
        @include('sql-assistant.partials._result-success')

        <p class="mt-8 text-center text-slate-500 dark:text-slate-500 text-xs">
            Powered by AI · Results are auto-generated and may need review
        </p>

    </div>

    {{-- ── Inject server-side data for JS (before app script) ── --}}
    <script>
        // AI provider/model data from the controller — consumed by sql-assistant.js
        window.AI_DATA = @json($aiProviders);

        // CSRF token — used by JS for fetch() POST requests
        window.CSRF_TOKEN = '{{ csrf_token() }}';
    </script>

    {{-- ── App JavaScript ── --}}
    <script src="{{ asset('js/sql-assistant.js') }}"></script>

</body>
</html>
