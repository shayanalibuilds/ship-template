---
paths:
  - "resources/views/components/**"
  - "resources/js/components/**"
---

# Components

- Reuse `<x-button>`, `<x-card>`, `<x-empty-state>`, `<x-input>`, `<x-alert>` before creating new ones.
- Anonymous or class components, pick one style per use case and stay consistent.
- Vue components must be SSR-safe: no `window`, `document`, or `localStorage` at import time or top-level setup. Browser APIs go inside `onMounted`.
- Accessible labels on inputs. Icon-only buttons need `aria-label`.
