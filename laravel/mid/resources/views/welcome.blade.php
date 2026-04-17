<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ClientHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="max-w-xl text-center">
            <h1 class="text-4xl font-semibold tracking-tight">ClientHub</h1>
            <p class="mt-4 text-slate-600">
                A small client, project and task manager.
                A mid-level Laravel reference app demonstrating policies, actions, queued notifications and activity logging.
            </p>
            <div class="mt-8 flex items-center justify-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-slate-900 text-white text-sm rounded-md hover:bg-slate-800">
                        Go to dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-slate-900 text-white text-sm rounded-md hover:bg-slate-800">
                        Log in
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 text-slate-800 text-sm rounded-md hover:bg-slate-100">
                        Register
                    </a>
                @endauth
            </div>
        </div>
    </div>
</body>
</html>
