-- Reset first. A1: hold a row lock until COMMIT.
BEGIN;
UPDATE rsod5_demo.stock SET quantity = quantity - 1
WHERE warehouse_id = 'A' AND quantity >= 1
RETURNING quantity;
-- A2: run after B1 starts waiting. This unblocks B.
COMMIT;
