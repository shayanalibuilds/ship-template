## Outcome

<!-- One sentence: what becomes true after this PR merges that was not true before. -->

## Commits

<!-- One row per commit. Never re-type or paraphrase the change — the link IS the review. -->

| Commit                                                                       | Title                          |
| ---------------------------------------------------------------------------- | ------------------------------ |
| [`abc1234`](https://github.com/shayanalibuilds/ship-template/commit/abc1234) | feat: short imperative subject |

## Quality gates

<!-- Fill from your last real `composer test` run. Every row must pass before requesting review. -->

| Gate              | Result                       |
| ----------------- | ---------------------------- |
| peck (typos)      | ✅ 0 misspellings            |
| pest              | ✅ 11 passed · 76 assertions |
| pint + prettier   | ✅ clean                     |
| phpstan (level 8) | ✅ 0 errors                  |
| rector            | ✅ clean                     |

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
- [ ] Every commit is linked, not re-described
- [ ] Quality-gates table filled from a real run, not invented
