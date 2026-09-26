---
paths:
    - 'config/**'
---

# Config

- Config files return plain arrays. No closures that touch services at load time.
- Values come from `env()` with a sensible default for SQLite/file drivers.
- Cache, queue, and session drivers must remain env-switchable so Redis can attach later without code changes.
- Do not delete `SHIP_*` tokens if they appear in config (`config/ship.php` from PR 4).
- `config:cache` in production only after `.env` is final.
