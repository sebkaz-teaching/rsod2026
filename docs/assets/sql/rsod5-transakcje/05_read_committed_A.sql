-- Reset first with 99_reset.sql, then use TWO independent connections.
-- A1: run this block; A reads 5.
BEGIN ISOLATION LEVEL READ COMMITTED;
SELECT pg_backend_pid() AS session_a;
SELECT quantity FROM rsod5_demo.stock WHERE warehouse_id = 'A';
-- A2: run after B1 (B has updated but NOT committed); A still reads 5.
SELECT quantity FROM rsod5_demo.stock WHERE warehouse_id = 'A';
-- A3: run after B2 (COMMIT); A now reads 4 in the SAME transaction.
SELECT quantity FROM rsod5_demo.stock WHERE warehouse_id = 'A';
COMMIT;
