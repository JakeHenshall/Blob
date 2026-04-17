# Production

This document captures the things you would actually configure before putting ClientHub in front of real users. None of it is exotic; it is the boring list that separates a reference app from a production one.

## Environment

Swap these values relative to `.env.example`:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://clienthub.example

DB_CONNECTION=mysql        # or pgsql
DB_HOST=...
DB_PORT=...
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...

SESSION_DRIVER=database    # or redis
QUEUE_CONNECTION=redis     # see "Queue" below
CACHE_STORE=redis

FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=...
AWS_BUCKET=...

MAIL_MAILER=ses            # or postmark / mailgun / smtp
MAIL_FROM_ADDRESS="hello@clienthub.example"

SANCTUM_STATEFUL_DOMAINS=clienthub.example

LOG_CHANNEL=stack
LOG_LEVEL=info
```

`AppServiceProvider::boot` forces `https://` on generated URLs when `APP_ENV=production`.

## Deploy steps

```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan event:cache
php artisan view:cache
php artisan migrate --force
npm ci
npm run build
```

On each deploy:

1. Put the app in maintenance if the migration is not backward-compatible (`php artisan down`).
2. Run migrations (`php artisan migrate --force`).
3. Restart the queue worker (`php artisan queue:restart`).
4. Clear compiled caches if relevant (`php artisan optimize:clear` for a hot fix; usually not needed).
5. Bring the app back up (`php artisan up`).

## Queue

The reference uses `QUEUE_CONNECTION=database`. That is fine for a single-worker, low-volume deployment. For anything bigger, switch to Redis:

- Install a Redis instance.
- Set `QUEUE_CONNECTION=redis`.
- Run `php artisan queue:work redis --tries=5 --backoff=30` under a supervisor (systemd, Supervisor, Laravel Horizon).
- Monitor `failed_jobs` — Sanctum token revocation and notification delivery are the two flows that land there when the mail provider is down.

`TaskAssignedNotification` is already marked `ShouldQueue` with `tries = 5` and `backoff = 30`.

## Storage

Set `FILESYSTEM_DISK=s3` (or any driver that supports signed URLs). `UploadProjectFileAction` will pick it up automatically. `ProjectFile::temporaryUrl()` will start returning actual signed URLs instead of `null`.

Consider lifecycle rules on the bucket:

- Versioning ON — a checksum mismatch means something overwrote a file that the DB thought was immutable.
- Transition to infrequent-access tier after N days.
- MFA delete on the bucket policy if regulatory.

## Caching

The reference has **no bespoke caching layer**. When the dashboard or the API starts showing latency:

1. Profile first (`php artisan pail`, a local APM, or `LOG_LEVEL=debug` with timing logs).
2. Cache the expensive aggregate, not the whole view. Dashboard task counts and project counts are the usual suspects — `Cache::remember('dashboard:counts:'.$user->id, 60, fn() => ...)`.
3. Invalidate on the Action that changes the underlying data (e.g. `CompleteTaskAction` invalidates the assignee's cached counts).
4. Move to tagged caches only when the invalidation keys become hard to reason about.

Do not precache list endpoints — the filtering surface is too wide for the hit rate to justify the complexity.

## Rate limiting

`AppServiceProvider::boot` defines the `api` limiter:

```php
RateLimiter::for('api', function (Request $request) {
    $user = $request->user();

    return $user
        ? Limit::perMinute(60)->by((string) $user->id)
        : Limit::perMinute(20)->by($request->ip());
});
```

Tune the numbers based on real usage. Practical tips:

- Authenticated limits should be generous enough for the SPA to poll without hitting them.
- Unauthenticated limits should be tight enough to defeat credential stuffing — `POST /api/v1/auth/token` is additionally limited by `throttle:10,1`.
- When limits are not enough (actual abuse), escalate to the web server / CDN / WAF. Application-level limits are not a DDoS defence.

## Sessions and tokens

- `SESSION_DRIVER=database` keeps sessions portable between instances without extra infrastructure. `redis` is faster.
- Sanctum personal access tokens live in `personal_access_tokens`. They expire after 30 days by default (set in `AuthController::token`). Revoke on password change with `$user->tokens()->delete()`.
- The SPA cookie flow is active (`EnsureFrontendRequestsAreStateful` in `bootstrap/app.php`). Set `SANCTUM_STATEFUL_DOMAINS` to your real domain.

## Database

- Use a connection pooler (PgBouncer, ProxySQL) when you have more than a couple of workers plus a web tier.
- Index review: `clients.owner_id`, `projects.owner_id`, `projects.client_id`, `tasks.project_id`, `tasks.assignee_id`, `tasks.status`, `tasks.completed_at`, `activity_logs.subject_type,subject_id`, `activity_logs.created_at` — the migrations already add the obvious ones; review when query patterns evolve.
- Soft deletes mean you want `deleted_at` indexed if you frequently filter on it.

## Scaling path

In the order you are likely to need them:

1. Put a cache in front of the dashboard.
2. Move the queue to Redis and run multiple workers.
3. Move sessions to Redis.
4. Add a read replica and wire `DB_READ_HOST` / `DB_WRITE_HOST` for the paginated index endpoints.
5. Serve static assets from a CDN (`ASSET_URL=https://cdn.example`).
6. Split the API onto its own fleet once its traffic profile diverges from the web UI's.
7. Extract a dedicated search index (Meilisearch, Typesense) when `ILIKE '%term%'` stops cutting it.

Each step is reversible, and none of them require touching the domain layer.

## Security hardening checklist

- `APP_DEBUG=false` in production.
- HTTPS enforced — the provider does this via `URL::forceScheme('https')`.
- `SESSION_SECURE_COOKIE=true`.
- `SESSION_SAME_SITE=lax` (or `strict` if no cross-site callbacks).
- CSP headers via a middleware or the web server.
- `bcrypt` rounds raised (`BCRYPT_ROUNDS=12` by default) — measure before raising further.
- Automatic dependency updates (`composer audit`, `npm audit` in CI).
- Backups of the database and the storage disk.
- `composer outdated --direct` in CI to catch framework drift.

## What this app deliberately does not do

- No custom deploy tooling — use Forge, Vapor, Deployer or plain rsync.
- No Docker Compose for prod — left to the platform.
- No feature flags — nothing worth gating yet.
- No multi-tenancy — single-tenant deployment, one database.
