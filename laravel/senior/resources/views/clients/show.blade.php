@extends('layouts.app')
@section('title', $client->name)

@section('content')
    <div class="flex items-start justify-between mb-6">
        <div>
            <div class="text-sm text-slate-500">Client</div>
            <h1 class="text-2xl font-semibold">{{ $client->name }}</h1>
            @if ($client->isArchived())
                <span class="badge bg-amber-100 text-amber-800 mt-2">Archived {{ $client->archived_at->diffForHumans() }}</span>
            @endif
        </div>
        <div class="flex items-center gap-2">
            @can('update', $client)
                <a href="{{ route('clients.edit', $client) }}" class="btn-secondary">Edit</a>
            @endcan
            @can('create', App\Models\Project::class)
                <a href="{{ route('projects.create', $client) }}" class="btn-primary">New project</a>
            @endcan
            @can('archive', $client)
                @unless ($client->isArchived())
                    <form method="POST" action="{{ route('clients.archive', $client) }}">
                        @csrf
                        <button class="btn-secondary" onclick="return confirm('Archive this client and its active projects?')">Archive</button>
                    </form>
                @endunless
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 card p-4 text-sm">
            <div class="text-slate-500">Owner</div>
            <div class="mb-3">{{ $client->owner?->name }}</div>
            <div class="text-slate-500">Email</div>
            <div class="mb-3">{{ $client->contact_email ?: '—' }}</div>
            <div class="text-slate-500">Phone</div>
            <div class="mb-3">{{ $client->contact_phone ?: '—' }}</div>
            @if ($client->notes)
                <div class="text-slate-500">Notes</div>
                <p class="whitespace-pre-line">{{ $client->notes }}</p>
            @endif
        </div>

        <div class="lg:col-span-2 card">
            <div class="px-4 py-3 border-b border-slate-200 font-medium">Projects ({{ $client->projects->count() }})</div>
            <ul class="divide-y divide-slate-100">
                @forelse ($client->projects as $project)
                    <li class="px-4 py-3 text-sm flex items-center justify-between">
                        <div>
                            <a href="{{ route('projects.show', $project) }}" class="font-medium hover:underline">{{ $project->name }}</a>
                            <div class="text-slate-500">{{ $project->status->label() }}</div>
                        </div>
                        <div class="text-slate-500">{{ $project->due_on?->format('d M Y') ?: 'No due date' }}</div>
                    </li>
                @empty
                    <li class="px-4 py-6 text-slate-500 text-sm text-center">No projects yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
