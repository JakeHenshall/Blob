<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash />

            <x-page-header title="Tasks" subtitle="Work items across projects.">
                <x-slot name="actions">
                    @can('create', \App\Models\Task::class)
                        <a href="{{ route('tasks.create') }}" class="inline-flex items-center px-3 py-2 bg-slate-900 text-white text-sm rounded-md hover:bg-slate-800">New task</a>
                    @endcan
                </x-slot>
            </x-page-header>

            <x-card class="p-4 mb-4">
                <form method="GET" action="{{ route('tasks.index') }}" class="flex flex-wrap items-end gap-3">
                    <div class="flex-1 min-w-[220px]">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Search</label>
                        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="w-full rounded-md border-slate-300 text-sm" placeholder="Title or description">
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
                        <label class="block text-xs font-medium text-slate-600 mb-1">Assignee</label>
                        <select name="assigned_to" class="rounded-md border-slate-300 text-sm">
                            <option value="">Anyone</option>
                            @foreach ($assignableUsers as $user)
                                <option value="{{ $user->id }}" @selected(($filters['assigned_to'] ?? null) == $user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="due_soon" value="1" @checked(! empty($filters['due_soon'])) class="rounded border-slate-300">
                        Due soon
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="mine" value="1" @checked(! empty($filters['mine'])) class="rounded border-slate-300">
                        Assigned to me
                    </label>
                    <button class="inline-flex items-center px-3 py-2 bg-slate-800 text-white text-sm rounded-md hover:bg-slate-700">Apply</button>
                    <a href="{{ route('tasks.index') }}" class="text-sm text-slate-600 hover:underline">Reset</a>
                </form>
            </x-card>

            <x-card>
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-slate-600">Task</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-600">Project</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-600">Status</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-600">Priority</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-600">Assignee</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-600">Due</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($tasks as $task)
                            <tr class="hover:bg-slate-50 {{ $task->isOverdue() ? 'bg-rose-50/40' : '' }}">
                                <td class="px-4 py-3">
                                    <a href="{{ route('tasks.show', $task) }}" class="font-medium text-slate-900 hover:underline">{{ $task->title }}</a>
                                </td>
                                <td class="px-4 py-3 text-slate-700">{{ $task->project?->name }}</td>
                                <td class="px-4 py-3"><x-badge :colour="$task->status->colour()">{{ $task->status->label() }}</x-badge></td>
                                <td class="px-4 py-3"><x-badge :colour="$task->priority->colour()">{{ $task->priority->label() }}</x-badge></td>
                                <td class="px-4 py-3 text-slate-700">{{ $task->assignee?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $task->due_at?->toFormattedDateString() ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">No tasks found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </x-card>

            <div class="mt-4">{{ $tasks->links() }}</div>
        </div>
    </div>
</x-app-layout>
