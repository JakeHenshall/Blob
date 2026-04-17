@extends('layouts.app')

@section('content')
    <div class="max-w-2xl mx-auto text-center py-12">
        <h1 class="text-4xl font-bold tracking-tight text-slate-900">ClientHub</h1>
        <p class="mt-3 text-slate-600">A production-style Laravel reference application for managing clients, projects, tasks, notes and files.</p>
        <div class="mt-8 flex items-center justify-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="btn-primary">Go to dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn-primary">Sign in</a>
                <a href="{{ route('register') }}" class="btn-secondary">Create account</a>
            @endauth
        </div>
    </div>
@endsection
