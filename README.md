# Ship Template

A Laravel ship template for AI-assisted product delivery.

Fork it, run `php artisan ship:rename`, and ship your product from a clean, tested base.

> Status: under construction — PR 1 of 7. See `.ai/tasks/plan.md` for the plan.

## Stack

- Laravel 13, PHP ^8.3
- Vue 3 + Inertia, SSR on by default (dev and production)
- Tailwind CSS
- Fission-style tooling: Pint, Rector, Larastan/PHPStan, Peck, Pest, Prettier

## Run locally (short version, full docs land in PR 7)

```bash
composer install
npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
npm run build:ssr
php artisan serve
```

## Quality gates

```bash
composer ready
```
