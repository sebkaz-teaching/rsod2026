-- Reset first. A1: lock the row before making an application-side decision.
BEGIN;
SELECT quantity FROM rsod5_demo.stock WHERE warehouse_id = 'A' FOR UPDATE;
-- A2: run while B1 waits. Use the value read while holding the lock.
UPDATE rsod5_demo.stock SET quantity = 4 WHERE warehouse_id = 'A';
COMMIT;
