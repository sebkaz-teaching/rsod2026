-- Change only v_requested to 7, 0 or NULL and run the complete block again.
DO $$
DECLARE
    v_available integer := 5;
    v_requested integer := 2;
BEGIN
    IF v_requested IS NULL OR v_requested <= 0 THEN
        RAISE NOTICE 'Invalid request';
    ELSIF v_requested > v_available THEN
        RAISE NOTICE 'Insufficient stock';
    ELSE
        RAISE NOTICE 'Request accepted; remaining: %', v_available - v_requested;
    END IF;
END;
$$;
