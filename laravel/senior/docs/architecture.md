# Architecture

This document explains how a request moves through the app, and why each layer exists.

## Request flow (web)

```
HTTP request
   │
   ▼
routes/web.php  ── binds URL to a controller action and middleware
   │
   ▼
Middleware  ── auth, verified, CSRF, role (stock Laravel + one custom)
   │
   ▼
Form Request  ── validates input and authorises via Policy in authorize()
   │
   ▼
Controller  ── loads data for the view, calls an Action for real work,
               returns a redirect or view
   │
   ▼
Action  ── opens a DB transaction, writes domain models,
           records activity via ActivityLogger, dispatches notifications
   │
   ▼
Eloquent model  ── persists data, casts enums, exposes relationships and scopes
   │
   ▼
Notification  ── queued via the `database` queue driver
```

## Request flow (API)

```
HTTP request
   │
   ▼
routes/api.php  (/api/v1 prefix)
   │
   ▼
Middleware  ── throttle:api, auth:sanctum
   │
   ▼
Form Request  ── same classes reused from web
   │
   ▼
Controller  ── returns JsonResource or AnonymousResourceCollection
   │
   ▼
Action  ── identical to web
```

The api and web surfaces **share**:

- Form Requests (validation + authorisation)
- Policies
- Actions
- Services (e.g. `ActivityLogger`)
- Eloquent models

They only differ in the controller that adapts the result for the channel (Blade vs JsonResource).

## Layers

```
app/
  Http/
    Controllers/          ← thin adapters (web and api/v1)
    Middleware/           ← EnsureUserHasRole
    Requests/             ← validation + policy invocation
    Resources/            ← JSON shaping (api only)
  Actions/                ← transactional workflows with side effects
  Services/               ← reusable infrastructure (ActivityLogger)
  Policies/               ← authorisation decisions
  Models/                 ← persistence, casts, relationships, scopes
  Notifications/          ← queued user-facing notifications
  Support/                ← enums (Role, ProjectStatus, TaskStatus, TaskPriority)
  Providers/              ← AppServiceProvider (gates, rate limits, URL forcing)
```

## Why thin controllers

Controllers in this app do three things:

1. **Authorise** via `$this->authorize(...)` or by letting a Form Request do it.
2. **Shape data for the channel** (eager loads for Blade, pagination for API Resources).
3. **Hand off to an Action** when there is meaningful business logic.

They deliberately do not:

- inline validation rules
- make role comparisons (`if ($user->role === ...)`)
- dispatch notifications
- write activity log rows

## Why Actions

An Action class wraps a **workflow** that is non-trivially bigger than "persist a row":

- `CreateProjectAction` — persist, log activity, return the project.
- `AssignTaskAction` — update, log, conditionally notify the new assignee.
- `CompleteTaskAction` — guard against double-completion, update, log.
- `ArchiveClientAction` — archive the client, cascade project status, log.
- `UploadProjectFileAction` — capture metadata, move file, persist record, log.

Every action opens a DB transaction and is idempotent where idempotency is meaningful. Side effects (notifications, log writes) are co-located with the write they describe.

## Why Services

`ActivityLogger` is the one service in the app. It is a **service** rather than an action because:

- it is stateless, cheap to call repeatedly
- it is called from many actions, not orchestrated as a workflow
- it has a tiny, stable surface (`record()`)

"Service" is not a catch-all bucket. If you find yourself adding a `UserService`, consider whether the logic belongs in an action or a model instead.

## Why not repositories

Eloquent already abstracts the database. Adding a repository layer in an app of this size almost always:

- duplicates the model API
- hides scopes, eager loading and pagination behind a weaker interface
- forces test doubles where in-memory SQLite would do

We reach for scopes on the models (`Project::scopeSearch`, `Task::scopeOpen`, `Client::scopeActive`) and keep Actions for side effects. That's enough.

If we were sourcing data from an external service, a repository would pull its weight. With Eloquent, it doesn't.

## Where state lives

- **Casts and enums** on the model (`status`, `priority`, `role`, dates)
- **Scopes** on the model for common filters
- **Small domain methods** on the model (`Client::isArchived()`, `Task::isComplete()`, `User::isManager()`)
- **Side effects** in Actions
- **Authorisation decisions** in Policies
- **Rate limiting rules** in `AppServiceProvider::boot`

## Defensive defaults

`AppServiceProvider::boot` applies a few senior-flavoured defaults:

```php
Model::preventLazyLoading(! app()->isProduction());
```

Lazy loading is treated as a bug outside production. If a test or a dev-time request triggers an N+1, it throws — you fix the eager load instead of shipping a slow page.

```php
if (app()->environment('production')) {
    URL::forceScheme('https');
}
```

Prevents accidentally generating `http://` URLs in emails behind a TLS-terminating proxy.

Rate limiters are keyed per authenticated user when possible, falling back to IP:

```php
RateLimiter::for('api', function (Request $request) {
    $user = $request->user();

    return $user
        ? Limit::perMinute(60)->by((string) $user->id)
        : Limit::perMinute(20)->by($request->ip());
});
```

## What is deliberately not built

- No container/DI configuration beyond constructor injection. Laravel's auto-wiring is enough.
- No CQRS, event sourcing, or message bus. Plain actions + Eloquent are plenty.
- No Livewire/Inertia layer. The web UI is server-rendered Blade; the API is a first-class citizen instead.
- No feature flag system. There are no features worth gating yet.
- No caching layer — see [production.md](production.md) for when to add one.
