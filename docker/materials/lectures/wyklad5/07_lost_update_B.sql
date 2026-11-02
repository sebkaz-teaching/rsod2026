-- B1: run after A1, before A2. Another application also reads 5.
BEGIN;
SELECT quantity FROM rsod5_demo.stock WHERE warehouse_id = 'A';
-- B2: run after A2 commits. Reuse the stale calculated value 4.
UPDATE rsod5_demo.stock SET quantity = 4 WHERE warehouse_id = 'A';
COMMIT;
SELECT quantity FROM rsod5_demo.stock WHERE warehouse_id = 'A';
