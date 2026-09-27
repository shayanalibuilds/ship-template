# Ship a feature

End-to-end walkthrough of one real feature: the Releases flow that ships with
this template (list, show, create, with draft/published states). Use it as the
reference when you ship the next one.

## 1. Scope it in one sentence

"A visitor can list published releases, open one by slug, and create a draft."
If your sentence has an "and then", split the PR.

## 2. Read the rules that own your paths

- `.ai/rules/index.md` maps paths to rule files — read every match first.
- This feature touched models, http, actions, views, tests, migrations, config:
  seven rule files, two minutes of reading, hours of rework saved.

## 3. Model and migration first

- The `Release` model with a `ReleaseStatus` enum (`draft`, `published`) came
  before any HTTP code. States are enums, never magic strings.
- Migration: create the table with typed columns and indexes; the factory gets
  one state per enum case.

## 4. Actions own the business rules

- `CreateRelease` action: generates the unique slug, sets the initial status.
- Derived fields (slugs, status transitions) are computed inside actions —
  never in controllers, never in views.
- Controllers stay thin: validate, call the action, return a response.

## 5. Validation in Form Requests

- `StoreReleaseRequest` owns the rules and the failure behavior. Controllers
  never call `Validator::make()` directly.

## 6. Pages through Inertia

- `resources/js/Pages/Releases/{Index,Show,Create}.vue` render server-side.
- Everything must survive SSR: no `window`, no `document`, see
  `docs/agents/vue-ssr.md`.

## 7. Tests before the UI, not after

- The Pest tests covered pagination, ordering, slug uniqueness, draft
  visibility, and validation failures — all before the Vue pages existed.

## 8. Gates and PR

- `composer test` until exit 0, then the PR with commit links and real stats.
- Full story of the loop: `docs/agents/workflow.md`.
