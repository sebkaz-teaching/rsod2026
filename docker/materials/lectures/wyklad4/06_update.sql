-- Initially run with 2, then with 7. Repeated successful calls change stock.
DO $$
DECLARE
    v_item_id integer := 1;
    v_requested integer := 2;
    v_remaining integer;
BEGIN
    IF v_requested IS NULL OR v_requested <= 0 THEN
        RAISE EXCEPTION 'Requested quantity must be positive';
    END IF;

    UPDATE rsod4_demo.stock
    SET quantity = quantity - v_requested
    WHERE item_id = v_item_id AND quantity >= v_requested
    RETURNING quantity INTO v_remaining;

    IF FOUND THEN
        RAISE NOTICE 'Issued: %, remaining: %', v_requested, v_remaining;
    ELSE
        RAISE NOTICE 'Nothing issued: unknown item or insufficient stock';
    END IF;
END;
$$;
SELECT * FROM rsod4_demo.stock ORDER BY item_id;
