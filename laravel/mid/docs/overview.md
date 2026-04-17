# Overview

## Domain

ClientHub models a small client-services workflow:

- **Users** log in and do work. They have one of three roles.
- **Clients** are the organisations or people a user works for.
- **Projects** belong to a client and have an owner (a user).
- **Tasks** belong to a project and can be assigned to a user.
- **Notes** belong to a project and capture free-text comments.
- **Activities** record meaningful events that were caused by a user.

## Relationships

```
User ──< Client (as owner)
User ──< Project (as owner)
User ──< Task (as assignee, nullable)
User ──< Note (as author)

Client ──< Project
Project ──< Task
Project ──< Note

Activity ──> User (causer, nullable)
Activity ──> any model (polymorphic subject)
```

## Roles

- **Admin** can do anything. Full read/write on every resource.
- **Manager** can create clients and projects, and manage the things they own. Cannot edit resources owned by other managers.
- **User** (the default) can view clients they own, projects they own or are assigned tasks on, and the tasks they are assigned to. They can update their own task status, complete their tasks, and add notes to projects they can see.

Role-based behaviour is expressed via Policies; see [authorization.md](authorization.md).

## Status and priority

Projects have a status (`pending`, `active`, `on_hold`, `completed`, `cancelled`).
Tasks have a status (`todo`, `in_progress`, `completed`, `cancelled`) and a priority (`low`, `medium`, `high`, `urgent`).

Both are implemented as PHP 8.1 backed enums in `app/Enums` and cast automatically on the model.

## Soft deletes

Clients, Projects and Tasks use soft deletes. Notes do not — they're short-lived comments where hard deletes are fine. Activities are never deleted by the app; they're an append-only audit trail.

## Queued notifications

Both notifications implement `ShouldQueue`:

- `TaskAssignedNotification` is sent to the assignee when a task is assigned.
- `ProjectCreatedNotification` is sent to Admins and Managers (excluding the creator) when a new project is created.

Both are sent via `mail` and `database` channels, and are pushed onto the `database` queue.
