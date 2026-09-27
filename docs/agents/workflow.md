# Workflow

The daily loop for every agent working in this repo. Short version: branch, tests
first, implement, gates, PR, stop. Humans merge.

## The loop

1. **Pick one concern.** If the change cannot be described in one sentence, it is
   more than one PR.
2. **Branch** from the latest `main`: `git checkout -b <type>/<topic>`.
   Types: `feat/`, `fix/`, `test/`, `docs/`, `chore/`, `ci/`.
3. **Read before writing.** `.ai/rules/index.md` plus every rule file whose paths
   match your edit, and `.ai/guidelines/` for always-on rules.
4. **Write the failing test first.** Name it after the outcome:
   `it('lists only published releases')`.
5. **Implement** the smallest change that turns the test green.
6. **Run the gates**: `composer ready` (= `composer test`). All five must pass:
   peck, pest, pint + prettier, phpstan (level 8), rector.
7. **Commit in reviewable units.** One commit tells one story. Tests may precede
   the implementation they cover.
8. **Open the PR.** Follow `.github/pull_request_template.md` and the PR style
   section in `AGENTS.md`: one-sentence outcome, clickable commit links, real
   gate numbers. Never re-type commit messages. Never invent stats.
9. **Stop.** Do not merge. Do not push `main`. Do not force-push. The human
   reviews and merges.

## If a gate fails

- Never delete a test to go green. Never add `skip()`.
- Diagnose in `tmp/test-fix.md` (gitignored), fix the cause, re-run.
- Style failures: run the fixer (`composer fix`, `npm run lint:fix`) — never
  hand-tune formatting.
