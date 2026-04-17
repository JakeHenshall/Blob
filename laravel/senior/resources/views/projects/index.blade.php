@extends('layouts.app')
@section('title', 'Projects')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Projects</h1>
    </div>

    <form method="GET" class="card p-4 flex flex-wrap gap-3 items-end mb-4">
        <div>
            <label class="label" for="q">Search</label>
            <input id="q" name="q" value="{{ request('q') }}" class="input" placeholder="Project name">
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
        <div>
            <label class="label" for="sort">Sort</label>
            <select id="sort" name="sort" class="input">
                <option value="created_at" @selected(request('sort') === 'created_at')>Newest</option>
                <option value="name" @selected(request('sort') === 'name')>Name</option>
                <option value="due_on" @selected(request('sort') === 'due_on')>Due date</option>
            </select>
        </div>
        <button class="btn-secondary">Filter</button>
    </form>

    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-4 py-2 text-left">Name</th>
                    <th class="px-4 py-2 text-left">Client</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-left">Tasks</th>
                    <th class="px-4 py-2 text-left">Due</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($projects as $project)
                    <tr>
                        <td class="px-4 py-2">
                            <a href="{{ route('projects.show', $project) }}" class="font-medium hover:underline">{{ $project->name }}</a>
                            <div class="text-slate-500 text-xs">Owner: {{ $project->owner?->name }}</div>
                        </td>
                        <td class="px-4 py-2">{{ $project->client?->name }}</td>
                        <td class="px-4 py-2">
                            <span class="badge bg-slate-100 text-slate-700">{{ $project->status->label() }}</span>
                        </td>
                        <td class="px-4 py-2 text-slate-600">{{ $project->open_tasks_count }}/{{ $project->tasks_count }}</td>
                        <td class="px-4 py-2 text-slate-500">{{ $project->due_on?->format('d M Y') ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">No projects.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $projects->links() }}</div>
@endsection
