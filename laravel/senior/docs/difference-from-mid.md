# How this version differs from the Mid version

This senior version keeps the same domain — clients, projects, tasks, notes — but adds the surfaces and decisions that separate a maintainable codebase from a production one.

Compared with the mid version, this version adds:

- **A versioned JSON API.** `routes/api.php` exposes `/api/v1` with Sanctum personal access tokens and a stateful SPA cookie flow. JSON Resources (`ProjectResource`, `TaskResource`) shape every response.
- **Rate limiting that matters.** Per-user when authenticated, per-IP otherwise; tight on token issuance; standard `X-RateLimit-*` headers on every response.
- **Project file uploads.** `UploadProjectFileAction` captures sha256, MIME and size **before** the temp file is moved, persists a `ProjectFile` row and an activity entry atomically, and works against any configured filesystem disk.
- **Services layer.** `ActivityLogger` is the one service in the app — a stateless utility that actions call. It is not a catch-all bucket.
- **Project-scoped gate checks.** `TaskPolicy::create` takes an optional `Project`; `StoreTaskRequest::authorize` composes `view` + `create` on the project, so a user who cannot see a project cannot create tasks in it.
- **Delegated policies.** `NotePolicy` and `ProjectFilePolicy` delegate their `view` decision to `ProjectPolicy`, so "who can see a project" lives in one place.
- **Role middleware alias.** `EnsureUserHasRole` is aliased as `role` for coarse-grained route gating.
- **Defensive defaults.** `Model::preventLazyLoading` outside production; forced HTTPS in production; standard Sanctum stateful SPA wiring.
- **Richer PHPDoc.** Every Action, Service, Policy, middleware and API controller has class- and method-level docs that explain intent, idempotency, thrown exceptions, and parameter shapes.
- **Written trade-offs.** [docs/architecture.md](architecture.md), [docs/production.md](production.md), [docs/q-and-a.md](q-and-a.md) and [docs/api.md](api.md) all explicitly name what was deliberately **not** built and why.

What the mid version does well and this version inherits:

- thin controllers
- Policies, not inline role checks
- Actions for workflows, not CRUD
- queued notifications
- activity logging
- soft deletes where they help

What this version does **not** add:

- CQRS, event sourcing, or a message bus.
- Livewire, Inertia or any interactive client-side layer beyond Alpine.
- Multi-tenancy.
- A bespoke error envelope on the API.
- OpenAPI/Swagger. PHPDoc on the controllers documents each endpoint.
- Feature flags, canary deploys, or custom deploy tooling.

The mid version is focused on clean application structure.
The senior version is focused on production readiness and engineering judgement under real-world constraints — authorisation, observability, API design, storage — with the trade-offs written down.
