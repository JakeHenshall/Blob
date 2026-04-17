# Architecture

This document explains how a request moves through the app and why the code is structured the way it is.

## Request flow

```
HTTP request
   │
   ▼
routes/web.php  ── binds URL to a controller action and middleware
   │
   ▼
Middleware  ── auth, verified, CSRF, etc. (stock Laravel)
   │
   ▼
Form Request ── validates input and authorises with Policy (via authorize())
   │
   ▼
Controller  ── resolves model(s), calls an Action for real work, returns a redirect or view
   │
   ▼
Action class ── wraps business workflow in a DB transaction,
                writes to the ActivityLogger and dispatches notifications
   │
   ▼
Eloquent model ── persists data, casts enums, exposes relationships and scopes
   │
   ▼
Notification ── queued via the `database` queue driver
```

The view side:

```
Controller returns view(...)
   │
   ▼
Blade template (layouts/app.blade.php → resources/views/<resource>/<view>.blade.php)
   │
   ▼
Blade components in resources/views/components (x-card, x-badge, x-page-header, etc.)
```

## Why thin controllers

Controllers in this app do three things:

1. **Authorise** via `$this->authorize(...)` or by letting a FormRequest do it.
2. **Resolve and shape data** for the view (loading relationships, applying filters and sorts).
3. **Hand off** to an Action if there is meaningful business logic.

They deliberately do not:

- put validation rules inline
- implement role checks with `if ($user->role === ...)`
- call a notification or write to the activity log directly for workflows

Keeping controllers thin makes each file easy to read end-to-end and keeps the "what" in controllers and the "how" in actions, models and policies.

## Why Action classes

An Action class wraps a **workflow** that is non-trivially bigger than "persist a row":

- `CreateProjectAction` — persists the project, logs the activity, and fans out notifications to relevant staff.
- `AssignTaskAction` — updates assignment, notifies the assignee, logs the activity, and handles both "assign" and "unassign" cases.
- `CompleteTaskAction` — guards against double-completion, updates status and `completed_at`, logs activity.
- `CreateClientAction` — persists and logs.

If the business logic were just "create a row", it would stay in the controller. Actions exist where logic is composed of multiple steps that should happen together, atomically. They are plain invokable-style classes (`handle(...)`) with dependencies injected via the constructor, which makes them trivial to unit test.

`CreateClientAction` is arguably overkill at the moment (it just persists and logs), but having it in place makes it cheap to add future side effects (welcome email, CRM sync, etc.) without reopening the controller.

## Why not repositories

Eloquent already abstracts the database. Adding a repository layer in a Laravel app of this size almost always creates two problems:

- it duplicates the model API (`find`, `create`, `update`...) for no gain
- it hides query features like scopes, eager loading and pagination behind a weaker interface

We reach for scopes on the models (`Project::dueSoon()`, `Task::overdue()`, etc.) and keep Actions for side effects. That's enough.

If we were backing the same domain with something non-Eloquent (an external API, say), a repository would earn its keep. Until then, no.

## Where state lives

- **Casts and enums** on the model (`status`, `priority`, `role`, dates)
- **Scopes** on the model for common filters (`search`, `status`, `dueSoon`, `overdue`, `open`, `ownedBy`, `assignedTo`)
- **Small domain methods** on the model (`User::isStaff()`, `Task::isOverdue()`, `TaskStatus::isOpen()`)
- **Side effects** in Actions
- **Authorisation decisions** in Policies

## File layout

```
app/
  Actions/
    Clients/CreateClientAction.php
    Projects/CreateProjectAction.php
    Tasks/AssignTaskAction.php
    Tasks/CompleteTaskAction.php
  Enums/
    ProjectStatus.php
    Role.php
    TaskPriority.php
    TaskStatus.php
  Http/
    Controllers/
      ClientController.php
      DashboardController.php
      NoteController.php
      ProjectController.php
      TaskController.php
    Requests/
      AssignTaskRequest.php
      StoreClientRequest.php
      StoreNoteRequest.php
      StoreProjectRequest.php
      StoreTaskRequest.php
      UpdateClientRequest.php
      UpdateProjectRequest.php
      UpdateTaskRequest.php
  Models/
    Activity.php
    Client.php
    Note.php
    Project.php
    Task.php
    User.php
  Notifications/
    ProjectCreatedNotification.php
    TaskAssignedNotification.php
  Policies/
    ClientPolicy.php
    NotePolicy.php
    ProjectPolicy.php
    TaskPolicy.php
  Support/
    ActivityLogger.php
```
