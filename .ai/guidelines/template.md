---
paths:
    - '**'
---

# Template ground rules

- PHP 8.3+, Laravel 13, Blade for e-mail/system views, Vue 3 + Inertia for pages. SSR on by default.
- `declare(strict_types=1);` on every PHP file. Typed properties, parameters, return types.
- PR-first: branch, test, implement, open PR, stop. Never push `main`, never merge, never force-push.
- Tokens `SHIP_GITHUB_OWNER`, `SHIP_PROJECT_SLUG`, `SHIP_VENDOR_NAMESPACE`, `SHIP_APP_NAME`, `SHIP_AGENT_LABEL` must not be deleted or renamed.
- Tests are required for every behavior change. No `skip()` to go green. No deleted tests.
- `.env` is gitignored. Ship `.env.example`. No secrets in git, ever.
- SQLite locally and in tests. Queue, cache, and session drivers come from `.env`.
- Run `composer ready` before claiming done.
