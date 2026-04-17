<x-app-layout>
    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <x-page-header title="New project" />

            <x-card class="p-6">
                <form method="POST" action="{{ route('projects.store') }}" class="space-y-4">
                    @csrf
                    @include('projects._form', [
                        'clients' => $clients,
                        'statuses' => $statuses,
                        'preselectedClientId' => $preselectedClientId,
                    ])
                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <a href="{{ route('projects.index') }}" class="text-sm text-slate-600 hover:underline">Cancel</a>
                        <button class="inline-flex items-center px-3 py-2 bg-slate-900 text-white text-sm rounded-md hover:bg-slate-800">Create project</button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
