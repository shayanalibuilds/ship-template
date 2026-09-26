---
paths:
    - 'resources/views/**'
---

# Blade views

- `<x-layouts.app>` wraps every full page (once Inertia-adjacent layouts exist).
- Escape output. `{{ }}` only. Never `{!! !!}` with user content.
- Labels on every input. Empty, loading, and error states exist for every list.
- No Eloquent in Blade. No queries in views.
- Tailwind utility classes only; no inline style novels.
