# rsod2026 teaching environment

Run `docker compose up -d` from this directory. Open http://localhost:5051.
The Polish walkthrough is in `START-TUTAJ.txt`.

## Layout

- `docker-compose.yml`: PostgreSQL 16, pgAdmin 9.18 and an idempotent preparation job.
- `databases.txt`: `database|marker_relation|initialization_sql`; `-|-` creates an empty database.
- `materials/lectures/`: numbered SQL demonstrations; no private quiz keys.
- `servers.json` and `pgpass`: preconfigured local database connection.

## Extending the course

Add SQL under `materials/labs/labNN` or `materials/lectures/wykladN` and add its database to `databases.txt`.
A seeded database needs an atomic setup script and a marker relation created in the same transaction.
Existing marker relations cause setup to be skipped; startup never resets a demonstration.
Run `docker compose up -d` after changes. Keep lecture copies synchronized with public `assets/sql`.
The pgAdmin file workspace preserves edited files; use a new filename for revised teaching scripts.

Named volumes retain database and pgAdmin state across stops. `docker compose down -v` deletes them.
This directory is excluded from Quarto resources and rendering. It has no host mounts outside itself.
PostgreSQL is reachable only within the Compose network; only the local pgAdmin port is published.
