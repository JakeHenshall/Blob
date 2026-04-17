<?php

namespace App\Http\Controllers;

use App\Actions\Clients\ArchiveClientAction;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Client::class);

        $query = Client::query()
            ->with('owner')
            ->withCount('projects')
            ->search($request->string('q')->toString() ?: null);

        if ($request->string('status')->toString() === 'archived') {
            $query->archived();
        } else {
            $query->active();
        }

        $user = $request->user();
        if (! $user->isManager()) {
            $query->where('owner_id', $user->id);
        }

        $sort = $request->string('sort')->toString() ?: 'created_at';
        $dir = $request->string('dir')->toString() === 'asc' ? 'asc' : 'desc';
        if (! in_array($sort, ['created_at', 'name'], true)) {
            $sort = 'created_at';
        }

        $clients = $query->orderBy($sort, $dir)->paginate(15)->withQueryString();

        return view('clients.index', compact('clients'));
    }

    public function create(): View
    {
        $this->authorize('create', Client::class);

        return view('clients.create');
    }

    public function store(StoreClientRequest $request, ActivityLogger $log): RedirectResponse
    {
        $data = $request->validated();

        $client = Client::create([
            ...$data,
            'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(4)),
            'owner_id' => $request->user()->id,
        ]);

        $log->record($request->user(), 'client.created', $client, ['name' => $client->name]);

        return redirect()->route('clients.show', $client)->with('status', 'Client created.');
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

    public function update(UpdateClientRequest $request, Client $client, ActivityLogger $log): RedirectResponse
    {
        $client->update($request->validated());

        $log->record($request->user(), 'client.updated', $client);

        return redirect()->route('clients.show', $client)->with('status', 'Client updated.');
    }

    public function archive(Client $client, ArchiveClientAction $action, Request $request): RedirectResponse
    {
        $this->authorize('archive', $client);

        $action->execute($request->user(), $client);

        return redirect()->route('clients.index')->with('status', 'Client archived.');
    }

    public function destroy(Client $client, ActivityLogger $log, Request $request): RedirectResponse
    {
        $this->authorize('delete', $client);

        $client->delete();
        $log->record($request->user(), 'client.deleted', $client);

        return redirect()->route('clients.index')->with('status', 'Client deleted.');
    }
}
