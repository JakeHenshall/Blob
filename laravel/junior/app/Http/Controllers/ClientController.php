<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $search = (string) $request->query('search', '');

        $clients = $request->user()
            ->clients()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->withCount('projects')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('clients.index', [
            'clients' => $clients,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('clients.create');
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $client = $request->user()->clients()->create($request->validated());

        return redirect()
            ->route('clients.show', $client)
            ->with('status', 'Client created.');
    }

    public function show(Client $client): View
    {
        $this->authorizeClient($client);

        $client->load(['projects' => fn ($q) => $q->withCount('tasks')->latest()]);

        return view('clients.show', ['client' => $client]);
    }

    public function edit(Client $client): View
    {
        $this->authorizeClient($client);

        return view('clients.edit', ['client' => $client]);
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $client->update($request->validated());

        return redirect()
            ->route('clients.show', $client)
            ->with('status', 'Client updated.');
    }

    public function destroy(Request $request, Client $client): RedirectResponse
    {
        $this->authorizeClient($client);

        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('status', 'Client deleted.');
    }

    private function authorizeClient(Client $client): void
    {
        abort_unless($client->user_id === request()->user()?->id, 403);
    }
}
