-- Reset first. A1: both transactions must read the same initial snapshot.
BEGIN ISOLATION LEVEL SERIALIZABLE;
SELECT quantity FROM rsod5_demo.stock WHERE warehouse_id = 'A';
-- A2: run after B1, before B2.
UPDATE rsod5_demo.stock SET quantity = quantity - 1 WHERE warehouse_id = 'A';
COMMIT;
