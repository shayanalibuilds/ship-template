# AGENTS

This is a Laravel 13 + PHP 8.3 template. Vue 3 + Inertia, SSR on by default.

## Non-negotiables

- PR-first. Do not push `main`. Do not merge. Do not force-push.
- `declare(strict_types=1);` on every PHP file. Typed properties, parameters, returns.
- Tokens `SHIP_*` must not be deleted.
- Never delete a test to go green. Diagnose in `tmp/test-fix.md` (gitignored).
- Run `composer ready` before you claim done.

## Where to look

- `.ai/rules/index.md` — path-scoped rules (lands in PR 2).
- `docs/agents/` — playbooks (land in PR 6).
- Laravel Boost MCP/tools when connected.

The full agent map, playbooks, and FAQ land in `docs/agents/` in PR 6.
