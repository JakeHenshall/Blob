<?php

namespace App\Http\Controllers;

use App\Actions\Projects\CreateProjectAction;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Client;
use App\Models\Project;
use App\Services\ActivityLogger;
use App\Support\ProjectStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Project::class);

        $user = $request->user();

        $query = Project::query()
            ->with(['client', 'owner'])
            ->withCount(['tasks', 'tasks as open_tasks_count' => fn ($q) => $q->whereNull('completed_at')])
            ->search($request->string('q')->toString() ?: null)
            ->ofStatus($request->string('status')->toString() ?: null);

        if (! $user->isManager()) {
            $query->where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                    ->orWhereHas('tasks', fn ($t) => $t->where('assignee_id', $user->id));
            });
        }

        $sort = $request->string('sort')->toString() ?: 'created_at';
        $dir = $request->string('dir')->toString() === 'asc' ? 'asc' : 'desc';
        if (! in_array($sort, ['created_at', 'name', 'due_on'], true)) {
            $sort = 'created_at';
        }

        $projects = $query->orderBy($sort, $dir)->paginate(15)->withQueryString();
        $statuses = ProjectStatus::cases();

        return view('projects.index', compact('projects', 'statuses'));
    }

    public function create(Client $client): View
    {
        $this->authorize('create', Project::class);

        return view('projects.create', compact('client'));
    }

    public function store(StoreProjectRequest $request, Client $client, CreateProjectAction $action): RedirectResponse
    {
        $project = $action->execute($request->user(), $client, $request->validated());

        return redirect()->route('projects.show', $project)->with('status', 'Project created.');
    }

    public function show(Project $project): View
    {
        $this->authorize('view', $project);

        $project->load([
            'client',
            'owner',
            'tasks' => fn ($q) => $q->with('assignee')->orderByRaw('completed_at IS NULL DESC')->latest(),
            'notes.author',
            'files.uploader',
        ]);

        return view('projects.show', compact('project'));
    }

    public function edit(Project $project): View
    {
        $this->authorize('update', $project);

        $statuses = ProjectStatus::cases();

        return view('projects.edit', compact('project', 'statuses'));
    }

    public function update(UpdateProjectRequest $request, Project $project, ActivityLogger $log): RedirectResponse
    {
        $project->update($request->validated());
        $log->record($request->user(), 'project.updated', $project);

        return redirect()->route('projects.show', $project)->with('status', 'Project updated.');
    }

    public function destroy(Project $project, ActivityLogger $log, Request $request): RedirectResponse
    {
        $this->authorize('delete', $project);
        $project->delete();
        $log->record($request->user(), 'project.deleted', $project);

        return redirect()->route('projects.index')->with('status', 'Project deleted.');
    }
}
