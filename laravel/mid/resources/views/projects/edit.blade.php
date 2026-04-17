<x-app-layout>
    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <x-page-header title="Edit project" :subtitle="$project->name" />

            <x-card class="p-6">
                <form method="POST" action="{{ route('projects.update', $project) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    @include('projects._form', [
                        'project' => $project,
                        'clients' => $clients,
                        'statuses' => $statuses,
                    ])
                    <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                        @can('delete', $project)
                            <form method="POST" action="{{ route('projects.destroy', $project) }}"
                                onsubmit="return confirm('Archive this project?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-rose-600 hover:underline">Archive project</button>
                            </form>
                        @else
                            <span></span>
                        @endcan
                        <div class="flex items-center gap-3">
                            <a href="{{ route('projects.show', $project) }}" class="text-sm text-slate-600 hover:underline">Cancel</a>
                            <button class="inline-flex items-center px-3 py-2 bg-slate-900 text-white text-sm rounded-md hover:bg-slate-800">Save</button>
                        </div>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
