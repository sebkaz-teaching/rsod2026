-- Reset first. A1: the first SELECT establishes the transaction snapshot.
BEGIN ISOLATION LEVEL REPEATABLE READ;
SELECT pg_backend_pid() AS session_a;
SELECT quantity FROM rsod5_demo.stock WHERE warehouse_id = 'A';
-- A2: run after B commits; still 5 in the original snapshot.
SELECT quantity FROM rsod5_demo.stock WHERE warehouse_id = 'A';
COMMIT;
-- A3: outside the old transaction; now 4.
SELECT quantity FROM rsod5_demo.stock WHERE warehouse_id = 'A';
