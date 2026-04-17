# Authorization

## Roles

Three roles, stored as a single `role` column on `users` and cast to `App\Enums\Role`:

| Role    | Typical capability                                                                         |
| ------- | ------------------------------------------------------------------------------------------ |
| Admin   | Full access to all clients, projects, tasks and notes.                                     |
| Manager | Creates and manages their own clients and projects. Cannot touch other managers' records. |
| User    | Sees clients they own, projects they own or are tasked on, and tasks assigned to them.     |

Role checks are centralised in Policies. Controllers only call `authorize()` or `$user->can(...)`, and views use the `@can` directive. There are **no inline role comparisons** (`if ($user->role === ...`)) in controllers or views.

Small helpers on the `User` model exist for readability in Policies only:

- `$user->isAdmin()`
- `$user->isManager()`
- `$user->isStaff()` — admin or manager
- `$user->hasRole(Role::Admin, Role::Manager)`

## Policies

One Policy per resource, auto-discovered by Laravel (`App\Models\X` → `App\Policies\XPolicy`):

- `ClientPolicy` — `viewAny`, `view`, `create`, `update`, `delete`, `restore`
- `ProjectPolicy` — the standard set plus `addNote`
- `TaskPolicy` — standard set plus `assign` and `complete`
- `NotePolicy` — `update`, `delete` only (creation is gated on the parent project via `ProjectPolicy::addNote`)

### Sample policy

```php
public function update(User $user, Project $project): bool
{
    return $user->isAdmin() || $project->user_id === $user->id;
}
```

Short, boolean, and obvious at the call site:

```php
$this->authorize('update', $project);
```

## Why Policies instead of inline checks

- **Central** — the rule lives in one file; changing it is localised.
- **Testable** — Policies are plain classes and trivially unit-tested (see `tests/Unit/Policies/ProjectPolicyTest.php`).
- **Composable** — views use `@can`, controllers use `authorize`, FormRequests use `$this->user()->can(...)`.
- **Search-friendly** — one place to grep when you ask "who can do this?".

## Where authorization is invoked

- **FormRequests** `authorize()` method for create/update/delete inputs.
- **Controllers** `$this->authorize('view', $model)` for show/edit and index gates.
- **Blade** `@can('update', $project)` for UI affordances like buttons and menus.

## Admin override

Every Policy method permits admins first, then checks ownership. This is the simplest correct model for an app this size. At senior scale you'd likely move the admin-override rule into a global `before()` hook on a base Policy — here, keeping it explicit is clearer.

## Task assignment edge cases

- Only the project owner (or admin) can assign or reassign a task.
- The assignee can update their own task (status change, description tweak).
- Completing a task is allowed for anyone who can `update` the task.

These rules are expressed in `TaskPolicy::assign`, `TaskPolicy::update` and `TaskPolicy::complete`.
