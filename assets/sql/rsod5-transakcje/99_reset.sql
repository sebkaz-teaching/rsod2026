-- Use only AFTER both sessions have COMMITted or ROLLBACKed.
-- Restore this lecture's two rows only. No other demonstration is touched.
BEGIN;
UPDATE rsod5_demo.stock SET quantity = CASE warehouse_id WHEN 'A' THEN 5 ELSE 0 END;
COMMIT;
SELECT * FROM rsod5_demo.stock ORDER BY warehouse_id;
