---
paths:
    - 'app/Console/**'
    - 'app/Console/Commands/**'
    - 'routes/console.php'
---

# Console commands

- `$signature` + `$description` always.
- Exit codes matter: `self::SUCCESS` / `self::FAILURE`.
- Dry-run option (`--dry-run`) for anything destructive or repo-wide.
- Confirm before writing when running interactively; require explicit options in tests/CI.
