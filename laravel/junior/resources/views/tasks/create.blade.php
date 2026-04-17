<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">New task</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <x-card>
                <form method="POST" action="{{ route('tasks.store') }}" class="space-y-6">
                    @csrf
                    @include('tasks._form', [
                        'projects' => $projects,
                        'statuses' => $statuses,
                        'preselectedProjectId' => $preselectedProjectId,
                    ])

                    <div class="flex items-center gap-3">
                        <x-primary-button>Create task</x-primary-button>
                        <a href="{{ route('tasks.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
