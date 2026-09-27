# Clean code

The owner's doctrine, in one page. Agents: this is what "done" looks like.

## Simple beats clever

- Write for a beginner or intermediate developer who has never seen the
  pattern. If it takes a paragraph to explain, rewrite it until it does not.
- Small methods with honest names: `publish()`, `hasExpired()`, `totalRevenue()`.
- Nesting deeper than two levels is a signal to extract a method.
- One class, one job. `final` classes, constructor promotion, enums for states.

## Comments are a last resort

- No comments on obvious code — that is noise, and noise hides real signals.
- A comment earns its line only for a WHY the code cannot say: a constraint, a
  trap, a business decision. Everything else: delete it.
- Never TODO/FIXME. Fix now or open an issue.
- Full policy: `.ai/guidelines/language.md`.

## DocBlocks carry the types

- Every class and method: a DocBlock with `@param`, `@return`, `@throws` —
  real types, one short sentence of intent. Arrays are `list<string>` or a
  value object, never a bare `array`.

```php
/**
 * Publish a draft release and stamp its publication time.
 *
 * @throws ReleaseAlreadyPublished when the release is not in draft state
 */
public function publish(Release $release): Release
```

## Discipline

- English only, everywhere, always.
- Tests first, gates green, PR with commit links. Never push `main`.
- A reviewer should understand a commit in under a minute — because the code
  is simple, not because the commit message is long.
