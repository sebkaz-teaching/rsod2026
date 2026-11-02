-- Run once in a separate teaching database. Existing schemas are not overwritten.
-- Synthetic scale model; the original E1/E2/E3 classroom dataset is unchanged.
BEGIN;
CREATE SCHEMA rsod_indexes_demo;
CREATE TABLE rsod_indexes_demo.courses (
    course_id integer PRIMARY KEY, name text NOT NULL
);
CREATE TABLE rsod_indexes_demo.rooms (
    room_id integer PRIMARY KEY, name text NOT NULL,
    capacity integer NOT NULL CHECK (capacity > 0)
);
CREATE TABLE rsod_indexes_demo.instructors (
    instructor_id integer PRIMARY KEY, name text NOT NULL
);
CREATE TABLE rsod_indexes_demo.participants (
    participant_id integer PRIMARY KEY, name text NOT NULL
);
CREATE TABLE rsod_indexes_demo.editions (
    edition_id integer PRIMARY KEY,
    course_id integer NOT NULL REFERENCES rsod_indexes_demo.courses,
    starts_at timestamp NOT NULL, ends_at timestamp NOT NULL,
    room_id integer NOT NULL REFERENCES rsod_indexes_demo.rooms,
    instructor_id integer NOT NULL REFERENCES rsod_indexes_demo.instructors,
    seat_limit integer NOT NULL CHECK (seat_limit > 0),
    CHECK (ends_at > starts_at)
);
CREATE TABLE rsod_indexes_demo.enrollments (
    participant_id integer NOT NULL REFERENCES rsod_indexes_demo.participants,
    edition_id integer NOT NULL REFERENCES rsod_indexes_demo.editions,
    status text NOT NULL CHECK (status IN ('confirmed', 'cancelled')),
    PRIMARY KEY (participant_id, edition_id)
);
INSERT INTO rsod_indexes_demo.courses VALUES (1, 'Network administration');
INSERT INTO rsod_indexes_demo.rooms VALUES (1, 'Lecture hall', 250);
INSERT INTO rsod_indexes_demo.instructors VALUES (1, 'Instructor A');
INSERT INTO rsod_indexes_demo.participants
SELECT n, 'Participant ' || n FROM generate_series(1, 100000) AS g(n);
-- Sequential dates prevent room and instructor conflicts in this generated sample.
INSERT INTO rsod_indexes_demo.editions
SELECT n, 1, timestamp '2020-01-01 08:00' + (n - 1) * interval '1 day',
       timestamp '2020-01-01 10:00' + (n - 1) * interval '1 day', 1, 1, 250
FROM generate_series(1, 1000) AS g(n);
-- Each participant has two distinct editions. Each edition has 200 enrollments.
INSERT INTO rsod_indexes_demo.enrollments
SELECT n, ((n - 1 + offset_id) % 1000) + 1,
       CASE WHEN ((n - 1) / 1000) % 10 = 0 THEN 'cancelled' ELSE 'confirmed' END
FROM generate_series(1, 100000) AS g(n)
CROSS JOIN (VALUES (0), (1)) AS offsets(offset_id);
COMMIT;
ANALYZE rsod_indexes_demo.enrollments;
ANALYZE rsod_indexes_demo.participants;
SELECT count(*) AS enrollment_count,
       count(*) FILTER (WHERE status = 'confirmed') AS confirmed_count
FROM rsod_indexes_demo.enrollments;
SELECT edition_id, status, count(*)
FROM rsod_indexes_demo.enrollments WHERE edition_id = 42 GROUP BY edition_id, status;
