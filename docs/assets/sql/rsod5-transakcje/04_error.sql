-- Run marked steps separately. Expected SQLSTATEs: 23514, then 25P02.
-- STEP 1: start a transaction and trigger CHECK failure.
BEGIN;
UPDATE rsod5_demo.stock SET quantity = -1 WHERE warehouse_id = 'A';
-- STEP 2: this SELECT fails because the transaction is aborted.
SELECT * FROM rsod5_demo.stock ORDER BY warehouse_id;
-- STEP 3: recover the session. State stays A=2, B=3 if following 01-03.
ROLLBACK;
SELECT * FROM rsod5_demo.stock ORDER BY warehouse_id;
