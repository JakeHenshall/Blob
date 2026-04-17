# API

The app exposes a first-class JSON API under `/api/v1`. It shares Form Requests, Policies, Actions and Services with the web UI; only the controllers and the JsonResource classes are API-specific.

## Authentication

Sanctum personal access tokens. Issue a token with email + password + a device name:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/token \
  -H 'Accept: application/json' \
  -d 'email=manager@clienthub.test&password=password&device_name=cli'
```

Response:

```json
{
  "token": "1|3b2e...redacted",
  "expires_at": "2026-05-17T12:34:56+00:00",
  "user": {
    "id": 2,
    "name": "Morgan Manager",
    "email": "manager@clienthub.test",
    "role": "manager"
  }
}
```

Tokens expire after 30 days. Use the `Authorization: Bearer <token>` header on every subsequent call:

```bash
curl http://127.0.0.1:8000/api/v1/projects \
  -H 'Accept: application/json' \
  -H 'Authorization: Bearer 1|3b2e...redacted'
```

The SPA cookie flow (`EnsureFrontendRequestsAreStateful`) is also wired up in `bootstrap/app.php`, so a same-origin front-end can authenticate via session + XSRF-TOKEN without issuing a personal access token.

## Versioning

Routes are prefixed `/api/v1`. There is no content negotiation — the version lives in the URL because it is the simplest thing that works for HTTP clients and CDNs.

When a breaking change is needed:

1. Copy the relevant controllers to `App\Http\Controllers\Api\V2`.
2. Add `/api/v2/...` routes alongside the v1 ones.
3. Deprecate v1 with a `Sunset` header and a timeline.
4. Remove v1 after the deprecation window.

Form Requests, Policies and Actions are versionless — they encode domain rules, not wire format.

## Endpoints

### Auth

| Method | Path                   | Notes                                      |
| ------ | ---------------------- | ------------------------------------------ |
| POST   | `/api/v1/auth/token`   | Issue token. `throttle:10,1`.              |
| GET    | `/api/v1/auth/me`      | Return the authenticated user.             |
| POST   | `/api/v1/auth/logout`  | Revoke the current access token.           |

### Projects

| Method | Path                            | Notes                                                    |
| ------ | ------------------------------- | -------------------------------------------------------- |
| GET    | `/api/v1/projects`              | Paginated list. Query: `q`, `status`, `per_page` (1-100).|
| GET    | `/api/v1/projects/{project}`    | Single project.                                          |

### Tasks

| Method | Path                                      | Notes                                        |
| ------ | ----------------------------------------- | -------------------------------------------- |
| GET    | `/api/v1/projects/{project}/tasks`        | Paginated list. Query: `status`, `open`.     |
| POST   | `/api/v1/projects/{project}/tasks`        | Create. Returns 201 + `TaskResource`.        |
| POST   | `/api/v1/tasks/{task}/complete`           | Idempotent completion. Returns `TaskResource`.|

## Response shaping

All API responses go through `JsonResource` classes (`ProjectResource`, `TaskResource`). That means:

- Field names are stable across the API surface.
- Nested relationships only appear when they have been loaded on the server (`whenLoaded`).
- Aggregate counts only appear when the caller asked for them (`whenCounted`).

A resource is a wire-format contract. Changing its shape is a breaking change; adding a field is not.

## Pagination

List endpoints use Laravel's paginator. The API response is always:

```json
{
  "data": [ ... ],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta":  { "current_page": 1, "per_page": 20, "total": 43, ... }
}
```

`per_page` is clamped to `[1, 100]` in every list controller to prevent accidental DoS via huge page sizes.

## Rate limiting

Three rate limits apply:

- Token issuance: `throttle:10,1` (10 requests / minute / route) — guards against credential stuffing.
- Default API: defined in `AppServiceProvider::boot` via `RateLimiter::for('api', ...)`:
  - Authenticated: 60/min per user id
  - Unauthenticated: 20/min per IP
- Applied automatically to every `/api/...` request by `$middleware->throttleApi()` in `bootstrap/app.php`.

Responses exceeding the limit return `429 Too Many Requests` with `Retry-After` and `X-RateLimit-*` headers.

## Errors

Standard Laravel error shapes:

- `401 Unauthorized` — missing/invalid token.
- `403 Forbidden` — Policy denied.
- `404 Not Found` — missing or soft-deleted resource.
- `422 Unprocessable Entity` — Form Request validation failed. Body contains `errors` keyed by field.
- `429 Too Many Requests` — rate limit hit.

There is no custom exception handling beyond what Laravel provides — the defaults are correct for this surface, and a bespoke `{ "error": { ... } }` envelope would just cost clients a level of nesting with no payoff.

## What is deliberately not in the API

- **Client, note and file endpoints** — the UI writes these; exposing them over the API has not been requested by any consumer yet. Add on demand.
- **Admin-only endpoints** (user management, activity feed) — same rationale.
- **Cursor pagination** — offset paginator is fine for current volumes. Revisit when a list endpoint returns more than a few thousand rows consistently.
- **OpenAPI/Swagger** — PHPDoc on the controllers documents each endpoint. An OpenAPI spec is worth generating when external consumers need a machine-readable contract.
