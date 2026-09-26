# FAQ

Questions agents actually ask, answered once.

**Can I merge my own PR?**
No. Humans merge. Open the PR and stop — that is the whole job.

**Why is the namespace still `App\` after `ship:rename`?**
Deliberate. `App\` is a Laravel convention; renaming namespaces buys nothing
and breaks config caching, tests, and IDE links. The rename changes identity
(composer name, APP_NAME, tokens), not the framework contract.

**`composer ready` vs `composer test`?**
They run the same gates. `ready` is the word you say when you claim done.

**A gate fails but my change is fine. Can I ignore it?**
No. peck flags a word → add it to `peck.json` in the same PR. phpstan flags a
line → fix the type, or write the DocBlock that makes it provably safe. Rector
wants a rewrite → let it (`composer refactor`) and review the diff.

**Where do tokens come from?**
`SHIP_GITHUB_OWNER`, `SHIP_PROJECT_SLUG`, `SHIP_VENDOR_NAMESPACE`, `SHIP_APP_NAME`,
`SHIP_AGENT_LABEL` — declared in the template, filled by `ship:rename`,
contract listed in `config/ship.php`. Never delete or rename them.

**SQLite in production?**
Fine to start (`docs/agents/scale.md` ladder). Postgres is a config change
when you need it.

**Where do I put a new rule for agents?**
Path-scoped rules go in `.ai/rules/<topic>.md` + an index entry in
`.ai/rules/index.md`. Always-on guidance goes in `.ai/guidelines/<topic>.md`.
Docs for workflows go in `docs/agents/`. One place per idea — no duplication.

**The tests need a real API.**
They do not and must not. Fake the transport, test the behavior. If the
behavior genuinely needs the network, that is an integration concern for a
human, not this suite.

**How do I run only one gate?**
`composer test:typos` / `test:unit` / `test:lint` / `test:types` /
`test:refactor`. Fix fast, then run the full `composer test` before the PR.
