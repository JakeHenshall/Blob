# Business logic

This document explains where each kind of logic lives and why.

## Controllers

Controllers do as little as possible. A typical mutation looks like:

```php
public function store(StoreProjectRequest $request, Client $client, CreateProjectAction $action): RedirectResponse
{
    $project = $action->execute($request->user(), $client, $request->validated());

    return redirect()->route('projects.show', $project)->with('status', 'Project created.');
}
```

Validation lives in the Form Request, authorisation lives in the Policy (invoked from `authorize()` on the Form Request), business logic lives in the Action.

Controllers **do** handle simple cases inline. `NoteController::store`, `ClientController::update` and `ProjectController::update` are straightforward one-liners against Eloquent — there is no Action for them because the workflow is just "persist". The moment an extra side effect appears (logging, notifying, transaction), the code moves into an Action.

## Models

Models carry the parts of domain logic that are **about shape and state**:

- relationships (`owner()`, `client()`, `projects()`, `tasks()`, `assignee()`, `uploader()`)
- casts (`status` → enum, `due_on` → date, `role` → Role)
- scopes (`scopeSearch`, `scopeOfStatus`, `scopeActive`, `scopeArchived`, `scopeOpen`)
- small domain predicates (`Client::isArchived()`, `Task::isComplete()`, `User::isManager()`)
- tiny presentation helpers that are pure (`ProjectFile::humanSize()`, `ProjectFile::temporaryUrl()`)

What models **do not** do:

- dispatch notifications
- write to the activity log
- open transactions
- run multi-step workflows

A model that knew how to "complete a task and notify the assignee's manager and log it" would be impossible to unit test in isolation and fiddly to reason about.

## Actions

Actions encapsulate **workflows**:

- They take clean inputs the Form Request has already validated.
- They open a DB transaction when they write to more than one row.
- They orchestrate multiple concerns: persistence, activity logging, notification.
- They return the affected model(s).

This repo has five:

- `CreateProjectAction` — persist a project, log activity.
- `ArchiveClientAction` — archive a client, cascade status to open projects, log.
- `AssignTaskAction` — update assignment, log, notify the new assignee (queued).
- `CompleteTaskAction` — idempotent, update status + `completed_at`, log.
- `UploadProjectFileAction` — capture metadata **before** move, move file, persist `ProjectFile`, log.

Every constructor takes the `ActivityLogger` service. Every `execute()` method is side-effectful and wrapped in `DB::transaction` unless the whole operation is a single write. Idempotency is explicit (`if ($task->isComplete()) return $task;`).

### Why not an Action for every CRUD method?

Because `$project->update($request->validated())` is already a single line that tells the whole truth. Wrapping it in an Action adds a file, a constructor, a docblock and a unit test, and prevents the reader from seeing what's happening at a glance. Actions earn their place when a workflow involves **coordination** — multiple side effects, a transaction boundary, or non-obvious rules.

## Services

One service: `App\Services\ActivityLogger`. It is a service rather than an action because it is stateless, called from everywhere, and has a tiny stable surface (`record(User, string, ?Model, array)`).

The `Services` folder exists for pieces of **infrastructure** that actions reach for. It is not a bucket for anything that is "too long to be a controller". If you find yourself writing a `ProjectService`, reconsider whether the logic wants to be an Action (workflow), a scope (query), a model method (pure predicate), or a Policy (decision).

## Policies

Authorisation lives in Policies. See [authorization.md](authorization.md).

## Form Requests

Validation rules and authorisation, nothing else. Each Form Request's `authorize()` delegates to a Policy:

```php
public function authorize(): bool
{
    return $this->user()?->can('update', $this->route('project')) ?? false;
}
```

When the authorisation check is **scoped** — a task create gated on both class-level ability and the parent project — `authorize()` composes two `can()` calls:

```php
public function authorize(): bool
{
    $user = $this->user();
    $project = $this->route('project');

    if (! $user || ! $project instanceof Project) {
        return false;
    }

    return $user->can('view', $project)
        && $user->can('create', [Task::class, $project]);
}
```

This kind of composition stays in the Form Request because it is still "can this request proceed?". Policies stay boolean and per-ability.

## Notifications

`TaskAssignedNotification` implements `ShouldQueue`, so dispatching it inside an Action does not slow the HTTP response. It is triggered **only** from `AssignTaskAction`, and only when the assignee is not the actor:

```php
if ($assignee && $assignee->id !== $actor->id) {
    $assignee->notify(new TaskAssignedNotification($task));
}
```

## Activity logging

`ActivityLogger::record` writes rows to `activity_logs` with:

- the causer (the currently authenticated user, if any)
- the morph subject (Client, Project, Task, ProjectFile, etc.)
- an event string (`project.created`, `task.assigned`, `project.file_uploaded`, …)
- optional structured `properties` (JSON)
- the request IP

**Only Actions and the three remaining inline controller mutations call the logger.** That keeps "what counts as a notable event" in a small, greppable set of places. See [observability.md](observability.md) for the full event list and retention stance.

## What is deliberately not abstracted

- **Search/filter/sort** is built inline on the controller using model scopes. There is no `ProjectSearchService`.
- **Dashboard queries** are inline in `DashboardController`. Extracting a `DashboardQuery` would cost more than it saves at this size.
- **Email templates** use the default `MailMessage` builder. No custom Mailables yet.
- **No `Manager`, `Helper` or `Repository` layer.** If you propose one, justify it in the PR.

This is intentional. The app stays small, and you can read it in one sitting.
