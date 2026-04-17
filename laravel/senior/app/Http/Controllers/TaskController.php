<?php

namespace App\Http\Controllers;

use App\Actions\Tasks\AssignTaskAction;
use App\Actions\Tasks\CompleteTaskAction;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Support\TaskPriority;
use App\Support\TaskStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Task::class);

        $user = $request->user();

        $query = Task::query()
            ->with(['project', 'assignee'])
            ->search($request->string('q')->toString() ?: null);

        $mine = $request->boolean('mine');
        if ($mine || ! $user->isManager()) {
            $query->where('assignee_id', $user->id);
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($request->boolean('open')) {
            $query->open();
        }

        $tasks = $query->latest()->paginate(20)->withQueryString();

        return view('tasks.index', [
            'tasks' => $tasks,
            'statuses' => TaskStatus::cases(),
        ]);
    }

    public function create(Project $project): View
    {
        $this->authorize('create', Task::class);

        return view('tasks.create', [
            'project' => $project,
            'users' => User::orderBy('name')->get(),
            'statuses' => TaskStatus::cases(),
            'priorities' => TaskPriority::cases(),
        ]);
    }

    public function store(StoreTaskRequest $request, Project $project, ActivityLogger $log): RedirectResponse
    {
        $data = $request->validated();

        $task = Task::create([
            ...$data,
            'project_id' => $project->id,
            'created_by_id' => $request->user()->id,
            'status' => $data['status'] ?? TaskStatus::Todo->value,
            'priority' => $data['priority'] ?? TaskPriority::Normal->value,
        ]);

        $log->record($request->user(), 'task.created', $task, ['title' => $task->title]);

        return redirect()->route('projects.show', $project)->with('status', 'Task created.');
    }

    public function show(Task $task): View
    {
        $this->authorize('view', $task);

        $task->load(['project.client', 'assignee', 'creator']);

        return view('tasks.show', [
            'task' => $task,
            'users' => User::orderBy('name')->get(),
            'statuses' => TaskStatus::cases(),
            'priorities' => TaskPriority::cases(),
        ]);
    }

    public function update(UpdateTaskRequest $request, Task $task, ActivityLogger $log): RedirectResponse
    {
        $task->update($request->validated());
        $log->record($request->user(), 'task.updated', $task);

        return redirect()->route('tasks.show', $task)->with('status', 'Task updated.');
    }

    public function assign(Request $request, Task $task, AssignTaskAction $action): RedirectResponse
    {
        $this->authorize('assign', $task);

        $data = $request->validate([
            'assignee_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $assignee = $data['assignee_id'] ? User::find($data['assignee_id']) : null;
        $action->execute($request->user(), $task, $assignee);

        return redirect()->route('tasks.show', $task)->with('status', 'Assignment updated.');
    }

    public function complete(Task $task, CompleteTaskAction $action, Request $request): RedirectResponse
    {
        $this->authorize('complete', $task);

        $action->execute($request->user(), $task);

        return back()->with('status', 'Task completed.');
    }

    public function destroy(Task $task, ActivityLogger $log, Request $request): RedirectResponse
    {
        $this->authorize('delete', $task);
        $project = $task->project;
        $task->delete();
        $log->record($request->user(), 'task.deleted', $task);

        return redirect()->route('projects.show', $project)->with('status', 'Task deleted.');
    }
}
