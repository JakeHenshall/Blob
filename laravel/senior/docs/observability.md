# Observability

This app treats observability as a first-class concern. Three things cover most production needs: an in-app activity log, structured application logs, and basic rate-limit headers on the API.

## Activity log

The `activity_logs` table is an append-only audit trail. Every meaningful domain event is recorded exactly once, from the Action that performed it.

### Schema

| Column        | Type               | Notes                                              |
| ------------- | ------------------ | -------------------------------------------------- |
| `id`          | `bigint`           |                                                    |
| `user_id`     | `bigint nullable`  | The causer. `null` for system-initiated events.    |
| `event`       | `string`           | Dotted event name (`project.created`).             |
| `subject_type` | `string nullable` | Morph class of the target model.                   |
| `subject_id`  | `bigint nullable`  | Key of the target model.                           |
| `properties`  | `json nullable`    | Arbitrary structured context.                      |
| `ip_address`  | `string nullable`  | Captured via `Illuminate\Support\Facades\Request`. |
| `created_at`  | `datetime`         | No `updated_at` — this log is append-only.         |

### Event catalogue

| Event                       | Emitted from                     | Properties                                       |
| --------------------------- | -------------------------------- | ------------------------------------------------ |
| `client.created`            | `ClientController::store`        | `name`                                           |
| `client.archived`           | `ArchiveClientAction`            | `name`                                           |
| `client.updated`            | `ClientController::update`       | —                                                |
| `client.deleted`            | `ClientController::destroy`      | —                                                |
| `project.created`           | `CreateProjectAction`            | `client_id`, `name`                              |
| `project.updated`           | `ProjectController::update`      | —                                                |
| `project.deleted`           | `ProjectController::destroy`     | —                                                |
| `project.note_added`        | `NoteController::store`          | `note_id`                                        |
| `project.note_deleted`      | `NoteController::destroy`        | `note_id`                                        |
| `project.file_uploaded`     | `UploadProjectFileAction`        | `file_id`, `size_bytes`, `mime_type`             |
| `project.file_deleted`      | `ProjectFileController::destroy` | `file_id`                                        |
| `task.created`              | `TaskController::store` / API    | `title` (API adds `source: "api"`)               |
| `task.updated`              | `TaskController::update`         | —                                                |
| `task.assigned`             | `AssignTaskAction`               | `assignee_id`, `previous_assignee_id`            |
| `task.completed`            | `CompleteTaskAction`             | `title`                                          |
| `task.deleted`              | `TaskController::destroy`        | —                                                |

### Why the log exists where it does

Only Actions and three very small inline controller mutations call the logger. That means:

- **Searchable in one pass.** Grepping for `activity->record(` and `activity.record(` finds every log emission in the codebase.
- **Never duplicated.** A single code path produces a single log row.
- **Never from a model.** Models are hydrated by Eloquent, factories and seeders; none of those produce user events.

### What is not logged

- Pure reads (`view`, `show`) — too noisy for the payoff.
- Failed writes — Laravel already logs exceptions to the application log.
- Authentication events (login, logout) — Sanctum handles tokens and session guard handles logins; add a listener only when someone asks for it.

### Retention

The table is append-only. There is no built-in retention policy because:

- At the expected scale (tens of thousands of rows per year per tenant) the table is tiny.
- Deciding retention is a policy/legal question, not a code question.

When retention is required, the simplest correct path is a scheduled job that deletes rows older than N days and keeps a monthly summary.

## Application logs

Standard Laravel logging, with a stack channel by default. Worth knowing:

- `LOG_LEVEL` defaults to `debug` in `.env.example` — drop to `info` or `warning` in production.
- `MAIL_MAILER=log` in development surfaces notifications in the log rather than the mail provider.
- `php artisan pail` tails the log in a friendly colourised stream; `composer dev` starts it alongside the web server.

For production, configure the `LOG_CHANNEL` stack to include a structured driver (`papertrail`, `slack`, `stderr` → your aggregator of choice). Ship logs off the box.

## Rate-limit headers

The default API limiter emits standard headers on every response:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 57
Retry-After: 30      (on 429)
X-RateLimit-Reset: 1713280000
```

Clients are expected to read these and back off. The limiter is keyed per user when authenticated and per IP otherwise, so one noisy client cannot starve another.

## Metrics and tracing (not included)

Deliberately out of scope for the reference app. In a real deployment:

- Request-level metrics via the web server (Nginx `access_log`) or an APM agent (New Relic, Datadog, Sentry Performance).
- Exception reporting via Sentry / Bugsnag — the hook point is the `Exceptions` block in `bootstrap/app.php`.
- Business metrics derived from `activity_logs` (e.g. "tasks completed per manager per week").

Adding these is a configuration change; they intentionally did not ship with the reference to keep the surface minimal.

## What to check when something breaks

1. `storage/logs/laravel.log` — the stack trace and anything logged above `debug`.
2. `activity_logs` — was the expected event recorded? If not, the Action probably failed before its log call, which means the transaction rolled back too.
3. `jobs` / `failed_jobs` tables — queued notifications that didn't go out.
4. `personal_access_tokens` — stale tokens still accepted after a user change their password (Sanctum does not invalidate tokens on password reset by default; revoke with `$user->tokens()->delete()`).
