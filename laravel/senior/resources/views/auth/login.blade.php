@extends('layouts.app')
@section('title', 'Sign in')

@section('content')
    <div class="max-w-sm mx-auto card p-6">
        <h1 class="text-xl font-semibold mb-4">Sign in</h1>
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="label" for="email">Email</label>
                <input id="email" name="email" type="email" class="input" value="{{ old('email') }}" required autofocus>
                @error('email')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="password">Password</label>
                <input id="password" name="password" type="password" class="input" required>
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" class="rounded border-slate-300"> Remember me
            </label>
            <button type="submit" class="btn-primary w-full">Sign in</button>
            <p class="text-center text-sm text-slate-500">No account? <a href="{{ route('register') }}" class="text-brand-600 hover:underline">Create one</a></p>
        </form>
    </div>
@endsection
