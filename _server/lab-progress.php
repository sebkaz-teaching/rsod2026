<?php
// Public student endpoint: a random bearer token grants access to one registration.
declare(strict_types=1);
require __DIR__ . '/db.php';
$cfg = rsod_config();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: ' . $cfg['allowed_origin']);
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Vary: Origin');
header('Cache-Control: no-store');
function lab_error(int $status, string $message): void {
    http_response_code($status);
    echo json_encode(['error' => $message]);
    exit;
}
if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] !== $cfg['allowed_origin']) {
    lab_error(403, 'origin_not_allowed');
}
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { lab_error(405, 'method_not_allowed'); }
$raw = file_get_contents('php://input', false, null, 0, 4097);
if (strlen($raw) > 4096) { lab_error(413, 'payload_too_large'); }
$body = json_decode($raw, true);
if (!is_array($body)) { lab_error(400, 'invalid_json'); }
// RSOD has one declaration per student/lab, without the WDP review queue.
if (($body['course'] ?? '') === 'rsod2026') {
    if (($body['action'] ?? '') !== 'confirm' || ($body['lab'] ?? '') !== 'lab01') {
        lab_error(422, 'invalid_action');
    }
    $email = $body['email'] ?? null;
    $group = $body['group'] ?? null;
    if (!is_string($email)) { lab_error(422, 'invalid_email'); }
    $email = strtolower(trim($email));
    if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) { lab_error(422, 'invalid_email'); }
    $domain = substr($email, strrpos($email, '@') + 1);
    if (!preg_match('/(?:^|\.)wat\.edu\.pl$/D', $domain)) { lab_error(422, 'email_domain_not_allowed'); }
    if (!is_string($group) || !in_array($group, ['1', '2', '3'], true)) { lab_error(422, 'invalid_group'); }
    try {
        $db = rsod_db();
        $db->exec('PRAGMA busy_timeout = 5000');
        $date = (new DateTimeImmutable('now', new DateTimeZone('Europe/Warsaw')))->format('Y-m-d');
        $insert = $db->prepare('INSERT INTO lab_confirmations
            (course, lab, email, class_group, class_date, confirmed_at)
            VALUES (?, ?, ?, ?, ?, ?) ON CONFLICT(course, lab, email) DO NOTHING');
        $insert->execute(['rsod2026', 'lab01', $email, $group, $date, gmdate('c')]);
        // A retry never alters the original group or confirmation timestamp.
        echo json_encode(['ok' => true]);
    } catch (Throwable $error) {
        error_log('RSOD confirmation failed: ' . $error->getMessage());
        lab_error(503, 'save_unavailable');
    }
    exit;
}
$action = $body['action'] ?? '';
if (!in_array($action, ['start', 'status', 'submit', 'finish'], true)) { lab_error(422, 'invalid_action'); }
$token = $body['token'] ?? null;
if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/D', $token)) { lab_error(422, 'invalid_token'); }
$hash = hash('sha256', $token);
try {
    $db = rsod_db();
    $db->exec('PRAGMA busy_timeout = 5000');
    if ($action === 'start') {
        $email = $body['email'] ?? null;
        if (!is_string($email)) { lab_error(422, 'invalid_email'); }
        $email = strtolower(trim($email));
        if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) { lab_error(422, 'invalid_email'); }
        $domain = substr($email, strrpos($email, '@') + 1);
        if (!preg_match('/(?:^|\.)wat\.edu\.pl$/D', $domain)) { lab_error(422, 'email_domain_not_allowed'); }
        $date = (new DateTimeImmutable('now', new DateTimeZone('Europe/Warsaw')))->format('Y-m-d');
        $insert = $db->prepare('INSERT INTO lab_participants
            (course, lab, class_date, email, token_hash, started_at)
            VALUES (?, ?, ?, ?, ?, ?) ON CONFLICT(course, lab, class_date, email) DO NOTHING');
        $insert->execute(['wdplab2026', 'cpp01', $date, $email, $hash, gmdate('c')]);
        $select = $db->prepare('SELECT * FROM lab_participants WHERE course = ? AND lab = ? AND class_date = ? AND email = ?');
        $select->execute(['wdplab2026', 'cpp01', $date, $email]);
        $participant = $select->fetch(PDO::FETCH_ASSOC);
        if (!hash_equals($participant['token_hash'], $hash)) { lab_error(409, 'already_registered'); }
    } else {
        $select = $db->prepare('SELECT * FROM lab_participants WHERE token_hash = ? ORDER BY id DESC LIMIT 1');
        $select->execute([$hash]);
        $participant = $select->fetch(PDO::FETCH_ASSOC);
        if (!$participant) { lab_error(403, 'invalid_access'); }
    }
    $id = (int)$participant['id'];
    if ($action === 'submit') {
        $task = $body['task'] ?? null;
        if (!is_int($task) || $task < 1 || $task > 15) { lab_error(422, 'invalid_task'); }
        // An approved task cannot be overwritten by a student's request.
        $update = $db->prepare("INSERT INTO lab_tasks (participant_id, task, status, submitted_at)
            VALUES (?, ?, 'pending', ?) ON CONFLICT(participant_id, task) DO UPDATE SET
            status = 'pending', submitted_at = excluded.submitted_at, reviewed_at = NULL, reviewed_by = NULL
            WHERE lab_tasks.status = 'revision'");
        $update->execute([$id, $task, gmdate('c')]);
    }
    if ($action === 'finish') {
        $db->prepare('UPDATE lab_participants SET finished_at = COALESCE(finished_at, ?) WHERE id = ?')
            ->execute([gmdate('c'), $id]);
    }
    $select = $db->prepare('SELECT task, status, submitted_at, reviewed_at FROM lab_tasks WHERE participant_id = ? ORDER BY task');
    $select->execute([$id]);
    $finished = $db->prepare('SELECT finished_at FROM lab_participants WHERE id = ?');
    $finished->execute([$id]);
    echo json_encode(['ok' => true, 'email' => $participant['email'], 'class_date' => $participant['class_date'],
        'finished_at' => $finished->fetchColumn(), 'tasks' => $select->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Throwable $error) {
    error_log('Lab progress failed: ' . $error->getMessage());
    lab_error(503, 'save_unavailable');
}
