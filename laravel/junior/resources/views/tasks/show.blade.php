<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $task->title }}</h2>
                <p class="text-sm text-gray-500">
                    <a href="{{ route('projects.show', $task->project) }}" class="hover:underline">{{ $task->project->name }}</a>
                    &middot;
                    <a href="{{ route('clients.show', $task->project->client) }}" class="hover:underline">{{ $task->project->client->name }}</a>
                </p>
            </div>
            <a href="{{ route('tasks.edit', $task) }}" class="text-sm text-blue-600 hover:underline">Edit</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            <x-card>
                <dl class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Status</dt>
                        <dd class="mt-1"><x-status-badge :status="$task->status" /></dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Due on</dt>
                        <dd class="text-gray-900">{{ optional($task->due_on)->format('d M Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Completed at</dt>
                        <dd class="text-gray-900">{{ optional($task->completed_at)->format('d M Y H:i') ?? '—' }}</dd>
                    </div>
                    @if ($task->description)
                        <div class="md:col-span-3">
                            <dt class="text-gray-500">Description</dt>
                            <dd class="text-gray-900 whitespace-pre-line">{{ $task->description }}</dd>
                        </div>
                    @endif
                </dl>
            </x-card>
        </div>
    </div>
</x-app-layout>
