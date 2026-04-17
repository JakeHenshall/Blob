<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Tasks\CompleteTaskAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Services\ActivityLogger;
use App\Support\TaskPriority;
use App\Support\TaskStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Task operations exposed over the v1 HTTP API.
 *
 * Every endpoint is gated through the Project/Task policies so a user can
 * only see or mutate tasks for projects they have access to.
 */
class TaskController extends Controller
{
    /**
     * Paginated list of tasks belonging to `$project`.
     *
     * Query parameters:
     *  - status    (string) filter by {@see \App\Support\TaskStatus}
     *  - open      (bool)   when truthy, exclude completed tasks
     *  - per_page  (int 1-100, default 20)
     */
    public function index(Request $request, Project $project): AnonymousResourceCollection
    {
        $this->authorize('view', $project);

        $query = $project->tasks()->with(['assignee']);

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }
        if ($request->boolean('open')) {
            $query->open();
        }

        $per = min(max((int) $request->input('per_page', 20), 1), 100);

        return TaskResource::collection($query->latest()->paginate($per));
    }

    /**
     * Create a task under `$project`. Authorisation and validation are
     * enforced by {@see StoreTaskRequest}.
     *
     * Responds with 201 and a {@see TaskResource}.
     */
    public function store(StoreTaskRequest $request, Project $project, ActivityLogger $log): JsonResponse
    {
        $data = $request->validated();

        $task = Task::create([
            ...$data,
            'project_id' => $project->id,
            'created_by_id' => $request->user()->id,
            'status' => $data['status'] ?? TaskStatus::Todo->value,
            'priority' => $data['priority'] ?? TaskPriority::Normal->value,
        ]);

        $log->record($request->user(), 'task.created', $task, [
            'title' => $task->title,
            'source' => 'api',
        ]);

        return (new TaskResource($task->load('assignee')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Mark `$task` as complete. Idempotent.
     */
    public function complete(Request $request, Task $task, CompleteTaskAction $action): TaskResource
    {
        $this->authorize('complete', $task);

        $updated = $action->execute($request->user(), $task);

        return new TaskResource($updated->load('assignee'));
    }
}
