<x-app-layout>
    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            <x-page-header :title="$client->name" :subtitle="$client->company">
                <x-slot name="actions">
                    @can('update', $client)
                        <a href="{{ route('clients.edit', $client) }}" class="inline-flex items-center px-3 py-2 bg-white border border-slate-300 text-slate-800 text-sm rounded-md hover:bg-slate-100">Edit</a>
                    @endcan
                    @can('create', \App\Models\Project::class)
                        <a href="{{ route('projects.create', ['client_id' => $client->id]) }}" class="inline-flex items-center px-3 py-2 bg-slate-900 text-white text-sm rounded-md hover:bg-slate-800">New project</a>
                    @endcan
                </x-slot>
            </x-page-header>

            <x-card class="p-6">
                <dl class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <dt class="text-xs text-slate-500">Email</dt>
                        <dd class="text-slate-900">{{ $client->email ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Phone</dt>
                        <dd class="text-slate-900">{{ $client->phone ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">Owner</dt>
                        <dd class="text-slate-900">{{ $client->owner?->name }}</dd>
                    </div>
                    @if ($client->notes)
                        <div class="md:col-span-3">
                            <dt class="text-xs text-slate-500">Notes</dt>
                            <dd class="mt-1 text-slate-800 whitespace-pre-wrap">{{ $client->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </x-card>

            <x-card>
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-900">Projects</h3>
                    <span class="text-xs text-slate-500">{{ $client->projects->count() }} total</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($client->projects as $project)
                        <a href="{{ route('projects.show', $project) }}" class="flex items-center justify-between px-6 py-3 hover:bg-slate-50">
                            <div>
                                <div class="font-medium text-slate-900">{{ $project->name }}</div>
                                <div class="text-xs text-slate-500">
                                    @if ($project->due_at) Due {{ $project->due_at->toFormattedDateString() }} @endif
                                </div>
                            </div>
                            <x-badge :colour="$project->status->colour()">{{ $project->status->label() }}</x-badge>
                        </a>
                    @empty
                        <div class="px-6 py-8 text-center text-slate-500 text-sm">No projects yet.</div>
                    @endforelse
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
