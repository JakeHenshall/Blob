# ClientHub (junior)

A small, internal-style business app built with Laravel 13, Blade, and Tailwind.
You can add clients, create projects for them, and track tasks against those
projects.

This is the **junior-level** reference build: it focuses on clean Laravel
fundamentals (MVC, CRUD, relationships, validation, seeding, tests) without
pulling in advanced architecture patterns.

## Features

- Authentication (Laravel Breeze: login, register, forgot/reset password)
- Profile page (update name/email/password, delete account)
- Dashboard with live counts (clients, projects, active projects, tasks,
  completed tasks, overdue tasks) and upcoming-task / recent-project lists
- Clients CRUD
- Projects CRUD, scoped to the signed-in user's clients
- Tasks CRUD, scoped to the signed-in user's projects
- Search + status filters on the projects and tasks index pages
- Status fields for projects and tasks; due date + completion date on tasks
- Form Request validation for every write endpoint
- Eloquent relationships (User → Clients → Projects → Tasks)
- Factories and a `DatabaseSeeder` for realistic demo data
- Pest feature tests covering the critical flows
- Pagination on all list views

## Stack

- Laravel 13
- PHP 8.3+
- Blade + Tailwind CSS 3 (Breeze preset) + Alpine.js
- Vite for asset bundling
- SQLite (default, zero-config) — works with MySQL/Postgres by swapping `.env`
- Pest for tests

## Run locally

Prerequisites: PHP 8.3+, Composer, Node 18+.

```bash
cd laravel/junior

composer install
npm install

cp .env.example .env
php artisan key:generate

php artisan migrate --seed

npm run build      # or: npm run dev
php artisan serve
```

Then open http://127.0.0.1:8000.

## Demo credentials

After seeding, you can log in with:

- **email:** `demo@clienthub.test`
- **password:** `password`

A second test account is also seeded at `test@example.com` / `password`.

## Run tests

```bash
php artisan test
# or
vendor/bin/pest
```

## Screenshots

_Screenshots of the dashboard, clients list, and project detail go here._

## Documentation

See the `docs/` folder:

- `docs/overview.md` — what the app does and who it's for
- `docs/setup.md` — install, environment, migrate/seed, tests
- `docs/structure.md` — tour of routes, controllers, models, requests,
  factories, seeders, views, tests
- `docs/q-and-a.md` — common interview-style questions answered
- `docs/difference-from-mid.md` — what's intentionally left out vs. the mid
  version
