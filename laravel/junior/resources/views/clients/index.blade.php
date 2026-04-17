<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Clients</h2>
            <a href="{{ route('clients.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-xs uppercase tracking-widest rounded-md hover:bg-gray-700">
                New client
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <x-card padding="p-4">
                <form method="GET" class="flex flex-col sm:flex-row gap-2">
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Search name, company, email"
                        class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-xs uppercase tracking-widest rounded-md hover:bg-gray-700">
                        Search
                    </button>
                    @if ($search !== '')
                        <a href="{{ route('clients.index') }}" class="px-4 py-2 text-xs uppercase tracking-widest text-gray-700 hover:text-gray-900 self-center">
                            Clear
                        </a>
                    @endif
                </form>
            </x-card>

            <x-card padding="p-0">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Projects</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($clients as $client)
                            <tr>
                                <td class="px-6 py-3 text-sm font-medium text-gray-900">
                                    <a href="{{ route('clients.show', $client) }}" class="hover:underline">{{ $client->name }}</a>
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-600">{{ $client->company ?? '—' }}</td>
                                <td class="px-6 py-3 text-sm text-gray-600">{{ $client->email ?? '—' }}</td>
                                <td class="px-6 py-3 text-sm text-gray-600">{{ $client->projects_count }}</td>
                                <td class="px-6 py-3 text-right text-sm">
                                    <a href="{{ route('clients.edit', $client) }}" class="text-blue-600 hover:underline">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-6 text-center text-sm text-gray-500">
                                    No clients found. <a href="{{ route('clients.create') }}" class="text-blue-600 hover:underline">Add your first one</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-card>

            <div>{{ $clients->links() }}</div>
        </div>
    </div>
</x-app-layout>
