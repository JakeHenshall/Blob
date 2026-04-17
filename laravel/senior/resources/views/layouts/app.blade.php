<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} @hasSection('title') &mdash; @yield('title') @endif</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    @auth
        <nav class="bg-white border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
                <div class="flex items-center gap-6">
                    <a href="{{ route('dashboard') }}" class="font-semibold text-slate-900">ClientHub</a>
                    <div class="hidden md:flex items-center gap-4 text-sm">
                        <a href="{{ route('dashboard') }}" class="text-slate-600 hover:text-slate-900">Dashboard</a>
                        <a href="{{ route('clients.index') }}" class="text-slate-600 hover:text-slate-900">Clients</a>
                        <a href="{{ route('projects.index') }}" class="text-slate-600 hover:text-slate-900">Projects</a>
                        <a href="{{ route('tasks.index') }}" class="text-slate-600 hover:text-slate-900">Tasks</a>
                    </div>
                </div>
                <div class="flex items-center gap-4 text-sm">
                    <span class="text-slate-600">{{ auth()->user()->name }}</span>
                    <span class="badge bg-slate-100 text-slate-700">{{ auth()->user()->role->label() }}</span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="text-slate-600 hover:text-slate-900">Sign out</button>
                    </form>
                </div>
            </div>
        </nav>
    @endauth

    @if (session('status'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-2 text-sm">
                {{ session('status') }}
            </div>
        </div>
    @endif

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>
</body>
</html>
