@extends('layouts.app')
@section('title', 'Clients')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Clients</h1>
        @can('create', App\Models\Client::class)
            <a href="{{ route('clients.create') }}" class="btn-primary">New client</a>
        @endcan
    </div>

    <form method="GET" class="card p-4 flex flex-wrap gap-3 items-end mb-4">
        <div>
            <label class="label" for="q">Search</label>
            <input id="q" name="q" value="{{ request('q') }}" class="input" placeholder="Name or email">
        </div>
        <div>
            <label class="label" for="status">Status</label>
            <select id="status" name="status" class="input">
                <option value="">Active</option>
                <option value="archived" @selected(request('status') === 'archived')>Archived</option>
            </select>
        </div>
        <div>
            <label class="label" for="sort">Sort</label>
            <select id="sort" name="sort" class="input">
                <option value="created_at" @selected(request('sort') === 'created_at')>Newest</option>
                <option value="name" @selected(request('sort') === 'name')>Name</option>
            </select>
        </div>
        <button class="btn-secondary">Filter</button>
    </form>

    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-4 py-2 text-left">Name</th>
                    <th class="px-4 py-2 text-left">Owner</th>
                    <th class="px-4 py-2 text-left">Projects</th>
                    <th class="px-4 py-2 text-left">Created</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($clients as $client)
                    <tr>
                        <td class="px-4 py-2">
                            <a href="{{ route('clients.show', $client) }}" class="font-medium text-slate-800 hover:underline">{{ $client->name }}</a>
                            @if ($client->isArchived())
                                <span class="badge bg-amber-100 text-amber-800 ml-2">Archived</span>
                            @endif
                            <div class="text-slate-500 text-xs">{{ $client->contact_email }}</div>
                        </td>
                        <td class="px-4 py-2 text-slate-600">{{ $client->owner?->name }}</td>
                        <td class="px-4 py-2 text-slate-600">{{ $client->projects_count }}</td>
                        <td class="px-4 py-2 text-slate-500">{{ $client->created_at?->format('d M Y') }}</td>
                        <td class="px-4 py-2 text-right">
                            <a href="{{ route('clients.show', $client) }}" class="text-brand-600 hover:underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">No clients found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $clients->links() }}</div>
@endsection
