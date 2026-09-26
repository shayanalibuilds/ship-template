# Plan

End goal: A stranger can clone the repo, `composer install && npm install`, boot with SQLite, see a home page and a Releases flow rendered through Inertia Vue SSR, run `composer test` green, and run `php artisan ship:rename` to take ownership of the template.

Tasks:

1. [current] chore/scaffold-laravel — Laravel 13 + Inertia Vue SSR shell + Fission tooling + smoke test
2. [queued] chore/boost-and-ai-surface — laravel/boost + .ai/guidelines + path-scoped .ai/rules + index
3. [queued] feat/app-shell — Release domain (draft/published), list/show/create pages, tests
4. [queued] feat/ship-rename-command — ship:rename with --dry-run/--force, config/ship.php, tests
5. [queued] test/quality-gates — GitHub Actions running composer test + SSR build on PHP 8.3
6. [queued] docs/agent-playbooks — workflow.md, ship-a-feature, fix-a-bug, ship-v1, production, scale, migrations, clean-code, vue-ssr, do-not, faq
7. [queued] docs/readme-and-license — complete README (SSR default, composer test/fix/dev), MIT

Questions and answers:

- File 1 (Blade) vs file 2 (Vue 3 + Inertia SSR)? File 2 governs. SSR is the default; no CSR-only pages.
- GitHub token/origin missing in the build sandbox? Local-first: branches and PR reports in chat; push + real PRs when the token is provided.
- Pest 5 requires PHP 8.4? Pinned to the Pest 4 line (PHP 8.3 compatible), per the no-8.4 rule.
- composer dev? `php artisan dev` (Laravel 13 TUI) with SSR processes registered in AppServiceProvider.

Out of scope:

- Auth, billing, multi-tenant, Livewire, separate SPA, Kubernetes, new UI kits.
