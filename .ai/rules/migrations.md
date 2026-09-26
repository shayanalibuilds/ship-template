---
paths:
    - 'database/migrations/**'
---

# Migrations

- Never edit or delete a migration that landed on `main`. New file, always.
- Index foreign keys and every column you filter or sort on, in the same PR as the filter.
- `down()` correct for local rollback of this unreleased migration.
- Nullable or defaulted columns so existing rows survive.
- No destructive changes (drop column/table) without the human confirming there is no production data.
- `migrate:fresh` only on disposable local/test databases.
