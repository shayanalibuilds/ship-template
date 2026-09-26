# Scale

Scale when the product demands it, not before. Every swap below is a config
change plus a server — no rewrite.

## The ladder, in order

1. **SQLite → Postgres.** When writes contend or you need concurrent users.
   Change `DB_CONNECTION`, migrate, done. The schema is portable.
2. **file → Redis for cache and sessions.** When one server is no longer
   enough. Change `CACHE_STORE` and `SESSION_DRIVER`, point them at the same
   Redis.
3. **sync/database queue → Redis queue.** When jobs pile up. Change
   `QUEUE_CONNECTION`, run more `queue:work` workers.
4. **One server → load balancer.** Only after steps 1-3. Sessions and cache in
   Redis make app servers interchangeable.

## What does NOT scale by config alone

- N+1 queries: watch the tests and the query log; `Model::with()` early.
- Slow pages: profile before caching. Cache after the query is honest.
- SSR process: it is a single Node process — scale it like any Node service.

## Anti-goals

- Do not add Redis "because production should have it". SQLite serves real
  products for a long time.
- Do not shard, cluster, or split services before the ladder says so.
- Do not scale a feature nobody uses.
