-- Run each complete DO block separately to compare the two loops.
DO $$
BEGIN
    FOR v_number IN 1..3 LOOP
        RAISE NOTICE 'FOR label: %', v_number;
    END LOOP;
END;
$$;

DO $$
DECLARE
    v_number integer := 1;
BEGIN
    WHILE v_number <= 3 LOOP
        RAISE NOTICE 'WHILE label: %', v_number;
        v_number := v_number + 1;
    END LOOP;
END;
$$;
