<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $search = (string) $request->query('search', '');
        $status = (string) $request->query('status', '');

        $tasks = Task::query()
            ->whereHas('project.client', fn ($q) => $q->where('user_id', $request->user()->id))
            ->with('project.client')
            ->when($search !== '', fn ($q) => $q->where('title', 'like', "%{$search}%"))
            ->when(
                $status !== '' && in_array($status, Task::STATUSES, true),
                fn ($q) => $q->where('status', $status)
            )
            ->orderByRaw('CASE status WHEN \'in_progress\' THEN 0 WHEN \'todo\' THEN 1 ELSE 2 END')
            ->orderBy('due_on')
            ->paginate(15)
            ->withQueryString();

        return view('tasks.index', [
            'tasks' => $tasks,
            'search' => $search,
            'status' => $status,
            'statuses' => Task::STATUSES,
        ]);
    }

    public function create(Request $request): View
    {
        $projects = $this->projectsForUser($request);

        return view('tasks.create', [
            'projects' => $projects,
            'statuses' => Task::STATUSES,
            'preselectedProjectId' => $request->integer('project_id') ?: null,
        ]);
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['completed_at'] = $data['status'] === 'done' ? now() : null;

        $task = Task::create($data);

        return redirect()
            ->route('tasks.show', $task)
            ->with('status', 'Task created.');
    }

    public function show(Task $task): View
    {
        $this->authorizeTask($task);

        $task->load('project.client');

        return view('tasks.show', ['task' => $task]);
    }

    public function edit(Request $request, Task $task): View
    {
        $this->authorizeTask($task);

        return view('tasks.edit', [
            'task' => $task,
            'projects' => $this->projectsForUser($request),
            'statuses' => Task::STATUSES,
        ]);
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $data = $request->validated();

        if ($data['status'] === 'done' && $task->status !== 'done') {
            $data['completed_at'] = now();
        } elseif ($data['status'] !== 'done') {
            $data['completed_at'] = null;
        }

        $task->update($data);

        return redirect()
            ->route('tasks.show', $task)
            ->with('status', 'Task updated.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorizeTask($task);

        $task->delete();

        return redirect()
            ->route('tasks.index')
            ->with('status', 'Task deleted.');
    }

    private function projectsForUser(Request $request)
    {
        return \App\Models\Project::query()
            ->whereHas('client', fn ($q) => $q->where('user_id', $request->user()->id))
            ->with('client:id,name')
            ->orderBy('name')
            ->get();
    }

    private function authorizeTask(Task $task): void
    {
        abort_unless(
            $task->project->client->user_id === request()->user()?->id,
            403
        );
    }
}
