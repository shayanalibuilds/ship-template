---
paths:
    - '**'
---

# Language and comments

English only. Everywhere. Code, comments, commit messages, PR titles and descriptions,
docs, chat with the owner. This is a hard rule, not a preference.

## Comments are a last resort

- No comments on obvious code. `// get the user` above `getUser()` is noise — delete it.
- A comment earns its line only when it explains a WHY the code cannot say: a
  constraint, a trap, a business rule, a decision and its reason.
- Comments explain constraints, not what the line does:

```php
// Production has rows. Do not make user_id unique until duplicates are gone.
```

- Never leave TODO or FIXME comments. Fix it now, or open an issue.
- If a block of code needs a paragraph to explain it, rewrite the code instead.
  Simple beats clever, every time.

## Write for the next reader

- Assume a beginner or intermediate developer who has never seen this pattern.
- Small methods with honest names read like prose: `publish()`, `hasExpired()`,
  `totalRevenue()`. Name things so well that comments become unnecessary.
- One class, one job. Nesting deeper than two levels is a signal to extract.

## DocBlocks carry the types

- Every class and every method gets a DocBlock. Keep it typed, not chatty.
- `@param`, `@return`, `@throws` with real types; one short sentence of intent at most.
- Arrays never stay untyped: `@param list<string>` or a value object, never `@param array`.

```php
/**
 * Publish a draft release and stamp its publication time.
 *
 * @throws ReleaseAlreadyPublished when the release is not in draft state
 */
public function publish(Release $release): Release
```

The type safety lives in the DocBlock and the signature. The story lives in the
method name. Anything else is a comment that will rot.
