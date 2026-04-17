# Testing

The test runner is PHPUnit 12. Run the whole suite:

```bash
php artisan test
```

In CI or locally you should see:

```
Tests:    20 passed (55 assertions)
```

Tests use an in-memory SQLite database (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:` in `phpunit.xml`) and the `sync` queue driver so notifications are dispatched synchronously and can be asserted with `Notification::fake()`.

## File layout

```
tests/
  TestCase.php
  Feature/
    Api/
      ProjectApiTest.php
    AuthTest.php
    ClientTest.php
    FileUploadTest.php
    ProjectWorkflowTest.php
  Unit/
    ArchiveClientActionTest.php
    CreateProjectActionTest.php
```

## Feature tests

Feature tests drive the app through HTTP. They are the primary form of testing here because they exercise routing, middleware, Form Requests, Policies, controllers, Actions, and database state in a single run.

They cover:

- **Auth** — guest redirects, registration, login happy-path.
- **Clients** — role-based create, cross-user view denial, admin override.
- **Projects** — manager creates a project, assignment notifies the assignee (queued), task completion flow.
- **File uploads** — stored file + activity row, disallowed MIME rejected.
- **API** — unauthenticated rejection, manager list, user scope enforcement, task completion via token.

`Notification::fake()` is used wherever we care that a specific notification was dispatched to a specific user. We do not unit-test the queue driver or the mail template — that is framework territory.

## Unit tests

Unit tests exist for Actions that have branching logic worth proving directly:

- `ArchiveClientActionTest` — archives active projects, is idempotent on already-archived clients.
- `CreateProjectActionTest` — generates a unique slug per client, writes an activity row.

Policies can be unit-tested directly when the branches multiply. At current size the feature tests already exercise every important branch, so we haven't duplicated them at the unit level.

## What is deliberately not tested

- **Blade rendering detail** beyond presence/absence of a string. We do not screenshot-test the UI.
- **Every Policy method branch** — where the Feature tests cover the branch via an HTTP flow, we do not duplicate.
- **Framework behaviour** — migrations, middleware, mail drivers, the queue worker itself.
- **Rate limiting numbers** — testing these is brittle and provides little signal. The presence of `throttle:10,1` on the token route is visible in `routes/api.php`.

## How to choose between Feature and Unit

- "Can role X do thing Y?" → Feature test.
- "Does Action A perform steps 1, 2 and 3?" → Unit test, especially if it has branches.
- "Does the enum label come out right?" → Unit test.
- In doubt → Feature test. The ROI is almost always higher.

## Senior-specific test patterns

### Assert the activity log row

```php
$this->assertDatabaseHas('activity_logs', [
    'event' => 'project.file_uploaded',
]);
```

Every Action writes exactly one activity row per call. Asserting it catches both "the side effect did not fire" and "the event name drifted".

### Assert a queued notification

```php
Notification::fake();
// ... action runs ...
Notification::assertSentTo($assignee, TaskAssignedNotification::class);
```

Because the queue driver is `sync` in tests, `ShouldQueue` notifications still hit the fake.

### Assert API pagination shape

```php
$this->getJson('/api/v1/projects')
    ->assertOk()
    ->assertJsonStructure([
        'data' => [ ['id', 'name', 'status'] ],
        'meta', 'links',
    ]);
```

Testing the envelope is cheap and protects the wire-format contract.

### Sanctum::actingAs

```php
Sanctum::actingAs($user);
$this->getJson('/api/v1/projects');
```

Exercises the token guard without fabricating a real token. Use this instead of `actingAs` for API tests.

## Adding new tests

- Follow the feature-first principle.
- Use `RefreshDatabase` in every test that touches the DB.
- Use `Sanctum::actingAs` for API routes, `actingAs` for web routes.
- Use `Notification::fake()` whenever an Action is expected to notify.
- Assert `activity_logs` whenever an Action is expected to record.
