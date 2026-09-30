<?php
// Store open-ended PRBA work; no automatic grading or public read endpoint.
declare(strict_types=1);
require __DIR__ . '/db.php';
$cfg = rsod_config();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: ' . $cfg['allowed_origin']);
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Vary: Origin');
header('Cache-Control: no-store');
function assignment_error(int $status, string $message): void {
    http_response_code($status);
    echo json_encode(['error' => $message]);
    exit;
}
if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] !== $cfg['allowed_origin']) {
    assignment_error(403, 'origin_not_allowed');
}
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { assignment_error(405, 'method_not_allowed'); }
$raw = file_get_contents('php://input', false, null, 0, 65537);
if (strlen($raw) > 65536) { assignment_error(413, 'payload_too_large'); }
$body = json_decode($raw, true);
if (!is_array($body)) { assignment_error(400, 'invalid_json'); }
foreach (['request_id', 'course', 'lecture', 'email', 'partner_email', 'authors'] as $field) {
    if (!isset($body[$field]) || !is_string($body[$field])) { assignment_error(422, 'invalid_' . $field); }
    $body[$field] = trim($body[$field]);
}
if ($body['course'] !== 'prba2026' || $body['lecture'] !== 'wyklad1') {
    assignment_error(422, 'unknown_assignment');
}
if (!preg_match('/^[a-f0-9-]{36}$/D', $body['request_id'])) { assignment_error(422, 'invalid_request_id'); }
foreach (['email', 'partner_email'] as $field) {
    if ($field === 'partner_email' && $body[$field] === '') { continue; }
    if (strlen($body[$field]) > 254 || !filter_var($body[$field], FILTER_VALIDATE_EMAIL)) {
        assignment_error(422, 'invalid_' . $field);
    }
}
if ($body['partner_email'] !== '' && strcasecmp($body['email'], $body['partner_email']) === 0) {
    assignment_error(422, 'duplicate_email');
}
if ($body['authors'] === '' || strlen($body['authors']) > 800) { assignment_error(422, 'invalid_authors'); }
$input = $body['answers'] ?? null;
if (!is_array($input)) { assignment_error(422, 'invalid_answers'); }
$answers = [];
foreach (['question1', 'question2', 'question3', 'concepts', 'rules', 'case'] as $field) {
    if (!isset($input[$field]) || !is_string($input[$field])) { assignment_error(422, 'invalid_' . $field); }
    $value = trim($input[$field]);
    if (strlen($value) > 12000 || (strpos($field, 'question') === 0 && $value === '')) {
        assignment_error(422, 'invalid_' . $field);
    }
    $answers[$field] = $value;
}
$encoded = json_encode($answers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
try {
    $db = rsod_db();
    $db->exec('PRAGMA busy_timeout = 5000');
    $db->beginTransaction();
    $insert = $db->prepare('INSERT INTO assignment_submissions
        (request_id, course, lecture, email, partner_email, authors, answers, submitted_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ON CONFLICT(request_id) DO NOTHING');
    $insert->execute([$body['request_id'], $body['course'], $body['lecture'], $body['email'],
        $body['partner_email'], $body['authors'], $encoded, gmdate('c')]);
    $select = $db->prepare('SELECT * FROM assignment_submissions WHERE request_id = ?');
    $select->execute([$body['request_id']]);
    $saved = $select->fetch(PDO::FETCH_ASSOC);
    foreach (['course', 'lecture', 'email', 'partner_email', 'authors'] as $field) {
        if ($saved[$field] !== $body[$field]) {
            $db->rollBack(); assignment_error(409, 'request_id_conflict');
        }
    }
    if ($saved['answers'] !== $encoded) { $db->rollBack(); assignment_error(409, 'request_id_conflict'); }
    $db->commit();
    echo json_encode(['ok' => true, 'id' => (int)$saved['id'], 'submitted_at' => $saved['submitted_at']]);
} catch (Throwable $error) {
    if (isset($db) && $db->inTransaction()) { $db->rollBack(); }
    error_log('Assignment submission failed: ' . $error->getMessage());
    assignment_error(503, 'save_unavailable');
}
