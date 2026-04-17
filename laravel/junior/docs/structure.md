# Project structure

A short tour of the pieces that matter, with a beginner-friendly explanation
of what each directory does.

## Routes (`routes/web.php`)

All app routes live here. The interesting bits:

- `GET /` — public welcome page
- `GET /dashboard` — the stats dashboard (`auth`, `verified`)
- `Route::resource('clients', ClientController::class)` — full CRUD
- `Route::resource('projects', ProjectController::class)` — full CRUD
- `Route::resource('tasks', TaskController::class)` — full CRUD
- Profile + Breeze auth routes (login, register, etc.)

`Route::resource()` is Laravel shorthand that wires up the seven standard CRUD
routes (`index`, `create`, `store`, `show`, `edit`, `update`, `destroy`) in
one line.

Route **model binding** is used everywhere: when a route has `{client}`,
Laravel automatically fetches that `Client` by ID and injects it into the
controller method.

## Controllers (`app/Http/Controllers`)

- `DashboardController` — a single invokable controller that gathers stats
  and returns the dashboard view.
- `ClientController`, `ProjectController`, `TaskController` — resource
  controllers. Each has the standard seven methods.
- Ownership is checked inline with small `authorizeClient/Project/Task`
  helpers that `abort(403)` if the record doesn't belong to the logged-in
  user. This is kept simple on purpose — in a mid-level build this would move
  to a `Policy`.

The controllers are deliberately readable: they query via Eloquent, call
`validated()` on a Form Request, and return a redirect or a view.

## Models (`app/Models`)

Each model lists its `$fillable` fields, `casts()` for dates, and its
relationships:

- `User` → `hasMany(Client)`
- `Client` → `belongsTo(User)`, `hasMany(Project)`, `hasManyThrough(Task)`
- `Project` → `belongsTo(Client)`, `hasMany(Task)` (plus a `STATUSES` const)
- `Task` → `belongsTo(Project)` (plus a `STATUSES` const and `isDone()`
  helper)

## Form Requests (`app/Http/Requests`)

One `Store…Request` and one `Update…Request` per resource. Each provides:

- `authorize()` — a simple ownership check
- `rules()` — Laravel validation rules using arrays of strings and
  `Rule::in(...)` / `Rule::exists(...)` for enums and foreign keys

Using Form Requests keeps controllers tiny — the controller just calls
`$request->validated()` to get clean, type-checked input.

## Migrations (`database/migrations`)

One migration per table, using the standard Schema builder. Noteworthy:

- `clients` has a `user_id` foreign key
- `projects` has a `client_id` foreign key
- `tasks` has a `project_id` foreign key

All foreign keys use `cascadeOnDelete` so deleting a client cleans up its
projects and their tasks automatically.

## Factories (`database/factories`)

Each factory uses Faker to produce realistic sample records. Factories are
chained in the seeder and reused in tests. `ProjectFactory` has an `active()`
state, and `TaskFactory` has a `done()` state.

## Seeders (`database/seeders/DatabaseSeeder.php`)

A single seeder creates:

- two users (demo + test)
- six clients for the demo user
- a handful of projects per client
- several tasks per project

All chained with `for(...)`, which is the modern Eloquent way to wire up
related records.

## Views (`resources/views`)

Blade templates organised by resource:

- `layouts/` — Breeze's app and navigation layouts (lightly extended to add
  Clients/Projects/Tasks links)
- `components/` — small reusable bits (`x-card`, `x-status-badge`,
  `x-flash`)
- `clients/`, `projects/`, `tasks/` — `index`, `create`, `edit`, `show`, and
  a shared `_form.blade.php` partial per resource
- `dashboard.blade.php` — KPI cards, upcoming tasks, recent projects
- `welcome.blade.php` — marketing-style homepage

The UI uses Tailwind utilities only, no custom CSS.

## Tests (`tests/Feature`)

Pest feature tests for each resource:

- `DashboardTest.php` — guest redirect + stats render
- `ClientTest.php` — list / search / create / validate / update / delete /
  ownership
- `ProjectTest.php` — list / filter / create / cross-user protection
- `TaskTest.php` — create / complete / reopen / ownership / status filter

Plus the Breeze-generated auth and profile tests.

Run them with `php artisan test` or `vendor/bin/pest`.
