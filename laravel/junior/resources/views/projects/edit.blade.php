<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit project</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-card>
                <form method="POST" action="{{ route('projects.update', $project) }}" class="space-y-6">
                    @csrf
                    @method('PUT')
                    @include('projects._form', [
                        'project' => $project,
                        'clients' => $clients,
                        'statuses' => $statuses,
                    ])

                    <div class="flex items-center gap-3">
                        <x-primary-button>Save changes</x-primary-button>
                        <a href="{{ route('projects.show', $project) }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                    </div>
                </form>
            </x-card>

            <x-card>
                <h3 class="text-sm font-semibold text-red-700">Delete project</h3>
                <p class="text-sm text-gray-600 mt-1">This will also delete its tasks.</p>
                <form method="POST" action="{{ route('projects.destroy', $project) }}" class="mt-3"
                    onsubmit="return confirm('Delete this project and its tasks?');">
                    @csrf
                    @method('DELETE')
                    <x-danger-button>Delete project</x-danger-button>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
