<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash />

            <x-page-header title="Clients" subtitle="Everyone you work with.">
                <x-slot name="actions">
                    @can('create', \App\Models\Client::class)
                        <a href="{{ route('clients.create') }}" class="inline-flex items-center px-3 py-2 bg-slate-900 text-white text-sm rounded-md hover:bg-slate-800">New client</a>
                    @endcan
                </x-slot>
            </x-page-header>

            <x-card class="p-4 mb-4">
                <form method="GET" action="{{ route('clients.index') }}" class="flex flex-wrap items-end gap-3">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Search</label>
                        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}"
                            class="w-full rounded-md border-slate-300 focus:border-slate-500 focus:ring-slate-500 text-sm"
                            placeholder="Name, company or email">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Sort</label>
                        <select name="sort" class="rounded-md border-slate-300 text-sm">
                            <option value="name" @selected(($filters['sort'] ?? '') === 'name')>Name</option>
                            <option value="company" @selected(($filters['sort'] ?? '') === 'company')>Company</option>
                            <option value="created_at" @selected(($filters['sort'] ?? '') === 'created_at')>Created</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Direction</label>
                        <select name="direction" class="rounded-md border-slate-300 text-sm">
                            <option value="asc" @selected(($filters['direction'] ?? '') === 'asc')>Asc</option>
                            <option value="desc" @selected(($filters['direction'] ?? '') === 'desc')>Desc</option>
                        </select>
                    </div>
                    <button class="inline-flex items-center px-3 py-2 bg-slate-800 text-white text-sm rounded-md hover:bg-slate-700">Apply</button>
                    <a href="{{ route('clients.index') }}" class="text-sm text-slate-600 hover:underline">Reset</a>
                </form>
            </x-card>

            <x-card>
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-slate-600">Name</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-600">Company</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-600">Owner</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-600">Projects</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($clients as $client)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <a href="{{ route('clients.show', $client) }}" class="font-medium text-slate-900 hover:underline">{{ $client->name }}</a>
                                    @if ($client->email)
                                        <div class="text-xs text-slate-500">{{ $client->email }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-700">{{ $client->company ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $client->owner?->name }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $client->projects_count }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('clients.show', $client) }}" class="text-sm text-slate-700 hover:underline">Open</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-slate-500">No clients found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-card>

            <div class="mt-4">{{ $clients->links() }}</div>
        </div>
    </div>
</x-app-layout>
