@extends('layouts.app')
@section('title', 'Create account')

@section('content')
    <div class="max-w-sm mx-auto card p-6">
        <h1 class="text-xl font-semibold mb-4">Create account</h1>
        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            <div>
                <label class="label" for="name">Name</label>
                <input id="name" name="name" class="input" value="{{ old('name') }}" required autofocus>
                @error('name')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="email">Email</label>
                <input id="email" name="email" type="email" class="input" value="{{ old('email') }}" required>
                @error('email')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="password">Password</label>
                <input id="password" name="password" type="password" class="input" required>
                @error('password')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="input" required>
            </div>
            <button type="submit" class="btn-primary w-full">Create account</button>
        </form>
    </div>
@endsection
