-- Run before creating the additional index. Save the full plan.
SELECT indexname, indexdef FROM pg_indexes
WHERE schemaname = 'rsod_indexes_demo' AND tablename = 'enrollments';
EXPLAIN (ANALYZE, BUFFERS)
SELECT participant_id
FROM rsod_indexes_demo.enrollments
WHERE edition_id = 42 AND status = 'confirmed'
ORDER BY participant_id;
