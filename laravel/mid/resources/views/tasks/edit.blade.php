<x-app-layout>
    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <x-page-header title="Edit task" :subtitle="$task->title" />

            <x-card class="p-6">
                <form method="POST" action="{{ route('tasks.update', $task) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    @include('tasks._form', [
                        'task' => $task,
                        'statuses' => $statuses,
                        'priorities' => $priorities,
                        'assignableUsers' => $assignableUsers,
                        'projectFixed' => true,
                    ])
                    <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                        @can('delete', $task)
                            <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Archive this task?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-sm text-rose-600 hover:underline">Archive task</button>
                            </form>
                        @else
                            <span></span>
                        @endcan
                        <div class="flex items-center gap-3">
                            <a href="{{ route('tasks.show', $task) }}" class="text-sm text-slate-600 hover:underline">Cancel</a>
                            <button class="inline-flex items-center px-3 py-2 bg-slate-900 text-white text-sm rounded-md hover:bg-slate-800">Save</button>
                        </div>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
