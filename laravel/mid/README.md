# ClientHub

A small client, project and task manager built with Laravel 13, Blade and Tailwind CSS.

## What this app is

ClientHub is a focused CRM-style app. Staff create clients, spin up projects for them, break projects into tasks, assign those tasks to teammates and keep notes on each project. Managers and admins get role-aware views, queued email notifications, and an activity log of meaningful events.

## What this version proves

This is the **mid-level** reference app in a three-tier progression (junior → mid → senior). Compared with the junior CRUD version it demonstrates:

- sensible application structure (controllers, requests, policies, actions, notifications)
- role-based access control with Policies instead of inline role checks
- Action classes for meaningful workflows
- queued notifications using the database queue driver
- an activity log for important domain events
- soft deletes where they actually help
- richer seeders, factories and test coverage
- feature tests for real flows plus a few focused unit tests

The emphasis is maintainability and separation of concerns, not enterprise ceremony.

## Why this is a mid-level example

A junior app proves you know the framework. This app proves you can make judgement calls about where logic belongs, when to reach for an abstraction (Policy, Action, Notification) and when to leave a controller as a thin CRUD. See [docs/difference-from-junior.md](docs/difference-from-junior.md) and [docs/difference-from-senior.md](docs/difference-from-senior.md) for the full contrast.

## Features

- Authentication via Laravel Breeze (Blade)
- Three roles: Admin, Manager, User
- Clients, Projects, Tasks and Notes CRUD
- Task assignment, status and priority
- Due dates and overdue highlighting
- Search, filter and sort on each resource
- Role-aware dashboards with:
  - projects grouped by status
  - overdue tasks
  - tasks assigned to me
  - recent activity feed
- Activity log for:
  - `client.created`
  - `project.created`
  - `task.assigned`
  - `task.completed`
  - `task.unassigned`
- Queued email notifications for:
  - task assignment
  - project creation
- Soft deletes on Clients, Projects and Tasks

## Stack

- PHP 8.3+
- Laravel 13
- Laravel Breeze (Blade)
- Tailwind CSS 3
- Vite 8
- Alpine.js
- SQLite by default (swap in `.env`)
- Pest 4 for tests

## Quick start

```bash
cd laravel/mid

cp .env.example .env
composer install
npm install

php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed

npm run build
php artisan serve
```

Then open http://127.0.0.1:8000.

### Seeded accounts

| Role    | Email                    | Password   |
| ------- | ------------------------ | ---------- |
| Admin   | `admin@clienthub.test`   | `password` |
| Manager | `manager@clienthub.test` | `password` |

Plus a handful of factory-generated Managers and Users. All seeded accounts use the password `password`.

## Testing

```bash
php artisan test
```

Tests use Pest and an in-memory SQLite database (configured in `phpunit.xml`). See [docs/testing.md](docs/testing.md) for what is and isn't covered.

## Queue setup

The app uses Laravel's `database` queue driver. Notifications (`TaskAssignedNotification`, `ProjectCreatedNotification`) are marked `ShouldQueue`. Run a worker to process them:

```bash
php artisan queue:work
```

During development you can run a queue listener alongside the dev server:

```bash
php artisan queue:listen --tries=1
```

In testing the queue connection is set to `sync` via `phpunit.xml`, so tests don't need a worker.

## Documentation

- [docs/overview.md](docs/overview.md) — domain, relationships, roles
- [docs/setup.md](docs/setup.md) — environment and tooling
- [docs/architecture.md](docs/architecture.md) — request flow, why actions, why thin controllers
- [docs/authorization.md](docs/authorization.md) — roles and policy design
- [docs/business-logic.md](docs/business-logic.md) — where logic lives and why
- [docs/testing.md](docs/testing.md) — feature vs unit testing strategy
- [docs/q-and-a.md](docs/q-and-a.md) — questions a mid-level Laravel dev should be able to answer
- [docs/difference-from-junior.md](docs/difference-from-junior.md)
- [docs/difference-from-senior.md](docs/difference-from-senior.md)
