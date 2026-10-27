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

## Local access

pgAdmin runs in desktop mode with automatic login as the existing local user.
The localhost-only port binding must remain in place. Each course has a distinct
session cookie name so opening both panels does not replace the other session.
Database authentication still uses the local pgpass file. Existing accounts,
server registrations, database volumes and edited SQL files are retained.
Open File starts in the container home directory; `materials` links to the
existing persistent SQL workspace. Close old browser tabs after this update.

Configuration reference: https://www.pgadmin.org/docs/pgadmin4/9.18/container_deployment.html
