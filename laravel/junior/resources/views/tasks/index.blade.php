<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tasks</h2>
            <a href="{{ route('tasks.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-xs uppercase tracking-widest rounded-md hover:bg-gray-700">
                New task
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <x-card padding="p-4">
                <form method="GET" class="flex flex-col sm:flex-row gap-2">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search task title"
                        class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                    <select name="status"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $s)
                            <option value="{{ $s }}" @selected($status === $s)>
                                {{ ucwords(str_replace('_', ' ', $s)) }}
                            </option>
                        @endforeach
                    </select>
                    <button class="px-4 py-2 bg-gray-800 text-white text-xs uppercase tracking-widest rounded-md hover:bg-gray-700">Filter</button>
                    @if ($search !== '' || $status !== '')
                        <a href="{{ route('tasks.index') }}" class="px-4 py-2 text-xs uppercase tracking-widest text-gray-700 self-center">Clear</a>
                    @endif
                </form>
            </x-card>

            <x-card padding="p-0">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($tasks as $task)
                            @php($overdue = $task->due_on && $task->due_on->isPast() && $task->status !== 'done')
                            <tr class="{{ $overdue ? 'bg-red-50/40' : '' }}">
                                <td class="px-6 py-3 text-sm font-medium text-gray-900">
                                    <a href="{{ route('tasks.show', $task) }}" class="hover:underline">{{ $task->title }}</a>
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-600">
                                    <a href="{{ route('projects.show', $task->project) }}" class="hover:underline">{{ $task->project->name }}</a>
                                    <div class="text-xs text-gray-500">{{ $task->project->client->name }}</div>
                                </td>
                                <td class="px-6 py-3"><x-status-badge :status="$task->status" /></td>
                                <td class="px-6 py-3 text-sm {{ $overdue ? 'text-red-700 font-medium' : 'text-gray-600' }}">
                                    {{ $task->due_on ? $task->due_on->format('d M Y') : '—' }}
                                </td>
                                <td class="px-6 py-3 text-right text-sm">
                                    <a href="{{ route('tasks.edit', $task) }}" class="text-blue-600 hover:underline">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-6 text-center text-sm text-gray-500">No tasks found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-card>

            <div>{{ $tasks->links() }}</div>
        </div>
    </div>
</x-app-layout>
