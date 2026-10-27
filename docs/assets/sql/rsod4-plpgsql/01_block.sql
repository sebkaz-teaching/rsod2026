-- NOTICE output appears in the Messages tab in pgAdmin.
DO $$
DECLARE
    v_available integer := 5;
    v_requested integer := 2;
    v_remaining integer;
BEGIN
    v_remaining := v_available - v_requested;
    RAISE NOTICE 'Available: %, requested: %, remaining: %',
        v_available, v_requested, v_remaining;
END;
$$;
