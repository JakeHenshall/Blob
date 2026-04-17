<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit client</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-card>
                <form method="POST" action="{{ route('clients.update', $client) }}" class="space-y-6">
                    @csrf
                    @method('PUT')
                    @include('clients._form', ['client' => $client])

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <x-primary-button>Save changes</x-primary-button>
                            <a href="{{ route('clients.show', $client) }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                        </div>
                    </div>
                </form>
            </x-card>

            <x-card>
                <h3 class="text-sm font-semibold text-red-700">Delete client</h3>
                <p class="text-sm text-gray-600 mt-1">This will also delete all projects and tasks belonging to this client.</p>
                <form method="POST" action="{{ route('clients.destroy', $client) }}" class="mt-3"
                    onsubmit="return confirm('Delete this client and all related projects/tasks?');">
                    @csrf
                    @method('DELETE')
                    <x-danger-button>Delete client</x-danger-button>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
