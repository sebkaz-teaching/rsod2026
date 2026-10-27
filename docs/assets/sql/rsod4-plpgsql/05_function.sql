-- A pure decision function: it does not read or change the stock table.
CREATE OR REPLACE FUNCTION rsod4_demo.stock_status(p_quantity integer)
RETURNS text
LANGUAGE plpgsql
AS $$
BEGIN
    IF p_quantity IS NULL OR p_quantity < 0 THEN
        RETURN 'invalid';
    ELSIF p_quantity = 0 THEN
        RETURN 'unavailable';
    ELSE
        RETURN 'available';
    END IF;
END;
$$;

SELECT quantity, rsod4_demo.stock_status(quantity) AS status
FROM (VALUES (5), (0), (-1), (NULL::integer)) AS samples(quantity);

SELECT item_name, quantity, rsod4_demo.stock_status(quantity) AS status
FROM rsod4_demo.stock
ORDER BY item_id;
