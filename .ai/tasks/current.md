# Current

Outcome: When a developer clones the repo, the Laravel 13 + Inertia Vue SSR shell boots with Fission tooling configured and the /up smoke test is green.

Branch: chore/scaffold-laravel

Tests I will add:

- Pest smoke test: GET /up returns 200 (replaces skeleton ExampleTest).

Must not break:

- The scaffold commit on main.
- `php artisan migrate` on SQLite.
- `/up` health endpoint.
