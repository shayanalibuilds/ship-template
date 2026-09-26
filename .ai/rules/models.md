---
paths:
    - 'app/Models/**'
---

# Models

- `$fillable` or `$guarded` explicit. No bare models.
- `casts()` method for dates, enums, JSON.
- Factories for every model. States for meaningful variants (e.g. `published()`).
- Name relationship methods after their inverse where sensible.
- No business writes hidden in observers unless documented in the model docblock.
- Query scopes for repeated filters (`scopePublished()`).
