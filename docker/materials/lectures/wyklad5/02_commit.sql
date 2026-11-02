-- Start at A=5, B=0. Commit the transfer: A=3, B=2, total=5.
BEGIN;
UPDATE rsod5_demo.stock SET quantity = quantity - 2 WHERE warehouse_id = 'A';
UPDATE rsod5_demo.stock SET quantity = quantity + 2 WHERE warehouse_id = 'B';
COMMIT;
SELECT * FROM rsod5_demo.stock ORDER BY warehouse_id;
