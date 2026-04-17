<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'ClientHub') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50 min-h-screen flex flex-col">
        <header class="w-full border-b border-gray-200 bg-white">
            <div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
                <span class="font-semibold text-gray-900">{{ config('app.name') }}</span>
                @if (Route::has('login'))
                    <nav class="flex items-center gap-3 text-sm">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="px-4 py-2 rounded-md bg-gray-900 text-white hover:bg-gray-800">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-700 hover:text-gray-900">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="px-4 py-2 rounded-md bg-gray-900 text-white hover:bg-gray-800">Register</a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </div>
        </header>

        <main class="flex-1 flex items-center">
            <div class="max-w-3xl mx-auto px-6 py-16 text-center">
                <h1 class="text-4xl sm:text-5xl font-semibold text-gray-900 tracking-tight">
                    A simple hub for clients, projects, and tasks.
                </h1>
                <p class="mt-4 text-gray-600 text-lg leading-relaxed">
                    {{ config('app.name') }} is a small internal-style app built with Laravel, Blade, and Tailwind.
                    Track clients, plan projects, and tick off tasks.
                </p>
                <div class="mt-8 flex items-center justify-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-5 py-2.5 bg-gray-900 text-white rounded-md hover:bg-gray-800">Go to dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="px-5 py-2.5 bg-gray-900 text-white rounded-md hover:bg-gray-800">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-5 py-2.5 border border-gray-300 text-gray-800 rounded-md hover:border-gray-400">Create an account</a>
                        @endif
                    @endauth
                </div>
            </div>
        </main>

        <footer class="py-6 text-center text-xs text-gray-500">
            Built with Laravel {{ Illuminate\Foundation\Application::VERSION }} &middot; PHP {{ PHP_VERSION }}
        </footer>
    </body>
</html>
