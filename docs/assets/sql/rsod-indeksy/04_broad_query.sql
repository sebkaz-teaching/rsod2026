-- 90% of rows match. Compare this plan with the selective edition query.
EXPLAIN (ANALYZE, BUFFERS)
SELECT participant_id, edition_id, status
FROM rsod_indexes_demo.enrollments
WHERE status = 'confirmed';

SELECT pg_size_pretty(pg_relation_size('rsod_indexes_demo.enrollments')) AS table_size,
       pg_size_pretty(pg_indexes_size('rsod_indexes_demo.enrollments')) AS indexes_size;
