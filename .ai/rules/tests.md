---
paths:
  - "tests/**"
---

# Tests

- Pest, Laravel helpers, `RefreshDatabase` where the DB is touched.
- Assert HTTP status, database state, and the Inertia component (`assertInertia`).
- Name tests after the outcome: `it('lists only published releases')`.
- No external network in tests. No wall-clock flakiness.
- Do not delete a failing test. Diagnose in `tmp/test-fix.md`.
- Full CRUD tests when the work is CRUD — before the UI.
