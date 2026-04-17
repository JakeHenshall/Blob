# Q & A

Questions a mid-level Laravel developer should be able to answer about this repository.

## 1. Why did you introduce roles and policies here?

Because the junior version had one kind of user and no meaningful access control. A CRM-style app needs at least three shapes of behaviour: someone who can do anything, someone who manages their own book of work, and someone who can only see what they're involved with. Roles name those shapes. Policies decide what each shape is allowed to do.

## 2. Why use Policies instead of checking roles inline?

Three reasons:

- **Central** — every rule for a resource lives in one file.
- **Testable** — Policies are plain classes with no framework ceremony to mock.
- **Search-friendly** — if someone changes the rule for editing a project, it's one file, not twelve.

Inline `if ($user->role === 'admin')` scatters rules across controllers and views and makes changes risky.

## 3. Why did you add Action classes?

To keep workflows out of controllers. `CreateProjectAction` persists the project, writes an activity log entry and fans out notifications to Admins and Managers — three concerns that belong together in one atomic operation. Doing that inside a controller would make the controller hard to read and impossible to reuse.

## 4. Why not use Action classes for every CRUD method?

Because `$project->update($request->validated())` is already a single line that tells the whole truth. Wrapping it in an Action would add overhead and hide what's happening. Actions earn their place when a workflow involves **coordination** — multiple side effects, transactions, notifications, or non-obvious business rules.

## 5. Why not use the repository pattern?

Eloquent is already an abstraction over the database. Adding a repository layer in an Eloquent app almost always:

- duplicates the model API
- loses scopes, eager loading and paginator conveniences
- forces test doubles on code that runs fine against in-memory SQLite

If we were sourcing data from an external service, a repository would pull its weight. With Eloquent, it doesn't.

## 6. What belongs in a controller in this app?

- Authorisation invocations (usually delegated to a FormRequest).
- Query shaping for the view (filtering, sorting, eager loading, pagination).
- Calling an Action when there's real business logic.
- Returning a redirect or a view.

Controllers should read like a page of prose. If you can't, extract.

## 7. What belongs in a model?

- Relationships.
- Casts (enums, dates).
- Query scopes.
- Small, pure domain methods (`Task::isOverdue()`, `TaskStatus::isOpen()`).

Not: side effects, notifications, logging, validation.

## 8. What belongs in an Action?

- A workflow with multiple steps (persist + log + notify, for example).
- A DB transaction boundary if the workflow writes to more than one table.
- Idempotency where it matters (e.g. `CompleteTaskAction` no-ops on already-completed tasks).

Not: fetching data for a view, or one-line CRUD.

## 9. Why queue notifications?

Because emails are slow, and the user shouldn't wait for them. Making `TaskAssignedNotification` and `ProjectCreatedNotification` implement `ShouldQueue` means the HTTP response returns immediately and a worker processes the email later.

It also isolates failures: a bad SMTP server won't 500 the request, it'll just leave a job on the queue to retry.

## 10. Why use the database queue driver?

- Zero extra infrastructure — the same SQLite/MySQL/Postgres you already have runs the queue.
- Durable — jobs survive a restart.
- Plenty for this app's volume.

At higher scale you'd switch to Redis or SQS, but for a reference app the database driver is the simplest defensible choice.

## 11. Why add activity logging?

Because "who did what and when" is useful context for any app where more than one person edits shared data. The activity log powers the dashboard feed and gives us a thin audit trail. We only log events that have real domain meaning (`client.created`, `project.created`, `task.assigned`, `task.completed`, `task.unassigned`), not every CRUD call.

## 12. Why use soft deletes here?

Because Clients, Projects and Tasks usually shouldn't vanish the moment someone clicks Delete. A client being "archived" is the common shape of delete in this kind of app; the data is often needed for reporting or reactivation later.

Notes and Activities do **not** use soft deletes. A note is a throwaway comment. An activity is an immutable fact.

## 13. How did you choose what to test?

Start from user-facing behaviour. For each resource:

- One happy-path feature test that creates/updates and asserts database state.
- At least one authorization test that proves a Policy bite.
- At least one test that exercises a filter, so the index remains useful.

Then unit-test anything that has branches and is easier to exercise directly than through HTTP: Policies, Actions and enums.

## 14. Why use feature tests for these flows?

Because a feature test proves the real stack works: route → middleware → FormRequest → Policy → controller → Action → Eloquent → notification. A unit test for the controller alone would pass even if the route wasn't wired up. For a CRUD app, feature tests are the highest-ROI tool.

## 15. Why add a few unit tests too?

- Speed: enum tests run in milliseconds and catch typos instantly.
- Branch coverage: `ProjectPolicyTest` exercises each branch of `update()` explicitly, which would take several HTTP-level tests to do equivalently.
- Documentation: a unit test against `CompleteTaskAction` reads like a spec.

## 16. How would you extend this app safely?

- Add a new resource → migration + model + factory + policy + form requests + controller + views + feature tests.
- Add a new domain event → log it with a new `action` string and (optionally) a new notification.
- Add a new role → add the enum case, update Policies, and run the test suite.
- Add background work → write a queued Job; keep Actions thin wrappers that dispatch the Job.

## 17. What would you change if the app grew much larger?

- Extract dashboard and list queries into read-model query classes.
- Move admin override into a Policy base class `before()`.
- Consider Livewire for the forms once they get more interactive.
- Split the activity log into a dedicated service if logging rules get complex.
- Introduce an API (see Q18) rather than forcing Blade views to do double duty.
- Cache dashboard counts if they get expensive.

## 18. How would you add an API later?

- Install Sanctum.
- Add API Resources (`ProjectResource`, `TaskResource`) for response shaping.
- Introduce an `api` route group that reuses the existing Form Requests, Policies and Actions — only the controllers become thin JSON adapters.
- Don't duplicate authorization or validation logic; this is exactly why Policies and FormRequests are worth having.

## 19. What tradeoffs did you make to keep this maintainable?

- **No repository layer** — gain: simplicity. Cost: if we swap Eloquent out, we rewrite more.
- **Inline query scopes** in controllers — gain: readability. Cost: repeating the same filter chain across controllers if it grows.
- **Policies keyed by role+ownership** — gain: obvious rules. Cost: expressing anything more nuanced (per-team permissions, ABAC) requires a rework.
- **Activity log is append-only** — gain: simple audit trail. Cost: no update semantics, no retention policy.

Each choice is reversible if the app grows.

## 20. What makes this mid-level rather than junior?

- There's a real authorization design, not just auth.
- Controllers are thin on purpose.
- There's an explicit division between models (shape), actions (workflows) and policies (rules).
- Notifications are queued, not blocking.
- Tests cover flows and rules, not just the framework.
- The code reads like it was written by someone who's been burnt by bad structure once.

It's not senior because it doesn't make explicit decisions about caching, rate limiting, observability, API design or scaling. Those are all valid next steps; see [difference-from-senior.md](difference-from-senior.md).
