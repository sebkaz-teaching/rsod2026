-- Exactly the same SELECT as in 01_before.sql; repeat to observe cache effects.
EXPLAIN (ANALYZE, BUFFERS)
SELECT participant_id
FROM rsod_indexes_demo.enrollments
WHERE edition_id = 42 AND status = 'confirmed'
ORDER BY participant_id;

-- Explain the operational query after isolating the enrollment lookup.
EXPLAIN (ANALYZE, BUFFERS)
SELECT p.participant_id, p.name
FROM rsod_indexes_demo.enrollments AS e
JOIN rsod_indexes_demo.participants AS p USING (participant_id)
WHERE e.edition_id = 42 AND e.status = 'confirmed'
ORDER BY e.participant_id;
