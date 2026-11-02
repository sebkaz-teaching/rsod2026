-- Run the whole block after A1 and before A2.
BEGIN;
UPDATE rsod5_demo.stock SET quantity = quantity - 1 WHERE warehouse_id = 'A';
COMMIT;
