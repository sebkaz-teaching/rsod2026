-- Run once in a separate database; do not overwrite existing demonstrations.
BEGIN;
CREATE SCHEMA rsod5_demo;
CREATE TABLE rsod5_demo.stock (
    warehouse_id text PRIMARY KEY,
    quantity integer NOT NULL CHECK (quantity >= 0)
);
INSERT INTO rsod5_demo.stock VALUES ('A', 5), ('B', 0);
COMMIT;
SELECT * FROM rsod5_demo.stock ORDER BY warehouse_id;
