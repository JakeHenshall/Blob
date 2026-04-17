# Overview

## Domain

ClientHub models a small client-services workflow:

- **Users** log in and do work. They have one of three roles.
- **Clients** are the organisations or people a user works for.
- **Projects** belong to a client and have an owner (a user).
- **Tasks** belong to a project and can be assigned to a user.
- **Notes** belong to a project and capture free-text comments.
- **Project files** belong to a project and are stored on a configured disk.
- **Activity logs** record meaningful events that were caused by a user.

## Relationships

```
User ──< Client (as owner)
User ──< Project (as owner)
User ──< Task (as assignee, nullable)
User ──< Task (as creator)
User ──< Note (as author)
User ──< ProjectFile (as uploader)

Client ──< Project
Project ──< Task
Project ──< Note
Project ──< ProjectFile

ActivityLog ──> User (causer, nullable)
ActivityLog ──> any model (polymorphic subject)
```

## Roles

- **Admin** can do anything. Full read/write on every resource.
- **Manager** can create clients and projects, manage what they own, and view everything. Cannot delete clients or projects outright.
- **User** (the default) can view clients they own, projects they own or are tasked on, and the tasks they are assigned to. They can update their own task status, complete their tasks, add notes, and upload files on projects they can see.

Role-based behaviour is expressed via Policies and a thin `role` middleware; see [authorization.md](authorization.md).

## Status and priority

Projects have a status (`pending`, `active`, `on_hold`, `completed`, `cancelled`, `archived`).
Tasks have a status (`todo`, `in_progress`, `blocked`, `done`) and a priority (`low`, `normal`, `high`, `urgent`).

Both are implemented as PHP 8.1 backed enums in `app/Support` and cast automatically on the model.

## Soft deletes

Clients, Projects, Tasks, Project Files and Users use soft deletes. Notes and Activity logs do not — notes are short-lived comments where hard deletes are fine, and activity logs are append-only and never deleted by the app.

## Queued notifications

`TaskAssignedNotification` implements `ShouldQueue`. It is dispatched from `AssignTaskAction` whenever a new assignee is set and the assignee is not the actor. Delivery uses both `mail` and `database` channels. `tries = 5` with a 30-second backoff provides a modest retry strategy without overwhelming the mail provider if it's flapping.

## File uploads

Project files live on the configured `filesystems.default` disk. See [production.md](production.md) for disk selection guidance. `UploadProjectFileAction` captures MIME type, byte size and sha256 checksum **before** the uploaded temp file is moved, then persists the `ProjectFile` record and the activity log entry atomically.

## HTTP surface

Two entry points:

- `routes/web.php` — session-authenticated Blade UI.
- `routes/api.php` — Sanctum-authenticated JSON API under `/api/v1`. See [api.md](api.md).

Both routes reuse the same Form Requests, Policies, Actions and Services. Only the controllers differ.
