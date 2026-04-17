@extends('layouts.app')
@section('title', 'New project')

@section('content')
    <div class="mb-4 text-sm text-slate-500">
        <a href="{{ route('clients.show', $client) }}" class="hover:underline">{{ $client->name }}</a> / new project
    </div>
    <h1 class="text-2xl font-semibold mb-6">New project</h1>
    <form method="POST" action="{{ route('projects.store', $client) }}" class="card p-6 space-y-4 max-w-xl">
        @csrf
        @include('projects._form')
        <div class="flex items-center gap-3">
            <button class="btn-primary">Create project</button>
            <a href="{{ route('clients.show', $client) }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
