@extends('layouts.app')
@section('title', 'New client')

@section('content')
    <h1 class="text-2xl font-semibold mb-6">New client</h1>
    <form method="POST" action="{{ route('clients.store') }}" class="card p-6 space-y-4 max-w-xl">
        @csrf
        @include('clients._form')
        <div class="flex items-center gap-3">
            <button class="btn-primary">Create client</button>
            <a href="{{ route('clients.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
