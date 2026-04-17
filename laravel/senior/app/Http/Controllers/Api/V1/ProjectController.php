<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Read-only API surface for projects.
 *
 * Results are automatically scoped to projects the caller can see:
 * admins and managers receive everything, everyone else is limited to
 * projects they own or have a task assigned on.
 */
class ProjectController extends Controller
{
    /**
     * Paginated list of projects visible to the authenticated user.
     *
     * Query parameters:
     *  - q         (string)   filter by name (substring match)
     *  - status    (string)   filter by {@see \App\Support\ProjectStatus}
     *  - per_page  (int 1-100, default 20)
     */
    public function index(Request $request): AnonymousResourceCollection
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

        $per = min(max((int) $request->input('per_page', 20), 1), 100);

        return ProjectResource::collection($query->latest()->paginate($per));
    }

    /**
     * Return a single project with its client and owner preloaded.
     */
    public function show(Request $request, Project $project): ProjectResource
    {
        $this->authorize('view', $project);

        return new ProjectResource($project->loadMissing(['client', 'owner']));
    }
}
