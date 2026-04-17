<x-app-layout>
    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <x-page-header title="Edit client" :subtitle="$client->name" />

            <x-card class="p-6">
                <form method="POST" action="{{ route('clients.update', $client) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    @include('clients._form', ['client' => $client])
                    <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                        @can('delete', $client)
                            <form method="POST" action="{{ route('clients.destroy', $client) }}"
                                onsubmit="return confirm('Archive this client?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-rose-600 hover:underline">Archive client</button>
                            </form>
                        @else
                            <span></span>
                        @endcan
                        <div class="flex items-center gap-3">
                            <a href="{{ route('clients.show', $client) }}" class="text-sm text-slate-600 hover:underline">Cancel</a>
                            <button class="inline-flex items-center px-3 py-2 bg-slate-900 text-white text-sm rounded-md hover:bg-slate-800">Save</button>
                        </div>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
