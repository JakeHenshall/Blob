<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $project->name }}</h2>
                <p class="text-sm text-gray-500">
                    <a href="{{ route('clients.show', $project->client) }}" class="hover:underline">{{ $project->client->name }}</a>
                </p>
            </div>
            <a href="{{ route('projects.edit', $project) }}" class="text-sm text-blue-600 hover:underline">Edit</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            <x-card>
                <dl class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Status</dt>
                        <dd class="mt-1"><x-status-badge :status="$project->status" /></dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Starts</dt>
                        <dd class="text-gray-900">{{ optional($project->starts_on)->format('d M Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Ends</dt>
                        <dd class="text-gray-900">{{ optional($project->ends_on)->format('d M Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Tasks</dt>
                        <dd class="text-gray-900">{{ $project->tasks->count() }}</dd>
                    </div>
                    @if ($project->description)
                        <div class="md:col-span-4">
                            <dt class="text-gray-500">Description</dt>
                            <dd class="text-gray-900 whitespace-pre-line">{{ $project->description }}</dd>
                        </div>
                    @endif
                </dl>
            </x-card>

            <x-card>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900">Tasks</h3>
                    <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}" class="text-sm text-blue-600 hover:underline">New task</a>
                </div>

                @forelse ($project->tasks as $task)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                        <div>
                            <a href="{{ route('tasks.show', $task) }}" class="text-sm font-medium text-gray-900 hover:underline">
                                {{ $task->title }}
                            </a>
                            @if ($task->due_on)
                                <div class="text-xs text-gray-500">Due {{ $task->due_on->format('d M Y') }}</div>
                            @endif
                        </div>
                        <x-status-badge :status="$task->status" />
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No tasks yet for this project.</p>
                @endforelse
            </x-card>
        </div>
    </div>
</x-app-layout>
