# Migrations

Schema changes are the hardest thing to undo. These rules keep them boring.

## Write-time rules

- Every schema change is a new migration. Never edit a migration that has been
  merged to `main` — it ran on someone's database already.
- One concern per migration: `create_releases_table` and
  `add_published_at_to_releases_table` are two migrations, not one.
- Typed columns, explicit indexes. The slug you query by gets an index the day
  it is born.
- Factories and enum states ship in the same PR as the migration they mirror.

## Destructive changes

- No destructive edits (drop column, drop table, tighten a type) in the same
  release that stops using the data. Two releases: deprecate and ignore first,
  drop later, after production has moved.
- Comment the constraint when history forces an odd shape:

```php
// Production has rows. Do not make user_id unique until duplicates are gone.
```

## Review and rollback

- `php artisan migrate` must work on a fresh database and on a copy of
  production. Test both locally before the PR.
- `migrate:rollback` must work for the migration you just wrote. If it cannot
  roll back, it needs a reason — and a comment saying why.
- Squashing to schema dumps is a maintenance task, done on `main` by humans,
  never inside a feature PR.
