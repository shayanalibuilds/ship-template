---
paths:
  - "app/Http/**"
  - "routes/**"
---

# HTTP layer

- Thin controllers: translate HTTP to actions/queries and return a response.
- One Form Request per write endpoint. `authorize()` is real once users exist; `return true` only until then, with a TODO naming the follow-up PR.
- Named routes only: `releases.index`, `releases.show`, `releases.store`.
- PRG pattern for HTML/Inertia form flow: POST -> redirect -> GET.
- No business logic in routes. No queries in Blade or Vue templates — pass page props.
- API endpoints return API Resources if/when JSON endpoints are added.
- Policies own authorization when a user model with roles exists.
