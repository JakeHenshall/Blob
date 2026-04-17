@extends('layouts.app')
@section('title', $project->name)

@section('content')
    <div class="mb-2 text-sm text-slate-500">
        <a href="{{ route('clients.show', $project->client) }}" class="hover:underline">{{ $project->client?->name }}</a>
    </div>
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold">{{ $project->name }}</h1>
            <div class="mt-2 flex items-center gap-3 text-sm">
                <span class="badge bg-slate-100 text-slate-700">{{ $project->status->label() }}</span>
                <span class="text-slate-500">Owner: {{ $project->owner?->name }}</span>
                @if ($project->due_on)
                    <span class="text-slate-500">Due {{ $project->due_on->format('d M Y') }}</span>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-2">
            @can('update', $project)
                <a href="{{ route('projects.edit', $project) }}" class="btn-secondary">Edit</a>
            @endcan
            <a href="{{ route('tasks.create', $project) }}" class="btn-primary">New task</a>
        </div>
    </div>

    @if ($project->description)
        <div class="card p-4 mb-6 text-sm text-slate-700 whitespace-pre-line">{{ $project->description }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="card">
                <div class="px-4 py-3 border-b border-slate-200 font-medium flex items-center justify-between">
                    <span>Tasks</span>
                    <a href="{{ route('tasks.create', $project) }}" class="text-sm text-brand-600 hover:underline">Add task</a>
                </div>
                <ul class="divide-y divide-slate-100 text-sm">
                    @forelse ($project->tasks as $task)
                        <li class="px-4 py-3 flex items-center justify-between gap-4">
                            <div class="flex items-start gap-3">
                                @can('complete', $task)
                                    <form method="POST" action="{{ route('tasks.complete', $task) }}">
                                        @csrf
                                        <button type="submit" class="mt-0.5 h-4 w-4 rounded border {{ $task->isComplete() ? 'bg-emerald-500 border-emerald-500' : 'border-slate-300 hover:border-brand-500' }}" title="Mark complete"></button>
                                    </form>
                                @endcan
                                <div>
                                    <a href="{{ route('tasks.show', $task) }}" class="font-medium hover:underline {{ $task->isComplete() ? 'line-through text-slate-400' : '' }}">{{ $task->title }}</a>
                                    <div class="text-slate-500 text-xs">
                                        {{ $task->status->label() }} &middot;
                                        {{ $task->assignee?->name ?? 'Unassigned' }}
                                        @if ($task->due_on) &middot; due {{ $task->due_on->format('d M') }} @endif
                                    </div>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="px-4 py-6 text-slate-500 text-center">No tasks yet.</li>
                    @endforelse
                </ul>
            </div>

            <div class="card">
                <div class="px-4 py-3 border-b border-slate-200 font-medium">Notes</div>
                @can('addNote', $project)
                    <form method="POST" action="{{ route('notes.store', $project) }}" class="p-4 border-b border-slate-100">
                        @csrf
                        <textarea name="body" rows="3" class="input" placeholder="Add a note..." required></textarea>
                        @error('body')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
                        <div class="mt-2 flex justify-end">
                            <button class="btn-primary">Add note</button>
                        </div>
                    </form>
                @endcan
                <ul class="divide-y divide-slate-100 text-sm">
                    @forelse ($project->notes as $note)
                        <li class="px-4 py-3">
                            <div class="text-xs text-slate-500">{{ $note->author?->name }} &middot; {{ $note->created_at?->diffForHumans() }}</div>
                            <p class="whitespace-pre-line mt-1">{{ $note->body }}</p>
                        </li>
                    @empty
                        <li class="px-4 py-6 text-slate-500 text-center">No notes yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card">
                <div class="px-4 py-3 border-b border-slate-200 font-medium">Files</div>
                @can('uploadFile', $project)
                    <form method="POST" action="{{ route('files.store', $project) }}" enctype="multipart/form-data" class="p-4 border-b border-slate-100 space-y-2">
                        @csrf
                        <input type="file" name="file" required class="block text-sm">
                        @error('file')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
                        <button class="btn-secondary w-full">Upload</button>
                    </form>
                @endcan
                <ul class="divide-y divide-slate-100 text-sm">
                    @forelse ($project->files as $file)
                        <li class="px-4 py-2 flex items-center justify-between">
                            <div>
                                <a href="{{ route('files.download', [$project, $file]) }}" class="font-medium hover:underline">{{ $file->original_name }}</a>
                                <div class="text-xs text-slate-500">{{ $file->humanSize() }} &middot; {{ $file->uploader?->name }}</div>
                            </div>
                            @can('delete', $file)
                                <form method="POST" action="{{ route('files.destroy', [$project, $file]) }}">
                                    @csrf @method('DELETE')
                                    <button class="text-rose-600 text-xs hover:underline" onclick="return confirm('Delete this file?')">Delete</button>
                                </form>
                            @endcan
                        </li>
                    @empty
                        <li class="px-4 py-6 text-slate-500 text-center">No files.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
