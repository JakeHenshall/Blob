<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash />

            <x-page-header title="Projects" subtitle="All engagements across your clients.">
                <x-slot name="actions">
                    @can('create', \App\Models\Project::class)
                        <a href="{{ route('projects.create') }}" class="inline-flex items-center px-3 py-2 bg-slate-900 text-white text-sm rounded-md hover:bg-slate-800">New project</a>
                    @endcan
                </x-slot>
            </x-page-header>

            <x-card class="p-4 mb-4">
                <form method="GET" action="{{ route('projects.index') }}" class="flex flex-wrap items-end gap-3">
                    <div class="flex-1 min-w-[220px]">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Search</label>
                        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="w-full rounded-md border-slate-300 text-sm" placeholder="Project name or description">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                        <select name="status" class="rounded-md border-slate-300 text-sm">
                            <option value="">All</option>
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Sort</label>
                        <select name="sort" class="rounded-md border-slate-300 text-sm">
                            <option value="due_at" @selected(($filters['sort'] ?? '') === 'due_at')>Due date</option>
                            <option value="name" @selected(($filters['sort'] ?? '') === 'name')>Name</option>
                            <option value="status" @selected(($filters['sort'] ?? '') === 'status')>Status</option>
                            <option value="created_at" @selected(($filters['sort'] ?? '') === 'created_at')>Created</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Direction</label>
                        <select name="direction" class="rounded-md border-slate-300 text-sm">
                            <option value="asc" @selected(($filters['direction'] ?? '') === 'asc')>Asc</option>
                            <option value="desc" @selected(($filters['direction'] ?? '') === 'desc')>Desc</option>
                        </select>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="due_soon" value="1" @checked(! empty($filters['due_soon'])) class="rounded border-slate-300">
                        Due soon
                    </label>
                    <button class="inline-flex items-center px-3 py-2 bg-slate-800 text-white text-sm rounded-md hover:bg-slate-700">Apply</button>
                    <a href="{{ route('projects.index') }}" class="text-sm text-slate-600 hover:underline">Reset</a>
                </form>
            </x-card>

            <x-card>
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-slate-600">Project</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-600">Client</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-600">Status</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-600">Tasks</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-600">Due</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-600">Owner</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($projects as $project)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <a href="{{ route('projects.show', $project) }}" class="font-medium text-slate-900 hover:underline">{{ $project->name }}</a>
                                </td>
                                <td class="px-4 py-3 text-slate-700">{{ $project->client?->name }}</td>
                                <td class="px-4 py-3"><x-badge :colour="$project->status->colour()">{{ $project->status->label() }}</x-badge></td>
                                <td class="px-4 py-3 text-slate-700">{{ $project->open_tasks_count }}/{{ $project->tasks_count }} open</td>
                                <td class="px-4 py-3 text-slate-700">{{ $project->due_at?->toFormattedDateString() ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $project->owner?->name }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">No projects found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </x-card>

            <div class="mt-4">{{ $projects->links() }}</div>
        </div>
    </div>
</x-app-layout>
