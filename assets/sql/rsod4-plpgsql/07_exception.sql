-- The inner block's successful and failing updates are both rolled back.
DO $$
BEGIN
    BEGIN
        UPDATE rsod4_demo.stock SET quantity = quantity + 1 WHERE item_id = 2;
        UPDATE rsod4_demo.stock SET quantity = -1 WHERE item_id = 1;
    EXCEPTION
        WHEN check_violation THEN
            RAISE NOTICE 'CHECK rejected negative stock; both inner updates were undone';
    END;
    RAISE NOTICE 'Execution continues after the handled error';
END;
$$;
SELECT * FROM rsod4_demo.stock ORDER BY item_id;
