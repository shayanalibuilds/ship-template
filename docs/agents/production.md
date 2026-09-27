# Production

Checklist for going live. Order matters; do not skip lines.

## Environment

- `.env` exists on the server only — never in git. Copy from `.env.example`,
  fill real values, then `php artisan key:generate --force`.
- `APP_ENV=production`, `APP_DEBUG=false`.
- `config:cache` and `route:cache` only after `.env` is final.

## Database

- SQLite is for local and tests. In production pick Postgres (or MySQL) and
  switch `DB_CONNECTION` — no code changes needed.
- `php artisan migrate --force` on every deploy, before restarting workers.

## Frontend and SSR

- `npm run build` and `npm run build:ssr` on every deploy — the server-rendered
  pages need the fresh manifest and SSR bundle.
- Run the SSR process and keep it alive (`php artisan inertia:start-ssr` behind
  a supervisor or a container restart policy).

## Runtime

- Queues: `QUEUE_CONNECTION=database` works out of the box; run
  `php artisan queue:work` under a supervisor.
- Scheduler: one crontab entry, `php artisan schedule:run` every minute.
- Sessions, cache, queue all read their drivers from `.env` — Redis attaches
  later without code changes (`docs/agents/scale.md`).

## Health and rollback

- `/up` must answer 200 behind the load balancer before traffic moves.
- Rollback = redeploy the previous release + `php artisan migrate:rollback`
  only if the release shipped a destructive migration (it should not — see
  `docs/agents/migrations.md`).
