# ClientHub (senior)

A small client, project and task manager built with Laravel 13, Blade, Tailwind CSS and a Sanctum-authenticated HTTP API.

## What this app is

ClientHub is a focused CRM-style app. Staff create clients, spin up projects for them, break projects into tasks, assign those tasks to teammates, attach files, and keep notes on each project. Managers and admins get role-aware views, queued email notifications, an activity audit log, a versioned JSON API, and storage-aware file uploads.

## What this version proves

This is the **senior-level** reference app in a three-tier progression (junior → mid → senior). Compared with the mid-level version it demonstrates:

- explicit architectural boundaries (controllers, form requests, actions, services, policies)
- a versioned HTTP API (`/api/v1`) with Sanctum personal access tokens
- JSON API Resources for stable response shaping
- rate limiting, per-user when authenticated, per-IP when not
- storage-driver agnostic file uploads with checksum and metadata capture
- an append-only activity log driven only from actions
- defensive engineering: transactional actions, idempotency, preventing lazy loading outside production, forced HTTPS in production
- richer authorisation (policy delegation, project-scoped gate checks)
- PHPDoc on every class that encodes non-trivial intent
- explicit documentation of trade-offs and deliberate non-goals

The emphasis is engineering judgement under real-world constraints — authorisation, observability, API design, storage — not enterprise ceremony.

## Why this is a senior-level example

A mid app proves you can structure a Laravel codebase maintainably. This app proves you can make the decisions that keep the codebase maintainable **in production**: where the API lives, who can see what, how failures surface, what counts as an audit event, where transactions belong, and what you deliberately chose not to build. See [docs/difference-from-mid.md](docs/difference-from-mid.md) for the full contrast.

## Features

- Authentication
  - Blade session auth for the web UI
  - Sanctum personal access tokens for the API
- Three roles: Admin, Manager, User
- Clients, Projects, Tasks, Notes, Files CRUD
- Project file uploads with sha256 checksums, MIME guarding, size limits, and temporary URLs on signed-URL capable disks
- Task assignment, status, priority and due dates
- Search, filter and sort on every list view
- Role-aware dashboards
- Activity log for:
  - `client.created`, `client.archived`
  - `project.created`, `project.updated`, `project.deleted`
  - `project.note_added`, `project.note_deleted`
  - `project.file_uploaded`, `project.file_deleted`
  - `task.created`, `task.updated`, `task.assigned`, `task.completed`, `task.deleted`
- Queued `TaskAssignedNotification` via `mail` + `database` channels
- Soft deletes on Clients, Projects, Tasks, Project Files, Users
- JSON API (`/api/v1`):
  - `POST /auth/token`, `GET /auth/me`, `POST /auth/logout`
  - `GET /projects`, `GET /projects/{project}`
  - `GET /projects/{project}/tasks`, `POST /projects/{project}/tasks`, `POST /tasks/{task}/complete`
- Rate limiting: 60/min per authenticated user, 20/min per IP for guests, 10/min on token issuance

## Stack

- PHP 8.3+
- Laravel 13
- Laravel Sanctum 4 (API tokens + stateful SPA)
- Blade + Tailwind CSS 3 + Alpine.js
- Vite 8
- SQLite by default (swap in `.env` for MySQL/Postgres)
- PHPUnit 12 for tests

## Quick start

```bash
cd laravel/senior

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

Plus six factory-generated users. All seeded accounts use the password `password`.

## Testing

```bash
php artisan test
```

Tests use PHPUnit with an in-memory SQLite database (configured in `phpunit.xml`). The queue connection is set to `sync` so notifications can be faked with `Notification::fake()`. See [docs/testing.md](docs/testing.md) for what is and isn't covered.

## Queue setup

The app uses Laravel's `database` queue driver. `TaskAssignedNotification` is marked `ShouldQueue`. Run a worker to process it:

```bash
php artisan queue:work
```

During development you can run a queue listener alongside the dev server:

```bash
composer dev
```

The `dev` script runs server, queue, log tail and Vite concurrently.

In testing the queue connection is `sync`, so tests do not need a worker.

## Using the API

Issue a token:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/token \
  -H 'Accept: application/json' \
  -d 'email=manager@clienthub.test&password=password&device_name=cli'
```

Call an endpoint:

```bash
curl http://127.0.0.1:8000/api/v1/projects \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer <token>"
```

See [docs/api.md](docs/api.md) for the full surface.

## Documentation

- [docs/overview.md](docs/overview.md) — domain, relationships, roles
- [docs/setup.md](docs/setup.md) — environment, tooling and storage disks
- [docs/architecture.md](docs/architecture.md) — layers, request flow, why each abstraction exists
- [docs/authorization.md](docs/authorization.md) — roles, policies and gate checks
- [docs/api.md](docs/api.md) — Sanctum, versioning, resources, rate limiting
- [docs/business-logic.md](docs/business-logic.md) — where logic lives and why
- [docs/observability.md](docs/observability.md) — activity log, logging strategy, auditability
- [docs/production.md](docs/production.md) — deployment, caching, queue, scaling
- [docs/testing.md](docs/testing.md) — feature vs unit testing strategy
- [docs/q-and-a.md](docs/q-and-a.md) — questions a senior Laravel dev should be able to answer
- [docs/difference-from-mid.md](docs/difference-from-mid.md)
