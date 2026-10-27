-- Run after 00_setup.sql. Change v_item_id to 999 to test a missing row.
DO $$
DECLARE
    v_item_id integer := 1;
    v_quantity integer;
BEGIN
    SELECT quantity INTO v_quantity
    FROM rsod4_demo.stock
    WHERE item_id = v_item_id;

    IF NOT FOUND THEN
        RAISE NOTICE 'Unknown item: %', v_item_id;
    ELSE
        RAISE NOTICE 'Item %, quantity %', v_item_id, v_quantity;
    END IF;
END;
$$;
