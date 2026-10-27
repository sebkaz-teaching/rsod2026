-- Run once in a separate teaching database. Existing schemas are not overwritten.
BEGIN;
CREATE SCHEMA rsod4_demo;
CREATE TABLE rsod4_demo.stock (
    item_id integer PRIMARY KEY,
    item_name text NOT NULL,
    quantity integer NOT NULL CHECK (quantity >= 0)
);
INSERT INTO rsod4_demo.stock (item_id, item_name, quantity)
VALUES (1, 'Radio', 5), (2, 'Tent', 3), (3, 'Battery', 0);
COMMIT;
SELECT * FROM rsod4_demo.stock ORDER BY item_id;
