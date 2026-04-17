@extends('layouts.app')
@section('title', 'Edit client')

@section('content')
    <h1 class="text-2xl font-semibold mb-6">Edit client</h1>
    <form method="POST" action="{{ route('clients.update', $client) }}" class="card p-6 space-y-4 max-w-xl">
        @csrf
        @method('PUT')
        @include('clients._form', ['client' => $client])
        <div class="flex items-center gap-3">
            <button class="btn-primary">Save changes</button>
            <a href="{{ route('clients.show', $client) }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
