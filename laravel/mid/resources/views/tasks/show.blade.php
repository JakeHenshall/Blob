<x-app-layout>
    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            <x-page-header :title="$task->title">
                <x-slot name="subtitle">
                    <a href="{{ route('projects.show', $task->project) }}" class="hover:underline">{{ $task->project->name }}</a>
                    &middot; <a href="{{ route('clients.show', $task->project->client) }}" class="hover:underline">{{ $task->project->client?->name }}</a>
                </x-slot>
                <x-slot name="actions">
                    @can('complete', $task)
                        @if ($task->status->isOpen())
                            <form method="POST" action="{{ route('tasks.complete', $task) }}">
                                @csrf
                                @method('PATCH')
                                <button class="inline-flex items-center px-3 py-2 bg-emerald-600 text-white text-sm rounded-md hover:bg-emerald-500">Mark complete</button>
                            </form>
                        @endif
                    @endcan
                    @can('update', $task)
                        <a href="{{ route('tasks.edit', $task) }}" class="inline-flex items-center px-3 py-2 bg-white border border-slate-300 text-slate-800 text-sm rounded-md hover:bg-slate-100">Edit</a>
                    @endcan
                </x-slot>
            </x-page-header>

            <x-card class="p-6">
                <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <dt class="text-xs text-slate-500">Status</dt>
                        <dd class="mt-1"><x-badge :colour="$task->status->colour()">{{ $task->status->label() }}</x-badge></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Priority</dt>
                        <dd class="mt-1"><x-badge :colour="$task->priority->colour()">{{ $task->priority->label() }}</x-badge></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Due</dt>
                        <dd class="text-slate-900">{{ $task->due_at?->toFormattedDateString() ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Completed</dt>
                        <dd class="text-slate-900">{{ $task->completed_at?->toFormattedDateString() ?? '—' }}</dd>
                    </div>
                    @if ($task->description)
                        <div class="col-span-2 md:col-span-4">
                            <dt class="text-xs text-slate-500">Description</dt>
                            <dd class="mt-1 text-slate-800 whitespace-pre-wrap">{{ $task->description }}</dd>
                        </div>
                    @endif
                </dl>
            </x-card>

            @can('assign', $task)
                <x-card class="p-6">
                    <h3 class="text-sm font-semibold text-slate-900 mb-3">Assignment</h3>
                    <form method="POST" action="{{ route('tasks.assign', $task) }}" class="flex items-center gap-3">
                        @csrf
                        @method('PATCH')
                        <select name="assigned_to" class="rounded-md border-slate-300 text-sm">
                            <option value="">Unassigned</option>
                            @foreach ($assignableUsers as $user)
                                <option value="{{ $user->id }}" @selected($task->assigned_to == $user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                        <button class="inline-flex items-center px-3 py-2 bg-slate-900 text-white text-sm rounded-md hover:bg-slate-800">Update</button>
                        <span class="text-sm text-slate-500">Currently: {{ $task->assignee?->name ?? 'Unassigned' }}</span>
                    </form>
                </x-card>
            @endcan
        </div>
    </div>
</x-app-layout>
