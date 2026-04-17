# Testing

The test runner is Pest. Run the whole suite:

```bash
php artisan test
```

In CI or locally you should see something like:

```
Tests:    28 passed (62 assertions)
```

Tests use an in-memory SQLite database (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:` in `phpunit.xml`) and the `sync` queue driver, so notifications are dispatched synchronously and can be faked with `Notification::fake()`.

## File layout

```
tests/
  Pest.php                     ← bootstraps feature + unit suites and adds actingAs* helpers
  TestCase.php
  Feature/
    Auth/AuthenticationTest.php
    ClientTest.php
    DashboardTest.php
    ProjectTest.php
    TaskTest.php
  Unit/
    Actions/CompleteTaskActionTest.php
    Enums/TaskStatusTest.php
    Policies/ProjectPolicyTest.php
```

## Feature tests

Feature tests drive the app through HTTP. They are the primary form of testing in this app because they exercise routing, middleware, FormRequests, Policies, controllers, Actions and database state in one go.

They cover:

- **Auth** — login/logout happy-path and invalid credentials.
- **Dashboard** — guest redirect, authenticated render with overdue and my-tasks data.
- **Clients** — role-based create, view gating for non-owners, admin override, search.
- **Projects** — queued notification on create, activity log, ownership-based update denial, status filter, note creation.
- **Tasks** — assignment with queued notification and activity, assignment denial, completion flow, assignee updates, "mine + due_soon" filter.

`Notification::fake()` is used wherever we care that a specific notification was dispatched to a specific user. We don't try to unit-test the queue or the mail template — that's framework territory.

## Unit tests

Unit tests exist in two situations:

1. **Pure logic** that has no value being reached through HTTP (e.g. enums).
2. **Actions and Policies** that benefit from being exercised directly, faster than full HTTP tests.

Examples:

- `CompleteTaskActionTest` — proves the action is idempotent.
- `ProjectPolicyTest` — proves each authorization branch.
- `TaskStatusTest` — proves the enum helper methods.

Actions that fan out notifications could also be unit-tested with `Notification::fake()`; we chose to exercise that path through the Feature tests to keep the unit layer small.

## What is deliberately not tested

- **Blade rendering detail** beyond presence/absence of a string. We do not screenshot-test the UI.
- **Every Policy method branch** — where the Feature tests already cover the branch via an HTTP flow, we don't duplicate.
- **Framework behaviour** — migrations, middleware, mail drivers, etc.
- **The queue worker itself** — we trust Laravel's queue.

## How to choose between Feature and Unit

- If the test asks "can role X do thing Y?" — Feature test.
- If the test asks "does the action do step A, B and C?" — Unit test is fine, especially if there are branches.
- If the test asks "does the enum label come out right?" — Unit test.
- If in doubt, Feature test. The ROI is almost always higher.

## Adding new tests

- Prefer Pest's `test('...')` and `it('...')` styles for readability.
- Use the `actingAsAdmin()`, `actingAsManager()`, `actingAsUser()` helpers from `tests/Pest.php`.
- Use `Notification::fake()` whenever an Action is expected to notify.
- Use `assertDatabaseHas('activities', [...])` to assert the activity log wrote the expected row.
