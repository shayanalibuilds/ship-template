---
paths:
  - "app/Http/**"
---

# Controllers and Form Requests

- One Form Request per write endpoint.
- `authorize()` real once users exist. Until then `return true` with a TODO comment naming the auth PR.
- Rules: required -> type -> max -> format. Validation errors must be field-targeted.
- Controllers are thin: validate, delegate to an action, redirect/render. No query chains longer than one call.
- Use `Inertia::render('Page/Name', [...])` for pages. Pass primitives and models the page actually uses.
