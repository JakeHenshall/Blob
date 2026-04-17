@extends('layouts.app')
@section('title', $task->title)

@section('content')
    <div class="mb-2 text-sm text-slate-500">
        <a href="{{ route('projects.show', $task->project) }}" class="hover:underline">{{ $task->project?->name }}</a>
    </div>
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold {{ $task->isComplete() ? 'line-through text-slate-400' : '' }}">{{ $task->title }}</h1>
            <div class="mt-2 flex items-center gap-3 text-sm text-slate-600">
                <span class="badge bg-slate-100">{{ $task->status->label() }}</span>
                <span>Priority: {{ ucfirst($task->priority->value) }}</span>
                @if ($task->due_on) <span>Due {{ $task->due_on->format('d M Y') }}</span> @endif
            </div>
        </div>
        <div class="flex items-center gap-2">
            @can('complete', $task)
                @unless ($task->isComplete())
                    <form method="POST" action="{{ route('tasks.complete', $task) }}">
                        @csrf
                        <button class="btn-primary">Mark complete</button>
                    </form>
                @endunless
            @endcan
            @can('delete', $task)
                <form method="POST" action="{{ route('tasks.destroy', $task) }}">
                    @csrf @method('DELETE')
                    <button class="btn-danger" onclick="return confirm('Delete this task?')">Delete</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
            @can('update', $task)
                <form method="POST" action="{{ route('tasks.update', $task) }}" class="card p-6 space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="label" for="title">Title</label>
                        <input id="title" name="title" class="input" value="{{ old('title', $task->title) }}" required>
                    </div>
                    <div>
                        <label class="label" for="description">Description</label>
                        <textarea id="description" name="description" rows="4" class="input">{{ old('description', $task->description) }}</textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="label">Status</label>
                            <select name="status" class="input">
                                @foreach ($statuses as $s)
                                    <option value="{{ $s->value }}" @selected(old('status', $task->status->value) === $s->value)>{{ $s->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label">Priority</label>
                            <select name="priority" class="input">
                                @foreach ($priorities as $p)
                                    <option value="{{ $p->value }}" @selected(old('priority', $task->priority->value) === $p->value)>{{ ucfirst($p->value) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="label">Assignee</label>
                            <select name="assignee_id" class="input">
                                <option value="">Unassigned</option>
                                @foreach ($users as $u)
                                    <option value="{{ $u->id }}" @selected(old('assignee_id', $task->assignee_id) == $u->id)>{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label">Due</label>
                            <input type="date" name="due_on" class="input" value="{{ old('due_on', $task->due_on?->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <button class="btn-primary">Save</button>
                </form>
            @endcan
        </div>
        <div>
            <div class="card p-4 text-sm">
                <div class="text-slate-500">Created by</div>
                <div>{{ $task->creator?->name }}</div>
                <div class="text-slate-500 mt-3">Assignee</div>
                <div>{{ $task->assignee?->name ?? 'Unassigned' }}</div>
                @if ($task->completed_at)
                    <div class="text-slate-500 mt-3">Completed</div>
                    <div>{{ $task->completed_at->format('d M Y H:i') }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
