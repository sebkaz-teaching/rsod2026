-- Start at A=5, B=0. Move two radios, inspect, then undo the entire transfer.
BEGIN;
UPDATE rsod5_demo.stock SET quantity = quantity - 2 WHERE warehouse_id = 'A';
UPDATE rsod5_demo.stock SET quantity = quantity + 2 WHERE warehouse_id = 'B';
SELECT * FROM rsod5_demo.stock ORDER BY warehouse_id;
ROLLBACK;
SELECT * FROM rsod5_demo.stock ORDER BY warehouse_id;
