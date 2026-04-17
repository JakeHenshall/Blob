# Business logic

This document explains where each kind of logic lives and why.

## Controllers

Controllers in this app do as little as possible. A typical `store` method:

```php
public function store(StoreProjectRequest $request, CreateProjectAction $action): RedirectResponse
{
    $project = $action->handle($request->user(), $request->validated());

    return redirect()
        ->route('projects.show', $project)
        ->with('status', 'Project created.');
}
```

That's it. Validation lives in the FormRequest, authorization lives in the Policy invoked from `authorize()` on the FormRequest, business logic lives in the Action.

Controllers **do** handle simple cases inline. For example `NoteController@store` just creates a row — there's no notification or activity log — so we skip the Action.

## Models

Models carry the parts of domain logic that are **about shape and state**:

- relationships (`owner()`, `client()`, `projects()`, `tasks()`, `notes()`, `assignee()`)
- casts (`status` → enum, `due_at` → date)
- scopes (`search`, `status`, `ownedBy`, `dueSoon`, `overdue`, `open`, `assignedTo`)
- small query-shaped domain methods (`Task::isOverdue()`, `Project::openTasksCount()`)

What models **do not** do:

- dispatch notifications
- write to the activity log
- perform multi-step workflows

A model that knew how to "complete a task and notify the assignee's manager and log it" would be impossible to unit test and annoying to reason about.

## Actions

Actions encapsulate **workflows**:

- They take the clean inputs the FormRequest already validated.
- They may open a DB transaction.
- They orchestrate multiple concerns: persistence, logging, notification.
- They return the affected model(s).

This repo has four:

- `CreateClientAction` — persists a client and logs activity.
- `CreateProjectAction` — persists a project, logs activity, notifies staff.
- `AssignTaskAction` — updates assignment, notifies the new assignee, logs assigned/unassigned activity.
- `CompleteTaskAction` — completes the task, sets timestamps, logs activity, and is idempotent.

### Why not an Action for every CRUD method?

Because a controller that does `$project->update($request->validated())` is already small and obvious. Wrapping it in an `UpdateProjectAction` would add a file, a constructor, a docblock and a unit test, and prevent the reader from seeing what's happening at a glance. Save Actions for the cases where the value is real: workflows with side effects.

## Policies

Authorization lives in Policies. See [authorization.md](authorization.md).

## Form Requests

Validation rules, and nothing else. Each FormRequest's `authorize()` delegates to a Policy:

```php
public function authorize(): bool
{
    return $this->user()->can('update', $this->route('project'));
}
```

## Notifications

Both notifications implement `ShouldQueue`, so sending them during an Action does not slow down the HTTP response. They are triggered from Actions, not from Models or Controllers.

## Activity logging

The `App\Support\ActivityLogger` writes rows to `activities` with:

- the causer (the currently authenticated user, if any)
- the morph subject (Client, Project, Task, etc.)
- an action string (`project.created`, `task.assigned`, `task.completed`, …)
- a human-readable description
- optional structured `properties` (JSON)

Only Actions call the logger. That keeps "what counts as a notable event" in one small set of places.

## What is deliberately not abstracted

- **Search/filter/sort** is built inline on the controller using model scopes. There's no search service.
- **Dashboard queries** are inline in `DashboardController`. They're small and well-named; extracting a `DashboardQuery` would cost more than it saves.
- **Views** use server-rendered Blade. No Livewire, Inertia, Vue or API.
- **Email templates** use the default `MailMessage` builder. No custom Mailables.
- **No `Service`, `Manager`, `Helper` or `Repository` layer**. If you add one, justify it.

This is intentional. The app stays small, and you can read it in one sitting.
