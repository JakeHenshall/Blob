@extends('layouts.app')
@section('title', 'Tasks')

@section('content')
    <h1 class="text-2xl font-semibold mb-6">Tasks</h1>

    <form method="GET" class="card p-4 flex flex-wrap gap-3 items-end mb-4">
        <div>
            <label class="label" for="q">Search</label>
            <input id="q" name="q" value="{{ request('q') }}" class="input" placeholder="Task title">
        </div>
        <div>
            <label class="label" for="status">Status</label>
            <select id="status" name="status" class="input">
                <option value="">Any</option>
                @foreach ($statuses as $s)
                    <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
                @endforeach
            </select>
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="mine" value="1" @checked(request('mine')) class="rounded border-slate-300">
            Assigned to me
        </label>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="open" value="1" @checked(request('open')) class="rounded border-slate-300">
            Only open
        </label>
        <button class="btn-secondary">Filter</button>
    </form>

    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-4 py-2 text-left">Title</th>
                    <th class="px-4 py-2 text-left">Project</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-left">Assignee</th>
                    <th class="px-4 py-2 text-left">Due</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($tasks as $task)
                    <tr>
                        <td class="px-4 py-2">
                            <a href="{{ route('tasks.show', $task) }}" class="font-medium hover:underline {{ $task->isComplete() ? 'line-through text-slate-400' : '' }}">{{ $task->title }}</a>
                        </td>
                        <td class="px-4 py-2">{{ $task->project?->name }}</td>
                        <td class="px-4 py-2">{{ $task->status->label() }}</td>
                        <td class="px-4 py-2">{{ $task->assignee?->name ?? '—' }}</td>
                        <td class="px-4 py-2 text-slate-500">{{ $task->due_on?->format('d M Y') ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">No tasks found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $tasks->links() }}</div>
@endsection
