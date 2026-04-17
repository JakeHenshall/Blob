<?php

namespace App\Http\Controllers;

use App\Actions\Projects\CreateProjectAction;
use App\Enums\ProjectStatus;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Project::class);

        $user = $request->user();
        $sort = $request->input('sort', 'due_at');
        $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        $sortable = ['name', 'status', 'due_at', 'created_at'];
        if (! in_array($sort, $sortable, true)) {
            $sort = 'due_at';
        }

        $projects = Project::query()
            ->with(['client', 'owner'])
            ->withCount(['tasks', 'tasks as open_tasks_count' => function ($q) {
                $q->whereIn('status', ['todo', 'in_progress']);
            }])
            ->search($request->input('q'))
            ->status($request->input('status'))
            ->when($request->boolean('due_soon'), fn ($q) => $q->dueSoon())
            ->when(! $user->isStaff(), function ($q) use ($user) {
                $q->where(function ($sub) use ($user) {
                    $sub->where('user_id', $user->id)
                        ->orWhereHas('tasks', fn ($t) => $t->where('assigned_to', $user->id));
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('projects.index', [
            'projects' => $projects,
            'filters' => $request->only(['q', 'status', 'due_soon', 'sort', 'direction']),
            'statuses' => ProjectStatus::options(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Project::class);

        return view('projects.create', [
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'statuses' => ProjectStatus::options(),
            'preselectedClientId' => $request->integer('client_id') ?: null,
        ]);
    }

    public function store(StoreProjectRequest $request, CreateProjectAction $action): RedirectResponse
    {
        $project = $action->handle($request->user(), $request->validated());

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Project created.');
    }

    public function show(Project $project): View
    {
        $this->authorize('view', $project);

        $project->load([
            'client',
            'owner',
            'tasks' => fn ($q) => $q->with('assignee')->latest(),
            'notes.author',
        ]);

        return view('projects.show', [
            'project' => $project,
            'assignableUsers' => User::orderBy('name')->get(['id', 'name', 'role']),
        ]);
    }

    public function edit(Project $project): View
    {
        $this->authorize('update', $project);

        return view('projects.edit', [
            'project' => $project,
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'statuses' => ProjectStatus::options(),
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->validated());

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Project updated.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('status', 'Project archived.');
    }
}
