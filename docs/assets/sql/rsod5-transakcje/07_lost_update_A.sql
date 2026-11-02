-- Reset first. A1: an application reads 5 and calculates 5 - 1 = 4.
BEGIN;
SELECT quantity FROM rsod5_demo.stock WHERE warehouse_id = 'A';
-- A2: run after B1 reads 5. Commit before B2.
UPDATE rsod5_demo.stock SET quantity = 4 WHERE warehouse_id = 'A';
COMMIT;
