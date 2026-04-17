<x-app-layout>
    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            <x-page-header :title="$project->name">
                <x-slot name="subtitle">
                    <a href="{{ route('clients.show', $project->client) }}" class="hover:underline">{{ $project->client?->name }}</a>
                </x-slot>
                <x-slot name="actions">
                    <x-badge :colour="$project->status->colour()">{{ $project->status->label() }}</x-badge>
                    @can('update', $project)
                        <a href="{{ route('projects.edit', $project) }}" class="inline-flex items-center px-3 py-2 bg-white border border-slate-300 text-slate-800 text-sm rounded-md hover:bg-slate-100">Edit</a>
                    @endcan
                    @can('create', \App\Models\Task::class)
                        <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}" class="inline-flex items-center px-3 py-2 bg-slate-900 text-white text-sm rounded-md hover:bg-slate-800">New task</a>
                    @endcan
                </x-slot>
            </x-page-header>

            <x-card class="p-6">
                <dl class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <dt class="text-xs text-slate-500">Owner</dt>
                        <dd class="text-slate-900">{{ $project->owner?->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Starts</dt>
                        <dd class="text-slate-900">{{ $project->starts_at?->toFormattedDateString() ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Due</dt>
                        <dd class="text-slate-900">{{ $project->due_at?->toFormattedDateString() ?? '—' }}</dd>
                    </div>
                    @if ($project->description)
                        <div class="md:col-span-3">
                            <dt class="text-xs text-slate-500">Description</dt>
                            <dd class="mt-1 text-slate-800 whitespace-pre-wrap">{{ $project->description }}</dd>
                        </div>
                    @endif
                </dl>
            </x-card>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <x-card>
                        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                            <h3 class="font-semibold text-slate-900">Tasks</h3>
                            <span class="text-xs text-slate-500">{{ $project->tasks->count() }} total</span>
                        </div>
                        <div class="divide-y divide-slate-100">
                            @forelse ($project->tasks as $task)
                                <div class="px-6 py-3 flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <a href="{{ route('tasks.show', $task) }}" class="font-medium text-slate-900 hover:underline">{{ $task->title }}</a>
                                        <div class="mt-1 flex items-center gap-2 text-xs text-slate-500">
                                            <x-badge :colour="$task->status->colour()">{{ $task->status->label() }}</x-badge>
                                            <x-badge :colour="$task->priority->colour()">{{ $task->priority->label() }}</x-badge>
                                            @if ($task->assignee)<span>&middot; {{ $task->assignee->name }}</span>@endif
                                            @if ($task->due_at)<span>&middot; Due {{ $task->due_at->toFormattedDateString() }}</span>@endif
                                        </div>
                                    </div>
                                    @can('complete', $task)
                                        @if ($task->status->isOpen())
                                            <form method="POST" action="{{ route('tasks.complete', $task) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="text-xs text-emerald-700 hover:underline">Complete</button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            @empty
                                <div class="px-6 py-8 text-center text-slate-500 text-sm">No tasks yet.</div>
                            @endforelse
                        </div>
                    </x-card>
                </div>

                <div>
                    <x-card>
                        <div class="px-5 py-4 border-b border-slate-100">
                            <h3 class="font-semibold text-slate-900">Notes</h3>
                        </div>
                        @can('addNote', $project)
                            <form method="POST" action="{{ route('projects.notes.store', $project) }}" class="px-5 pt-4 space-y-2">
                                @csrf
                                <textarea name="body" rows="3" placeholder="Add a note..." class="w-full rounded-md border-slate-300 text-sm"></textarea>
                                <x-form-error name="body" />
                                <div class="flex justify-end">
                                    <button class="inline-flex items-center px-3 py-1.5 bg-slate-900 text-white text-xs rounded-md hover:bg-slate-800">Add note</button>
                                </div>
                            </form>
                        @endcan
                        <div class="divide-y divide-slate-100 mt-2">
                            @forelse ($project->notes as $note)
                                <div class="px-5 py-3">
                                    <div class="text-sm text-slate-800 whitespace-pre-wrap">{{ $note->body }}</div>
                                    <div class="mt-1 flex items-center justify-between text-xs text-slate-500">
                                        <span>{{ $note->author?->name }} &middot; {{ $note->created_at->diffForHumans() }}</span>
                                        @can('delete', $note)
                                            <form method="POST" action="{{ route('projects.notes.destroy', [$project, $note]) }}" onsubmit="return confirm('Delete this note?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-rose-600 hover:underline">Delete</button>
                                            </form>
                                        @endcan
                                    </div>
                                </div>
                            @empty
                                <div class="px-5 py-6 text-center text-slate-500 text-sm">No notes yet.</div>
                            @endforelse
                        </div>
                    </x-card>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
