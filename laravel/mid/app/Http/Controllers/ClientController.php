<?php

namespace App\Http\Controllers;

use App\Actions\Clients\CreateClientAction;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Client::class);

        $user = $request->user();
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        $sortable = ['name', 'company', 'created_at'];
        if (! in_array($sort, $sortable, true)) {
            $sort = 'name';
        }

        $clients = Client::query()
            ->with('owner')
            ->withCount('projects')
            ->search($request->input('q'))
            ->when(! $user->isStaff(), fn ($q) => $q->where('user_id', $user->id))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('clients.index', [
            'clients' => $clients,
            'filters' => $request->only(['q', 'sort', 'direction']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Client::class);

        return view('clients.create');
    }

    public function store(StoreClientRequest $request, CreateClientAction $action): RedirectResponse
    {
        $client = $action->handle($request->user(), $request->validated());

        return redirect()
            ->route('clients.show', $client)
            ->with('status', 'Client created.');
    }

    public function show(Client $client): View
    {
        $this->authorize('view', $client);

        $client->load(['owner', 'projects' => fn ($q) => $q->latest()]);

        return view('clients.show', compact('client'));
    }

    public function edit(Client $client): View
    {
        $this->authorize('update', $client);

        return view('clients.edit', compact('client'));
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $client->update($request->validated());

        return redirect()
            ->route('clients.show', $client)
            ->with('status', 'Client updated.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);

        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('status', 'Client archived.');
    }
}
