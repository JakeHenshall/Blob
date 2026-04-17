@extends('layouts.app')
@section('title', 'Edit project')

@section('content')
    <h1 class="text-2xl font-semibold mb-6">Edit project</h1>
    <form method="POST" action="{{ route('projects.update', $project) }}" class="card p-6 space-y-4 max-w-xl">
        @csrf
        @method('PUT')
        @include('projects._form', ['project' => $project, 'statuses' => $statuses])
        <div class="flex items-center gap-3">
            <button class="btn-primary">Save changes</button>
            <a href="{{ route('projects.show', $project) }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
