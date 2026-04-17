# Setup

## Requirements

- PHP 8.3+
- Composer 2
- Node 20+
- SQLite (default) or any database supported by Laravel
- A writable filesystem disk for file uploads (local by default; S3-compatible for production)

## First run

```bash
cd laravel/senior
cp .env.example .env

composer install
npm install

php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed

npm run build
```

## Development

All four long-running processes in one terminal:

```bash
composer dev
```

That runs `php artisan serve`, `php artisan queue:listen`, `php artisan pail` (log tail) and `npm run dev` concurrently, which is the fastest way to iterate.

Or run them individually:

```bash
php artisan serve
php artisan queue:listen --tries=1
php artisan pail
npm run dev
```

## Environment

Key `.env` values this app cares about:

```
APP_NAME=ClientHub
APP_ENV=local

APP_LOCALE=en_GB
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_GB

DB_CONNECTION=sqlite

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

FILESYSTEM_DISK=local

MAIL_MAILER=log

SANCTUM_STATEFUL_DOMAINS=localhost,localhost:5173,127.0.0.1
```

`MAIL_MAILER=log` keeps emails out of your inbox during development. They appear in `storage/logs/laravel.log`.

## Switching database

If you prefer MySQL or Postgres, comment out the sqlite lines in `.env` and set your credentials. Then:

```bash
php artisan migrate:fresh --seed
```

## Switching filesystem disk

Out of the box `FILESYSTEM_DISK=local`, which stores files under `storage/app/private`. To use S3:

```
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=eu-west-2
AWS_BUCKET=clienthub-files
```

`UploadProjectFileAction` reads `config('filesystems.default')` at execution time, so the disk swap needs no code change. `ProjectFile::temporaryUrl()` gracefully degrades to `null` on disks that do not support signed URLs (e.g. `local`).

## Running tests

```bash
php artisan test
```

PHPUnit 12 is the test runner. `phpunit.xml` forces an in-memory SQLite database and the `sync` queue connection, so the suite is deterministic and requires no external services. See [testing.md](testing.md).

## Code style

The project ships with Laravel Pint. Before committing:

```bash
./vendor/bin/pint
```
