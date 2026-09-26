---
paths:
    - 'app/Actions/**'
    - 'app/Services/**'
---

# Actions

- `App\Actions\...` invokable classes for writes.
- `__invoke(array $data): Model` — return the model or a result object, not a bool.
- Side effects (mail, events, jobs) are dispatched from the action, never the controller.
- Actions are the only place business rules live. Controllers never write.
- Slugs, status transitions, and derived fields are computed inside actions.
