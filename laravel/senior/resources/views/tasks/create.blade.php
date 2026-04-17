@extends('layouts.app')
@section('title', 'New task')

@section('content')
    <div class="mb-2 text-sm text-slate-500">
        <a href="{{ route('projects.show', $project) }}" class="hover:underline">{{ $project->name }}</a>
    </div>
    <h1 class="text-2xl font-semibold mb-6">New task</h1>
    <form method="POST" action="{{ route('tasks.store', $project) }}" class="card p-6 space-y-4 max-w-xl">
        @csrf
        <div>
            <label class="label" for="title">Title</label>
            <input id="title" name="title" class="input" value="{{ old('title') }}" required>
            @error('title')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label" for="description">Description</label>
            <textarea id="description" name="description" rows="4" class="input">{{ old('description') }}</textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="label" for="status">Status</label>
                <select id="status" name="status" class="input">
                    @foreach ($statuses as $s)
                        <option value="{{ $s->value }}">{{ $s->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label" for="priority">Priority</label>
                <select id="priority" name="priority" class="input">
                    @foreach ($priorities as $p)
                        <option value="{{ $p->value }}" @selected($p->value === 'normal')>{{ ucfirst($p->value) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="label" for="assignee_id">Assignee</label>
                <select id="assignee_id" name="assignee_id" class="input">
                    <option value="">Unassigned</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label" for="due_on">Due date</label>
                <input id="due_on" name="due_on" type="date" class="input" value="{{ old('due_on') }}">
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button class="btn-primary">Create task</button>
            <a href="{{ route('projects.show', $project) }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
