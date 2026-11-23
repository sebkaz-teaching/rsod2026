-- Run once in the student's own database. Existing schemas are never overwritten.
-- To repeat exercises, reuse this dataset without running setup again.
BEGIN;
CREATE SCHEMA shop_lab;
CREATE TABLE shop_lab.customers (
    customer_id integer PRIMARY KEY,
    customer_name text NOT NULL,
    city text
);
CREATE TABLE shop_lab.products (
    product_id integer PRIMARY KEY,
    product_name text NOT NULL,
    category text NOT NULL,
    price numeric(10,2) NOT NULL CHECK (price >= 0),
    stock integer NOT NULL CHECK (stock >= 0),
    supplier text
);
CREATE TABLE shop_lab.orders (
    order_id integer PRIMARY KEY,
    customer_id integer REFERENCES shop_lab.customers(customer_id),
    order_date date NOT NULL,
    status text NOT NULL CHECK (status IN ('new', 'paid', 'cancelled'))
);
CREATE TABLE shop_lab.order_items (
    order_id integer REFERENCES shop_lab.orders(order_id),
    product_id integer REFERENCES shop_lab.products(product_id),
    quantity integer NOT NULL CHECK (quantity > 0),
    unit_price numeric(10,2) NOT NULL CHECK (unit_price >= 0),
    PRIMARY KEY (order_id, product_id)
);
INSERT INTO shop_lab.customers VALUES
(1, 'Anna Kowalska', 'Warszawa'), (2, 'Jan Nowak', 'Krakow'),
(3, 'Ola Lis', 'Warszawa'), (4, 'Piotr Wilk', 'Gdansk'),
(5, 'Ewa Kot', NULL), (6, 'Adam Zuk', 'Poznan');
INSERT INTO shop_lab.products VALUES
(1, 'Laptop', 'computers', 3000, 5, 'Acme'),
(2, 'Monitor', 'displays', 800, 10, NULL),
(3, 'Keyboard', 'accessories', 100, 20, 'Acme'),
(4, 'Mouse', 'accessories', 50, 0, NULL),
(5, 'Cable HDMI', 'accessories', 40, 30, 'CableCo'),
(6, 'Router', 'network', 200, 8, 'NetCo'),
(7, 'Switch', 'network', 400, 3, 'NetCo'),
(8, 'Webcam', 'accessories', 150, 0, 'Acme'),
(9, 'Monitor Pro', 'displays', 1200, 2, NULL),
(10, 'Dock', 'accessories', 600, 4, 'Acme');
INSERT INTO shop_lab.orders VALUES
(101, 1, '2026-11-01', 'paid'), (102, 1, '2026-11-03', 'new'),
(103, 2, '2026-11-04', 'paid'), (104, 3, '2026-11-05', 'cancelled'),
(105, NULL, '2026-11-06', 'paid'), (106, 4, '2026-11-08', 'new'),
(107, 2, '2026-11-10', 'paid'), (108, NULL, '2026-11-11', 'new');
INSERT INTO shop_lab.order_items VALUES
(101, 1, 1, 2900), (101, 3, 2, 90),
(102, 2, 1, 800), (102, 4, 2, 50),
(103, 6, 2, 180), (103, 5, 3, 40),
(104, 7, 1, 400), (105, 4, 1, 50),
(106, 9, 1, 1100), (106, 10, 1, 550),
(107, 3, 1, 100), (107, 2, 2, 750);
COMMIT;
