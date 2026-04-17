<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach ([
                    ['Clients', $stats['clients'], route('clients.index')],
                    ['Projects', $stats['projects'], route('projects.index')],
                    ['Active projects', $stats['projects_active'], route('projects.index', ['status' => 'active'])],
                    ['Tasks', $stats['tasks'], route('tasks.index')],
                    ['Completed tasks', $stats['tasks_completed'], route('tasks.index', ['status' => 'done'])],
                    ['Overdue tasks', $stats['tasks_overdue'], route('tasks.index')],
                ] as [$label, $value, $href])
                    <a href="{{ $href }}" class="block">
                        <x-card padding="p-4" class="hover:border-gray-300 transition">
                            <div class="text-xs uppercase tracking-wide text-gray-500">{{ $label }}</div>
                            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $value }}</div>
                        </x-card>
                    </a>
                @endforeach
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <x-card>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-900">Upcoming tasks</h3>
                        <a href="{{ route('tasks.index') }}" class="text-sm text-blue-600 hover:underline">View all</a>
                    </div>

                    @forelse ($recentTasks as $task)
                        <div class="flex items-start justify-between py-2 border-b border-gray-100 last:border-0">
                            <div>
                                <a href="{{ route('tasks.show', $task) }}" class="text-sm font-medium text-gray-900 hover:underline">
                                    {{ $task->title }}
                                </a>
                                <div class="text-xs text-gray-500">
                                    {{ $task->project->name }} &middot; {{ $task->project->client->name }}
                                </div>
                            </div>
                            <div class="text-right">
                                <x-status-badge :status="$task->status" />
                                @if ($task->due_on)
                                    <div class="text-xs text-gray-500 mt-1">Due {{ $task->due_on->format('d M Y') }}</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No open tasks. Nice work.</p>
                    @endforelse
                </x-card>

                <x-card>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-900">Recent projects</h3>
                        <a href="{{ route('projects.index') }}" class="text-sm text-blue-600 hover:underline">View all</a>
                    </div>

                    @forelse ($recentProjects as $project)
                        <div class="flex items-start justify-between py-2 border-b border-gray-100 last:border-0">
                            <div>
                                <a href="{{ route('projects.show', $project) }}" class="text-sm font-medium text-gray-900 hover:underline">
                                    {{ $project->name }}
                                </a>
                                <div class="text-xs text-gray-500">{{ $project->client->name }}</div>
                            </div>
                            <x-status-badge :status="$project->status" />
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No projects yet. <a href="{{ route('projects.create') }}" class="text-blue-600 hover:underline">Create one</a>.</p>
                    @endforelse
                </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
