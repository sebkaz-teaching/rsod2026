-- Start after 02: A=3, B=2. Correct a mistaken increase without losing the debit.
BEGIN;
UPDATE rsod5_demo.stock SET quantity = quantity - 1 WHERE warehouse_id = 'A';
SAVEPOINT after_debit;
UPDATE rsod5_demo.stock SET quantity = quantity + 99 WHERE warehouse_id = 'B';
SELECT * FROM rsod5_demo.stock ORDER BY warehouse_id;
ROLLBACK TO SAVEPOINT after_debit;
UPDATE rsod5_demo.stock SET quantity = quantity + 1 WHERE warehouse_id = 'B';
COMMIT;
SELECT * FROM rsod5_demo.stock ORDER BY warehouse_id;
