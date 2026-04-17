<?php

namespace App\Http\Controllers;

use App\Actions\Tasks\AssignTaskAction;
use App\Actions\Tasks\CompleteTaskAction;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Requests\AssignTaskRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Task::class);

        $user = $request->user();
        $sort = $request->input('sort', 'due_at');
        $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        $sortable = ['title', 'status', 'priority', 'due_at', 'created_at'];
        if (! in_array($sort, $sortable, true)) {
            $sort = 'due_at';
        }

        $tasks = Task::query()
            ->with(['project', 'assignee'])
            ->search($request->input('q'))
            ->status($request->input('status'))
            ->assignedTo($request->integer('assigned_to') ?: null)
            ->when($request->boolean('due_soon'), fn ($q) => $q->dueSoon())
            ->when($request->boolean('mine'), fn ($q) => $q->where('assigned_to', $user->id))
            ->when(! $user->isStaff(), function ($q) use ($user) {
                $q->where(function ($sub) use ($user) {
                    $sub->where('assigned_to', $user->id)
                        ->orWhereHas('project', fn ($p) => $p->where('user_id', $user->id));
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(20)
            ->withQueryString();

        return view('tasks.index', [
            'tasks' => $tasks,
            'filters' => $request->only(['q', 'status', 'assigned_to', 'due_soon', 'mine', 'sort', 'direction']),
            'statuses' => TaskStatus::options(),
            'priorities' => TaskPriority::options(),
            'assignableUsers' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Task::class);

        $project = null;
        if ($request->filled('project_id')) {
            $project = Project::findOrFail($request->integer('project_id'));
        }

        return view('tasks.create', [
            'projects' => Project::orderBy('name')->get(['id', 'name']),
            'statuses' => TaskStatus::options(),
            'priorities' => TaskPriority::options(),
            'assignableUsers' => User::orderBy('name')->get(['id', 'name']),
            'preselectedProject' => $project,
        ]);
    }

    public function store(StoreTaskRequest $request, AssignTaskAction $assignAction): RedirectResponse
    {
        $data = $request->validated();
        $assignedTo = $data['assigned_to'] ?? null;
        unset($data['assigned_to']);

        $task = Task::create($data);

        if ($assignedTo) {
            $assignAction->handle($task, User::find($assignedTo));
        }

        return redirect()
            ->route('tasks.show', $task)
            ->with('status', 'Task created.');
    }

    public function show(Task $task): View
    {
        $this->authorize('view', $task);

        $task->load(['project.client', 'assignee']);

        return view('tasks.show', [
            'task' => $task,
            'assignableUsers' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function edit(Task $task): View
    {
        $this->authorize('update', $task);

        return view('tasks.edit', [
            'task' => $task,
            'statuses' => TaskStatus::options(),
            'priorities' => TaskPriority::options(),
            'assignableUsers' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateTaskRequest $request, Task $task, AssignTaskAction $assignAction): RedirectResponse
    {
        $data = $request->validated();
        $newAssignee = $data['assigned_to'] ?? null;
        unset($data['assigned_to']);

        $task->update($data);

        if ($newAssignee !== $task->assigned_to) {
            $assignAction->handle($task, $newAssignee ? User::find($newAssignee) : null);
        }

        return redirect()
            ->route('tasks.show', $task)
            ->with('status', 'Task updated.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()
            ->route('tasks.index')
            ->with('status', 'Task archived.');
    }

    public function assign(AssignTaskRequest $request, Task $task, AssignTaskAction $action): RedirectResponse
    {
        $userId = $request->validated('assigned_to');
        $action->handle($task, $userId ? User::find($userId) : null);

        return back()->with('status', 'Task assignment updated.');
    }

    public function complete(Task $task, CompleteTaskAction $action): RedirectResponse
    {
        $this->authorize('complete', $task);

        $action->handle($task);

        return back()->with('status', 'Task marked as completed.');
    }
}
