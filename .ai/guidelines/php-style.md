---
paths:
    - 'app/**'
    - 'database/**'
    - 'tests/**'
---

# PHP style

- One class, one job. Small methods. Constructor promotion.
- `final` classes by default.
- Enums instead of magic strings for states.
- No God services named `Helper`, `Manager`, or `Utils`.
- Do not use untyped arrays where a DTO, value object, or enum is clearer.
- Comments explain constraints, not what the line does — and only when the
  constraint is non-obvious. No comment is better than an obvious one:

```php
// Production has rows. Do not make user_id unique until duplicates are gone.
```

- Every class and method carries a DocBlock with real types (`@param`, `@return`,
  `@throws`). Types first, one sentence of intent, no chatter.
- Code must be simple enough for a beginner to follow. If it needs a long comment
  to be understood, rewrite the code. See `.ai/guidelines/language.md`.
- Blade escaping: `{{ }}` everywhere. Never `{!! !!}` for user content.
- Name classes after the domain: `Release`, `PublishRelease`. Methods are verbs: `publish()`, `scopePublished()`.
