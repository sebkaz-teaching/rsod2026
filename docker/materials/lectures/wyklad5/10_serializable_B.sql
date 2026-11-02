-- B1: run after A1, before A2.
BEGIN ISOLATION LEVEL SERIALIZABLE;
SELECT quantity FROM rsod5_demo.stock WHERE warehouse_id = 'A';
-- B2: run after A2 commits. Expected serialization failure: SQLSTATE 40001.
UPDATE rsod5_demo.stock SET quantity = quantity - 1 WHERE warehouse_id = 'A';
-- B3: recover, then retry the ENTIRE transaction using a new snapshot.
ROLLBACK;
BEGIN ISOLATION LEVEL SERIALIZABLE;
SELECT quantity FROM rsod5_demo.stock WHERE warehouse_id = 'A';
UPDATE rsod5_demo.stock SET quantity = quantity - 1 WHERE warehouse_id = 'A';
COMMIT;
SELECT quantity FROM rsod5_demo.stock WHERE warehouse_id = 'A';
