-- Restore only the three teaching rows; keep the schema and function.
UPDATE rsod4_demo.stock AS s
SET quantity = baseline.quantity
FROM (VALUES (1, 5), (2, 3), (3, 0)) AS baseline(item_id, quantity)
WHERE s.item_id = baseline.item_id;
SELECT * FROM rsod4_demo.stock ORDER BY item_id;
