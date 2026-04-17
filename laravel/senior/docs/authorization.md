# Authorization

## Roles

Three roles, stored as a single `role` column on `users` and cast to `App\Support\Role`:

| Role    | Typical capability                                                                          |
| ------- | ------------------------------------------------------------------------------------------- |
| Admin   | Full access to all clients, projects, tasks, notes and files.                               |
| Manager | Creates and manages their own clients and projects. Views everything. Cannot hard-delete.   |
| User    | Sees clients they own, projects they own or are tasked on, and tasks assigned to them.      |

Small helpers on the `User` model exist for readability in Policies only:

- `$user->isAdmin()`
- `$user->isManager()` — returns true for managers **and** admins (an admin is a manager+)

There are **no inline role comparisons** (`if ($user->role === ...)`) in controllers or views.

## Policies

One Policy per resource, explicitly registered in `AppServiceProvider::boot`:

- `ClientPolicy` — `viewAny`, `view`, `create`, `update`, `archive`, `delete`
- `ProjectPolicy` — the standard set plus `addNote` and `uploadFile`
- `TaskPolicy` — standard set plus `assign` and `complete`
- `NotePolicy` — `view`, `delete` (creation is gated on the parent project via `ProjectPolicy::addNote`)
- `ProjectFilePolicy` — `view`, `delete` (creation is gated on the parent project via `ProjectPolicy::uploadFile`)

### Sample policy

```php
public function update(User $user, Project $project): bool
{
    return $user->isAdmin() || $project->owner_id === $user->id;
}
```

Short, boolean, obvious at the call site:

```php
$this->authorize('update', $project);
```

### Project-scoped create

`TaskPolicy::create` accepts an optional project argument:

```php
public function create(User $user, ?Project $project = null): bool
```

Callers tighten the check by passing the project explicitly:

```php
$user->can('create', [Task::class, $project]);
```

`StoreTaskRequest::authorize()` uses this form and also gates on `view` for the same project, so a user who cannot see a project cannot inject tasks into it even if Laravel's default class-level `create` check passes.

## Delegation across policies

Policies that hang off a parent resource delegate to the parent policy rather than re-implementing the rule:

```php
// NotePolicy
public function view(User $user, Note $note): bool
{
    return app(ProjectPolicy::class)->view($user, $note->project);
}

// ProjectFilePolicy
public function view(User $user, ProjectFile $file): bool
{
    return app(ProjectPolicy::class)->view($user, $file->project);
}
```

This keeps the "who can see a project" rule in exactly one place.

## Role middleware

`App\Http\Middleware\EnsureUserHasRole` is aliased to `role` in `bootstrap/app.php`. It aborts with 403 if the authenticated user's role is not in the allowed list:

```php
Route::get('/admin', fn () => ...)->middleware('role:admin');
Route::get('/staff', fn () => ...)->middleware('role:admin,manager');
```

Use the middleware for coarse-grained route groups where a Policy would be overkill (e.g. an admin-only area). Use Policies everywhere else.

## Where authorisation is invoked

- **Form Requests** `authorize()` for create/update/delete inputs (the single source of truth for web and API mutations).
- **Controllers** `$this->authorize('view', $model)` for show/edit, index gates and read-only API endpoints.
- **Blade** `@can('update', $project)` for UI affordances (buttons, menus, links).
- **Middleware** `role:...` for coarse-grained route gating.

## Admin override

Every Policy method permits admins first, then checks ownership. At this scale keeping the override inline is clearer than moving it into a `before()` hook. If Policy methods multiply, a base class `before()` becomes worth it.

## Task assignment edge cases

- Only the project owner (or admin) can assign or reassign a task.
- An assignee can update their own task (status, description, etc.).
- Completing a task is allowed for anyone who can `update` the task.
- Admins and managers can complete any task.

## Why this is a senior pattern

- Every mutation route has an explicit authorisation decision, expressible in one line.
- The same Policy drives the web UI's `@can` directives, the Form Request's `authorize()`, and the API controller's `authorize('view', $model)`.
- Resource-scoped delegation (`Note` and `ProjectFile` policies defer to `ProjectPolicy`) prevents rule drift.
- The regression we explicitly guarded against is `TaskPolicy::create` returning `true`, which would let any authenticated user POST tasks into any project. See [q-and-a.md](q-and-a.md) question 7.
