---
paths:
  - "app/**"
  - "database/**"
  - "tests/**"
---

# PHP style

- One class, one job. Small methods. Constructor promotion.
- `final` classes by default.
- Enums instead of magic strings for states.
- No God services named `Helper`, `Manager`, or `Utils`.
- Do not use untyped arrays where a DTO, value object, or enum is clearer.
- Comments explain constraints, not what the line does:

```php
// Production has rows. Do not make user_id unique until duplicates are gone.
```

- Blade escaping: `{{ }}` everywhere. Never `{!! !!}` for user content.
- Name classes after the domain: `Release`, `PublishRelease`. Methods are verbs: `publish()`, `scopePublished()`.
