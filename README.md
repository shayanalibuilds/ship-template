# SHIP_APP_NAME

> **New here?** This line is a token, not a placeholder mistake. Run
> `php artisan ship:rename "Your Product" --owner=your-github-org` and every
> `SHIP_*` token in this repo — the title above included — is filled in. See
> `docs/agents/ship-v1.md`.

A Laravel template for AI-assisted product delivery: fork it, rename it, and
ship from a clean, tested base. The included Releases domain (draft/published,
list/show/create) doubles as a worked example of the house patterns.

![quality gates](https://github.com/SHIP_GITHUB_OWNER/SHIP_PROJECT_SLUG/actions/workflows/quality-gates.yml/badge.svg)

## Stack

- Laravel 13, PHP ^8.3, `strict_types` everywhere
- Vue 3 + Inertia — **SSR on by default**, in dev and production
- Tailwind CSS
- Quality tooling: Pest · Pint · Prettier · PHPStan (level 8) · Rector · Peck

## Quick start

```bash
composer setup    # install, .env, app key, migrate, npm build + SSR build
composer dev      # Laravel TUI with SSR processes registered
```

Then open `http://localhost:8000`. SQLite works out of the box — no services
to start.

## Take ownership

```bash
php artisan ship:rename "Your Product" --owner=your-github-org
```

What it does, in order: previews every change, asks for confirmation (or
`--force`), fills the tokens (`SHIP_APP_NAME`, `SHIP_PROJECT_SLUG`,
`SHIP_GITHUB_OWNER`, `SHIP_VENDOR_NAMESPACE`, `SHIP_AGENT_LABEL`), rewrites
`composer.json` and `APP_NAME`, and never touches the `App\` namespace — that
stays a Laravel convention. `--dry-run` writes nothing. The contract lives in
`config/ship.php`.

## Quality gates

```bash
composer test    # runs every gate below; exit 0 means done
```

| Gate              | Checks                              |
| ----------------- | ----------------------------------- |
| peck              | no misspellings in code or docs     |
| pest              | the test suite, in parallel         |
| pint + prettier   | code and frontend style             |
| phpstan (level 8) | static types                        |
| rector            | refactorings stay applied, no drift |

`composer fix` applies the safe fixes (types, rector, prettier, pint) — then
re-run `composer test`.

## For agents

- `AGENTS.md` is the entry point. `.ai/rules/index.md` maps paths to rules;
  read the rules that own your edit before writing.
- Playbooks for the daily loop, shipping a feature, fixing a bug, production,
  and the hard bans: `docs/agents/`.
- PR-first: branch, tests first, gates green, open the PR — then stop.
  Humans merge. PRs carry clickable commit links and real gate stats.

## License

MIT. See [LICENSE](LICENSE) — after a rename, the copyright line is yours.
