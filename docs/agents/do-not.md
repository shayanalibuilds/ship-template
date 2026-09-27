# Do not

The hard bans. "But it would be faster" is not an argument. Each line below
has bitten a real project.

## Git and GitHub

- Do not push, merge, or force-push `main`. Humans merge.
- Do not rebase a branch the human has already started reviewing — commit
  links must stay valid.
- Do not squash "for cleanliness" while the PR is open.
- Do not commit `.env`, tokens, keys, dumps, or anything from `tmp/`.

## Code

- Do not write comments on obvious code. Do not leave TODO/FIXME.
- Do not write code a beginner cannot follow — rewrite it instead.
- Do not name a service `Helper`, `Manager`, or `Utils`.
- Do not use magic strings for states; use the enum.
- Do not put business rules in controllers or Blade templates. Actions own
  rules, form requests own validation.
- Do not use `{!! !!}` in Blade for anything a user can touch.

## Tests and gates

- Do not delete a test to go green. Do not `skip()` a test. Do not mark a test
  `@group flaky` and move on — fix the flake.
- Do not claim "done" without `composer test` exit 0.
- Do not invent gate numbers in a PR body. Run the suite, copy the real stats.
- Do not do network calls in tests. Do not sleep() for timing.

## Process

- Do not start without reading the rules that own your paths
  (`.ai/rules/index.md`).
- Do not mix two concerns in one PR.
- Do not edit a merged migration. Do not rename a `SHIP_*` token.
- Do not decide a product question alone. Ask the human.
