<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            <section>
                <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wide mb-3">Projects by status</h3>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                    @foreach (\App\Enums\ProjectStatus::cases() as $status)
                        <x-card class="p-4">
                            <div class="text-xs text-slate-500">{{ $status->label() }}</div>
                            <div class="mt-1 text-2xl font-semibold text-slate-900">
                                {{ $projectsByStatus[$status->value] ?? 0 }}
                            </div>
                        </x-card>
                    @endforeach
                </div>
            </section>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <x-card class="p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-slate-900">Overdue tasks</h3>
                        <span class="text-xs text-slate-500">{{ $overdueTasks->count() }} shown</span>
                    </div>
                    @forelse ($overdueTasks as $task)
                        <a href="{{ route('tasks.show', $task) }}" class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0 hover:bg-slate-50 px-2 -mx-2 rounded">
                            <div>
                                <div class="text-sm font-medium text-slate-900">{{ $task->title }}</div>
                                <div class="text-xs text-slate-500">
                                    {{ $task->project->name }}
                                    @if ($task->assignee) &middot; {{ $task->assignee->name }} @endif
                                </div>
                            </div>
                            <div class="text-xs text-rose-600 font-medium">
                                Due {{ $task->due_at?->toFormattedDateString() }}
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-slate-500">No overdue tasks. Nice.</p>
                    @endforelse
                </x-card>

                <x-card class="p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-slate-900">Tasks assigned to me</h3>
                        <a href="{{ route('tasks.index', ['mine' => 1]) }}" class="text-xs text-slate-600 hover:underline">View all</a>
                    </div>
                    @forelse ($myTasks as $task)
                        <a href="{{ route('tasks.show', $task) }}" class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0 hover:bg-slate-50 px-2 -mx-2 rounded">
                            <div>
                                <div class="text-sm font-medium text-slate-900">{{ $task->title }}</div>
                                <div class="text-xs text-slate-500">{{ $task->project->name }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-badge :colour="$task->priority->colour()">{{ $task->priority->label() }}</x-badge>
                                @if ($task->due_at)
                                    <span class="text-xs text-slate-500">{{ $task->due_at->toFormattedDateString() }}</span>
                                @endif
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-slate-500">Nothing assigned to you right now.</p>
                    @endforelse
                </x-card>
            </div>

            <x-card class="p-5">
                <h3 class="text-sm font-semibold text-slate-900 mb-3">Recent activity</h3>
                @forelse ($recentActivity as $activity)
                    <div class="flex items-start gap-3 py-2 border-b border-slate-100 last:border-0">
                        <div class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center text-xs font-semibold text-slate-600">
                            {{ strtoupper(mb_substr($activity->causer?->name ?? '?', 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <div class="text-sm text-slate-900">
                                <span class="font-medium">{{ $activity->causer?->name ?? 'System' }}</span>
                                <span class="text-slate-600">{{ $activity->description ?? $activity->action }}</span>
                            </div>
                            <div class="text-xs text-slate-500 mt-0.5">
                                {{ $activity->created_at->diffForHumans() }} &middot; {{ $activity->action }}
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No activity yet.</p>
                @endforelse
            </x-card>
        </div>
    </div>
</x-app-layout>
