-- Run once; the primary key index has a different leading column.
CREATE INDEX enrollments_edition_status_participant_idx
ON rsod_indexes_demo.enrollments (edition_id, status, participant_id);
ANALYZE rsod_indexes_demo.enrollments;
