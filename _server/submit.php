<?php
declare(strict_types=1);

require __DIR__ . '/db.php';

$cfg = rsod_config();

// --- CORS -------------------------------------------------------------
header('Access-Control-Allow-Origin: ' . $cfg['allowed_origin']);
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Vary: Origin');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'method_not_allowed']);
    exit;
}

header('Content-Type: application/json');

// --- Parse & validate input --------------------------------------------
$raw = file_get_contents('php://input');
$body = json_decode($raw, true);

if (!is_array($body)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_json']);
    exit;
}

$email = trim((string)($body['email'] ?? ''));
$lecture = trim((string)($body['lecture'] ?? ''));
$answers = $body['answers'] ?? null;

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['error' => 'invalid_email']);
    exit;
}

if (!isset($cfg['answer_keys'][$lecture])) {
    http_response_code(422);
    echo json_encode(['error' => 'unknown_lecture']);
    exit;
}

if (!is_array($answers)) {
    http_response_code(422);
    echo json_encode(['error' => 'invalid_answers']);
    exit;
}

// --- Recompute the score server-side — never trust a score sent by the
// browser, it can be edited in devtools before the request goes out.
$key = $cfg['answer_keys'][$lecture];
$score = 0;
$maxScore = 0;
foreach ($key as $qid => $spec) {
    $maxScore += $spec['points'];
    if (($answers[$qid] ?? null) === $spec['correct']) {
        $score += $spec['points'];
    }
}

$db = rsod_db();
$stmt = $db->prepare(
    'INSERT INTO submissions (lecture, email, score, max_score, answers, submitted_at, ip)
     VALUES (:lecture, :email, :score, :max_score, :answers, :submitted_at, :ip)'
);
$stmt->execute([
    ':lecture' => $lecture,
    ':email' => $email,
    ':score' => $score,
    ':max_score' => $maxScore,
    ':answers' => json_encode($answers),
    ':submitted_at' => gmdate('c'),
    ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
]);

echo json_encode([
    'ok' => true,
    'score' => $score,
    'maxScore' => $maxScore,
]);
