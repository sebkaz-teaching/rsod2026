-- B1: run after A1. Leave this transaction OPEN, then return to A2.
BEGIN;
SELECT pg_backend_pid() AS session_b;
UPDATE rsod5_demo.stock SET quantity = quantity - 1 WHERE warehouse_id = 'A';
-- B2: run after A2. Then return to A3.
COMMIT;
