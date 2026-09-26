# Current

Outcome: When a visitor opens the app, they see a home page and a Releases flow (list/show/create) rendered through Inertia Vue SSR, backed by a Release model with draft/published states, and the feature tests cover the flow.

Branch: feat/app-shell

Tests I will add:

- it lists only published releases
- it paginates the releases index
- it orders releases newest first
- it shows a published release by slug
- it does not show draft releases to guests
- it renders the create form
- it validates the create form
- it creates a draft release from the form
- it generates a unique slug when the title exists

Must not break:

- /up health endpoint, PR 1 smoke test.
- SSR build (`npm run build:ssr`).
