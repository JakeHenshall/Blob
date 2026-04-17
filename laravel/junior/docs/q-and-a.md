# Questions and answers

A short, plain-English walkthrough of the kind of questions a junior Laravel
developer is likely to be asked about this app.

## 1. What does this app do?

It's a small tool for tracking clients, their projects, and the tasks inside
those projects. You sign in, add a client, create projects for that client,
and log tasks with statuses and due dates. The dashboard summarises everything
at a glance.

## 2. Why did you build this app?

To demonstrate solid Laravel fundamentals in one project: MVC, CRUD,
relationships, validation, auth, seeders, and tests. The domain (clients →
projects → tasks) maps cleanly to `hasMany`/`belongsTo`, so it's a good vehicle
for showing that I understand how Laravel pieces fit together.

## 3. Why did you choose Laravel?

- Batteries-included: routing, ORM, validation, auth, testing, and migrations
  all come in the box.
- Great documentation and a strong convention-over-configuration story, so
  the code stays predictable as it grows.
- Blade + Eloquent make server-rendered CRUD apps very quick to build without
  needing a separate frontend stack.

## 4. What is MVC in Laravel?

**Model–View–Controller** is a way of separating responsibilities:

- **Model** — the data and how it relates to other data (here: `Client`,
  `Project`, `Task`).
- **View** — what the user sees (Blade templates in `resources/views`).
- **Controller** — the glue. It takes a request, asks the model for data,
  and returns a view or a redirect.

Laravel adds a router on top: URLs map to controller methods, which talk to
models and render views.

## 5. What does a route do?

A route maps an HTTP URL and method to some code. For example:

```php
Route::resource('clients', ClientController::class);
```

registers seven routes (`GET /clients`, `POST /clients`,
`GET /clients/{client}`, etc.) and points each one at a method on
`ClientController`.

## 6. What does a controller do?

A controller takes an incoming request, does a small amount of work (query
the database, validate input, decide what to do), and returns a response —
either a view, a redirect, or JSON. Controllers in this app are deliberately
thin: most of the validation lives in Form Requests, and the data access is
in Eloquent relationships.

## 7. What does a model do?

A model represents one row of a database table as a PHP object. It also
declares relationships (e.g. "a Project belongs to a Client") and which
columns are safely mass-assignable. In this app, `Project::tasks()` and
`Client::projects()` are the relationships you'll see used the most.

## 8. What is a migration?

A migration is a PHP file that describes a change to the database schema —
for example, "create a `tasks` table with these columns". Migrations are
version-controlled, so every developer and every environment ends up with the
same schema.

Run them with `php artisan migrate`. Roll back with `php artisan migrate:rollback`.

## 9. What is a seeder?

A seeder fills the database with example data. `DatabaseSeeder` in this app
creates two users, a handful of clients for the demo user, and related
projects and tasks. Run it with `php artisan db:seed` (or
`php artisan migrate --seed`).

## 10. What is a factory?

A factory generates fake model instances for seeding and testing. It says:
"when you ask for a `Client`, give it a realistic name, company, and email."
Factories can be chained with `for()` to build related records, e.g.
`Project::factory()->for($client)->create()`.

## 11. Why use Form Requests?

Because they keep controllers clean and validation reusable. A Form Request
like `StoreClientRequest`:

- has an `authorize()` method that decides if the user may do this action,
- has a `rules()` method that declares the validation rules,
- automatically rejects invalid input before the controller runs.

The controller then calls `$request->validated()` and gets back a clean,
trusted array.

## 12. What is validation?

Validation is the check that incoming data is shaped the way we expect —
required fields are present, emails look like emails, foreign keys point at
rows the user owns, dates are real dates, etc. Laravel's validator collects
all the errors at once so the form can show every problem at the same time.

## 13. Why use Blade templates?

Blade is Laravel's simple, server-rendered template engine. It's a good
choice here because:

- the app is server-driven (no SPA needed),
- Blade components (`x-card`, `x-status-badge`, `x-flash`) are an easy way
  to share bits of UI,
- it's familiar to anyone who's written PHP or HTML.

## 14. What is route model binding?

When a route has a wildcard like `{client}`, Laravel automatically looks up
that record by its primary key and injects the `Client` model straight into
the controller. So instead of `Client::findOrFail($id)` in every method, you
just type-hint `Client $client` and Laravel does the fetch — and returns a
404 if it doesn't exist.

## 15. How are the models related?

```
User
 └── hasMany → Client
         └── hasMany → Project
                 └── hasMany → Task
```

So:

- `User::clients()` returns that user's clients.
- `Client::projects()` returns that client's projects.
- `Project::tasks()` returns that project's tasks.

`Client` also has a convenience `hasManyThrough(Task)` so you can jump from a
client straight to all of their tasks.

## 16. Why did you use SQLite?

Because it's zero-config. You can clone the repo, run `migrate --seed`, and
you're running — no MySQL container, no local server. The app uses only
standard Eloquent queries, so swapping in MySQL or Postgres for production
is just a `.env` change.

## 17. What tests did you add?

Pest feature tests that exercise the important user journeys:

- dashboard renders for authenticated users and redirects guests,
- you can list, search, create, validate, update, and delete clients,
- you can list and filter projects, and can't attach one to someone else's
  client,
- you can create tasks, mark them done (which sets `completed_at`) and reopen
  them (which clears it), and can't move one into a stranger's project.

I didn't try to test every getter and every edge case — the goal was to
cover the flows that a real user would actually hit.

## 18. What would you improve next?

- Move ownership checks into proper `Policy` classes and use `Gate`/`authorize()`.
- Add per-project and per-client activity feeds.
- Add soft deletes on clients and projects, with a "restore" action.
- Add basic charts to the dashboard (tasks completed over time).
- Introduce an API layer (Sanctum + JSON resources) for a future mobile app.
- Add a role column / policy layer so you can have read-only users.

Those are the mid-level version's territory.

## 19. What was the hardest part?

Keeping the codebase simple. It's tempting to add services, repositories, and
action classes "because real apps have them", but at this scale they just add
indirection. The challenge was restraining myself to the vanilla Laravel
tools the framework already gives you.

## 20. How would you add another module later?

Same pattern as the existing three resources:

1. `php artisan make:model Invoice -mfs` — model, migration, factory, seeder.
2. `php artisan make:controller InvoiceController --resource --model=Invoice`.
3. `php artisan make:request StoreInvoiceRequest` and `UpdateInvoiceRequest`.
4. Add `Route::resource('invoices', InvoiceController::class)` to
   `routes/web.php`.
5. Add Blade views under `resources/views/invoices/`.
6. Add a nav link in `resources/views/layouts/navigation.blade.php`.
7. Add feature tests in `tests/Feature/InvoiceTest.php`.

That's the rhythm the whole app is built on.
