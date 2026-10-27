#!/bin/sh
set -eu

# Manifest: database|initialization marker relation|SQL file, or - for empty databases.
# A failed initialization can be retried; existing demonstration data is never reset.
while IFS='|' read -r database marker sql_file; do
    case "$database" in ''|\#*) continue ;; *[!a-z0-9_]*) echo 'Invalid database name' >&2; exit 1 ;; esac
    if [ "$(psql -X -d postgres -Atqc "SELECT 1 FROM pg_database WHERE datname = '$database'")" != "1" ]; then
        createdb "$database"
    fi
    if [ "$marker" != '-' ]; then
        case "$marker" in *[!a-z0-9_.]*) echo 'Invalid relation name' >&2; exit 1 ;; esac
        if [ "$(psql -X -d "$database" -Atqc "SELECT to_regclass('$marker') IS NOT NULL")" = 't' ]; then
            echo "$database: existing data preserved"
        else
            psql -X -v ON_ERROR_STOP=1 -d "$database" -f "/materials/$sql_file"
        fi
    fi
done < /setup/databases.txt
