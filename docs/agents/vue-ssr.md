# Vue and SSR

Inertia Vue pages are server-side rendered by default. Anything that only works
in a browser is a production bug that tests will miss.

## The rules

- SSR is the default. No CSR-only pages, no "splash screen while hydrating".
- No `window`, `document`, `localStorage`, or `sessionStorage` at module top
  level. Inside `onMounted()` is fine — that only runs in the browser.
- Data arrives via Inertia props from the controller. Pages never fetch their
  own data on load; that is what the SSR pass is for.
- Dates render from ISO strings sent by the backend; formatting happens in a
  composable that tolerates running on the server.
- `v-if` before `v-for` on empty states; the `EmptyState` component exists —
  use it instead of a hand-rolled div.

## Build and verify

- `npm run build` — client bundle.
- `npm run build:ssr` — server bundle. Both must exist or the SSR pass fails
  with `ViteManifestNotFoundException`.
- Smoke-check SSR locally: `curl http://localhost:8000` must return rendered
  HTML with real content, not an empty `<div id="app">`.

## Components

- `resources/js/Components/` holds shared pieces (`AppLayout`, `Alert`,
  `EmptyState`). Check for an existing component before writing a new one.
- Props are typed, events are named in the past tense (`saved`, `deleted`).
