-- B1: run after A1. SELECT waits; after A2 it returns the NEW value 4.
BEGIN;
SET LOCAL lock_timeout = '60s';
SELECT quantity FROM rsod5_demo.stock WHERE warehouse_id = 'A' FOR UPDATE;
-- B2: calculate 4 - 1 = 3 only after the locking read returns.
UPDATE rsod5_demo.stock SET quantity = 3 WHERE warehouse_id = 'A';
COMMIT;
SELECT quantity FROM rsod5_demo.stock WHERE warehouse_id = 'A';
