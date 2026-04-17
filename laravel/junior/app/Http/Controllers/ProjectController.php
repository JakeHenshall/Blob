<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $search = (string) $request->query('search', '');
        $status = (string) $request->query('status', '');

        $projects = Project::query()
            ->whereHas('client', fn ($q) => $q->where('user_id', $request->user()->id))
            ->with('client')
            ->withCount(['tasks', 'tasks as completed_tasks_count' => fn ($q) => $q->where('status', 'done')])
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->when(
                $status !== '' && in_array($status, Project::STATUSES, true),
                fn ($q) => $q->where('status', $status)
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('projects.index', [
            'projects' => $projects,
            'search' => $search,
            'status' => $status,
            'statuses' => Project::STATUSES,
        ]);
    }

    public function create(Request $request): View
    {
        return view('projects.create', [
            'clients' => $request->user()->clients()->orderBy('name')->get(),
            'statuses' => Project::STATUSES,
        ]);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $project = Project::create($request->validated());

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Project created.');
    }

    public function show(Project $project): View
    {
        $this->authorizeProject($project);

        $project->load(['client', 'tasks' => fn ($q) => $q->latest()]);

        return view('projects.show', ['project' => $project]);
    }

    public function edit(Request $request, Project $project): View
    {
        $this->authorizeProject($project);

        return view('projects.edit', [
            'project' => $project,
            'clients' => $request->user()->clients()->orderBy('name')->get(),
            'statuses' => Project::STATUSES,
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
        $this->authorizeProject($project);

        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('status', 'Project deleted.');
    }

    private function authorizeProject(Project $project): void
    {
        abort_unless(
            $project->client->user_id === request()->user()?->id,
            403
        );
    }
}
