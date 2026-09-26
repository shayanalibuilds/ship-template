## What

## Why

## How to test

- [ ] `composer install`
- [ ] `cp .env.example .env && php artisan key:generate`
- [ ] `php artisan migrate --seed`
- [ ] `composer ready`

## Agent checklist

- [ ] Read `.ai/rules/index.md` for touched paths
- [ ] Feature tests added or updated
- [ ] No secrets committed
- [ ] Rename tokens still intact unless this PR is a real rename
