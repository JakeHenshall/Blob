<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $client->name }}</h2>
            <a href="{{ route('clients.edit', $client) }}" class="text-sm text-blue-600 hover:underline">Edit</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            <x-card>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Company</dt>
                        <dd class="text-gray-900">{{ $client->company ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Email</dt>
                        <dd class="text-gray-900">{{ $client->email ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Phone</dt>
                        <dd class="text-gray-900">{{ $client->phone ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Created</dt>
                        <dd class="text-gray-900">{{ $client->created_at->format('d M Y') }}</dd>
                    </div>
                    @if ($client->notes)
                        <div class="md:col-span-2">
                            <dt class="text-gray-500">Notes</dt>
                            <dd class="text-gray-900 whitespace-pre-line">{{ $client->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </x-card>

            <x-card>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900">Projects</h3>
                    <a href="{{ route('projects.create') }}?client_id={{ $client->id }}" class="text-sm text-blue-600 hover:underline">New project</a>
                </div>

                @forelse ($client->projects as $project)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                        <div>
                            <a href="{{ route('projects.show', $project) }}" class="text-sm font-medium text-gray-900 hover:underline">
                                {{ $project->name }}
                            </a>
                            <div class="text-xs text-gray-500">{{ $project->tasks_count }} tasks</div>
                        </div>
                        <x-status-badge :status="$project->status" />
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No projects yet for this client.</p>
                @endforelse
            </x-card>
        </div>
    </div>
</x-app-layout>
