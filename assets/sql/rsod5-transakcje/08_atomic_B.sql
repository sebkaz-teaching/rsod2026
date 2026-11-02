-- B1: run after A1. The UPDATE waits for A; the timeout limits accidental hangs.
BEGIN;
SET LOCAL lock_timeout = '60s';
UPDATE rsod5_demo.stock SET quantity = quantity - 1
WHERE warehouse_id = 'A' AND quantity >= 1
RETURNING quantity;
-- B2: run after A2; UPDATE has returned 3. Commit B.
COMMIT;
SELECT * FROM rsod5_demo.stock ORDER BY warehouse_id;
