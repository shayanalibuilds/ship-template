# Ship v1

From fork to a real product, in order. Each step depends on the ones above it.

## 1. Take ownership

```bash
php artisan ship:rename "Your Product" --owner=your-github-org
```

Fills every `SHIP_*` token in the repo (`config/ship.php` lists where), rewrites
`composer.json` and `APP_NAME`. Re-run it any time — tokens without a value stay
as placeholders, so a partial rename is safe.

## 2. Make it yours in the first commit

- First feature on a branch, first PR, first green `composer test`.
- Keep the Release domain as the worked example — replace it only when your
  real domain exists.

## 3. Build the real domain

- Model + migration first, actions for rules, form requests for validation,
  Inertia pages for UI, tests before UI. The full recipe:
  `docs/agents/ship-a-feature.md`.

## 4. Seed something honest

- `database/seeders/` ships demo data. Replace it with data your product's
  first user will actually see.

## 5. Deploy the first version

- Production checklist: `docs/agents/production.md`.
- Scale is a later problem: `docs/agents/scale.md`.

## Definition of v1

- A stranger can sign up, use the core loop of the product, and come back.
- `composer test` is green on `main`.
- Every feature is merged through a reviewed PR — the git history tells the story.
