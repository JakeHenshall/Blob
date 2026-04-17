# Q & A

Questions a senior Laravel developer should be able to answer about this repository.

## 1. What makes this senior rather than mid?

The mid version proves you can structure a Laravel app cleanly. This version proves you can make the decisions that keep it shipping in production:

- a versioned JSON API backed by the same domain as the web UI
- Sanctum personal access tokens with a stateful SPA fallback
- rate limiting keyed per user, falling back to IP
- a storage-driver-agnostic upload pipeline with checksums, metadata capture and signed URLs
- transactional, idempotent actions with an activity audit trail
- project-scoped authorisation composing class-level and instance-level gate checks
- explicit `preventLazyLoading` outside production so N+1s are loud
- explicit HTTPS forcing in production
- PHPDoc that explains intent, thrown exceptions and idempotency, not just types
- written trade-offs for what was deliberately left out

## 2. Why expose an API and a web UI at once?

Because they are the same domain, and duplicating Form Requests, Policies, Actions and Services to build a parallel API codebase is the fastest way to create subtle divergence. In this app only the controllers differ — everything behind them is shared. That means:

- a fix to `ProjectPolicy::view` takes effect on the web UI, the API, and any future channel simultaneously
- authorisation logic has a single source of truth
- adding a new channel (a Livewire widget, a job, another API version) is a controller file, not an architectural event

## 3. Why URL-based versioning for the API?

Because it is the simplest thing that works for HTTP clients and caches. Header-based versioning (`Accept: application/vnd.clienthub.v2+json`) is more elegant but trips over proxies and CDNs and makes opening a URL in a browser painful. Sunsetting v1 becomes a 410 on the route prefix rather than header sniffing.

## 4. Why Sanctum for tokens instead of Passport?

Sanctum solves the two cases this app actually has:

- personal access tokens for CLI / server-to-server use
- stateful cookie auth for a same-origin SPA front-end

Passport (full OAuth2) is overkill unless we are issuing tokens to third-party applications on behalf of our users. If and when we do, we swap.

## 5. Why capture file metadata **before** `storeAs`?

Because `UploadedFile::storeAs()` moves the temp file, after which `getRealPath()` no longer points to anything readable. `hash_file('sha256', $upload->getRealPath())` would silently return `false` and the checksum column would be `null` forever. An earlier version of this code had that bug — it is now captured up front in `UploadProjectFileAction::checksum()`.

## 6. Why are some mutations inline in controllers and others in Actions?

A mutation moves into an Action when any of these are true:

- it opens a DB transaction
- it has side effects (notifications, activity log, external calls)
- it needs to be idempotent
- it is called from more than one controller (e.g. web + API)

If none of those apply, it is a single Eloquent call and it stays in the controller. Wrapping `$project->update($data)` in an `UpdateProjectAction` does not buy anything and costs a file.

## 7. What authorisation regression did you prevent, and how?

`TaskPolicy::create` used to `return true;` for every authenticated user, and `StoreTaskRequest::authorize` only checked class-level `create`. Combined, any authenticated user could POST tasks into any project they could not see. The fix:

- `TaskPolicy::create(User $user, ?Project $project = null)` returns `false` for non-staff unless the project argument is present and the user owns it.
- `StoreTaskRequest::authorize` composes two checks — `$user->can('view', $project)` and `$user->can('create', [Task::class, $project])` — so a user who cannot see the project cannot create tasks on it even if the class-level gate passes.

The lesson is structural: always pass the parent resource into a scoped ability check when one exists.

## 8. Why is there a `NotePolicy` at all?

Earlier iterations checked deletion inline in `NoteController::destroy` with an `abort_unless`. That works until the same rule shows up in a second place (an API endpoint, a queue job, a Blade button). A Policy gives us:

- one place to change the rule
- `@can('delete', $note)` in Blade
- `$this->authorize('delete', $note)` in the controller
- `$user->can('delete', $note)` in a Form Request if we later add one

The delta is tiny but the ergonomics compound.

## 9. Why does the activity log live in a service, not a trait or event listener?

A service is the simplest shape that matches the intent: "an action decided this event is worth remembering, write it down." A trait would bolt the concern onto a model and couple persistence to audit. An event + listener split the decision ("should this be logged?") from the action that already knows the answer, which makes the log noisy and harder to change.

Putting the decision in the Action, and the mechanics in a service, keeps the log curated and the call site honest.

## 10. Why `Model::preventLazyLoading(! app()->isProduction())`?

Because lazy loading is almost always an accident. Outside production it throws, so:

- tests fail on the PR that introduced the N+1
- developers hit the error locally and add the `with(...)` call before the page ships
- production never throws on the pattern, because the fix has already been made

If production did throw, one missed eager load would take the page down. Dev-strict, prod-forgiving is the right balance.

## 11. Where would you cache first?

The dashboard. `DashboardController` runs several count queries on every request, and the numbers change slowly relative to how often they are viewed. The plan:

1. Identify the specific queries (`php artisan pail` + a probe).
2. `Cache::remember('dashboard:counts:'.$user->id, 60, fn () => ...)`.
3. Invalidate from the relevant Actions (`CompleteTaskAction`, `CreateProjectAction`) rather than on every write to the tables — the cache is about the aggregate, not the row.

I would not cache list endpoints. The filter surface is too wide for the hit rate to pay back the complexity.

## 12. Why per-user rate limits instead of per-IP?

Because IP-based limits punish shared egress (corporate NAT, cloud provider IPs) and invite trivial evasion from attackers on residential dynamic IPs. Per-user limits are a fair signal once the request is authenticated. Unauthenticated requests still fall back to per-IP — there is no other key available.

## 13. Why is deletion mostly admin-only?

Because delete + audit trail rarely tells the full story, and archiving is what users actually want when they click Delete on a client or project. `ClientController::destroy` is admin-only; `ArchiveClientAction` is the user-facing verb. Tasks are deletable by managers/admins because a deleted task is usually a data-entry mistake, not a lifecycle event.

## 14. How would you add multi-tenancy?

With a column, not a database-per-tenant split, for this scale:

1. Add `tenant_id` to every domain table.
2. Add a global scope on each model that filters by `request()->user()->tenant_id`.
3. Add the column to every Form Request's authorisation check.
4. Add it to the factories and seeders.
5. Add a feature test that creates two tenants and verifies data isolation through the HTTP layer.

Database-per-tenant is worth it only when the cost of a tenant column leak is unacceptable (regulated workloads, or tenants with vastly different schemas).

## 15. How would you introduce events/event sourcing?

I wouldn't yet. The activity log is already an append-only fact stream; it is read by humans, not replayed into state. Event sourcing becomes interesting when:

- business state genuinely is a fold over events (ledger, inventory, versioned documents)
- multiple projections of the same facts are needed

Neither applies to a CRM workflow at this size.

## 16. Why do Form Requests do the authorisation?

Because the failure mode of forgetting a `$this->authorize(...)` call in the controller is silent and bad (a mutation succeeds when it shouldn't). The failure mode of a Form Request whose `authorize()` returns `false` is `403` before the action runs. Moving authorisation to the Form Request makes it impossible to wire up a mutation route without picking an ability.

## 17. What is in `Support/` and why?

Enums only:

- `Role` (admin, manager, user)
- `ProjectStatus`
- `TaskStatus`
- `TaskPriority`

These are casting targets for Eloquent models and are used across the codebase. `Support/` is not a dumping ground — if it grows beyond enums I would split it.

## 18. Why not use Laravel Echo / broadcasting?

Because nothing in the current feature set needs a live channel. `TaskAssignedNotification` is a one-off email, not a stream. If a real-time dashboard shipped, Echo + the `broadcast` channel on the notification would be a tidy addition — but only when the product asks for it.

## 19. What would you change if the app grew much larger?

- Extract dashboard and list queries into read-model query classes.
- Move the admin override into a Policy base class `before()` hook.
- Split the activity log into a dedicated write service with its own retention policy.
- Move long-running uploads to a direct-to-S3 signed-URL flow instead of posting through the app.
- Introduce a dedicated search index (Meilisearch / Typesense) when `ILIKE '%term%'` stops cutting it.
- Split the API onto its own deployment once its traffic profile diverges from the web UI's.

Each of these is reversible and only earns its place when the symptoms are real.

## 20. What tradeoffs did you make to keep this maintainable?

- **Single repo, single deployment.** Cost: a busy API shares CPU with a slow Blade page. Reversible by splitting the fleet.
- **Eloquent everywhere, no repository.** Cost: swapping the persistence layer later is more work. Upside: no duplicated query API, full access to scopes and paginator.
- **Activity log is append-only with no retention policy.** Cost: the table grows forever. Upside: audit trail is a simple fact.
- **No custom exception envelopes on the API.** Cost: clients live with Laravel's default error shapes. Upside: no bespoke error format to document.
- **PHPDoc, not OpenAPI.** Cost: no machine-readable contract for external consumers. Upside: no second source of truth to keep in sync.

Each choice is written down, not implicit. That is the senior part.
