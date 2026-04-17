# Setup

## Requirements

- PHP 8.3 or newer
- Composer 2
- Node 18+ (for Vite / Tailwind)
- SQLite (built into PHP) or MySQL/Postgres if you prefer

## 1. Install dependencies

```bash
cd laravel/junior
composer install
npm install
```

## 2. Environment

```bash
cp .env.example .env
php artisan key:generate
```

The default `.env` uses SQLite. The scaffold creates
`database/database.sqlite` for you. No other DB config needed.

If you want MySQL instead, change the DB block in `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=clienthub
DB_USERNAME=root
DB_PASSWORD=
```

## 3. Migrate and seed

```bash
php artisan migrate --seed
```

This creates the schema and loads demo data:

- 2 users (`demo@clienthub.test` / `password`, `test@example.com` / `password`)
- 6 clients for the demo user
- 1–3 projects per client
- 3–8 tasks per project

To reset the database at any time:

```bash
php artisan migrate:fresh --seed
```

## 4. Build assets

For local development with hot reloading:

```bash
npm run dev
```

Or build once for production-style output:

```bash
npm run build
```

## 5. Run the app

```bash
php artisan serve
```

Open http://127.0.0.1:8000 and log in with the demo credentials above.

## 6. Run tests

```bash
php artisan test
```

Or with Pest directly:

```bash
vendor/bin/pest
```

All tests use `RefreshDatabase` and an in-memory SQLite connection, so they
don't touch your development database.
