# Setup

## Requirements

- PHP 8.3+
- Composer 2
- Node 20+
- SQLite (default) or any other database supported by Laravel

## First run

```bash
cd laravel/mid
cp .env.example .env

composer install
npm install

php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed

npm run build
```

## Development

```bash
php artisan serve
php artisan queue:listen --tries=1
npm run dev
```

Or use the composer script defined in the junior repo (port that over if you want all four in one terminal):

```bash
composer dev
```

## Environment

Key `.env` values this app cares about:

```
APP_NAME=ClientHub

DB_CONNECTION=sqlite

QUEUE_CONNECTION=database
SESSION_DRIVER=database

MAIL_MAILER=log
```

`MAIL_MAILER=log` keeps emails out of your inbox during development. They appear in `storage/logs/laravel.log`.

## Switching database

If you prefer MySQL or Postgres, comment out the sqlite lines in `.env` and set your credentials. Then:

```bash
php artisan migrate:fresh --seed
```

## Running tests

```bash
php artisan test
```

Pest is the test runner. See [testing.md](testing.md).
