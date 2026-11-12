<?php
// Included only by the existing Basic-Auth-protected results.php route.
declare(strict_types=1);
if (!defined('LAB_PANEL_ENTRY')) { http_response_code(404); exit; }
$teacher = $_SERVER['REMOTE_USER'] ?? $_SERVER['PHP_AUTH_USER'] ?? '';
if (!is_string($teacher) || $teacher === '') {
    http_response_code(403);
    exit('Panel wymaga logowania prowadzącego przez ochronę serwera (Basic Auth).');
}
header('Cache-Control: no-store');
header('X-Frame-Options: DENY');
session_start(['use_strict_mode' => true, 'cookie_httponly' => true,
    'cookie_secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'), 'cookie_samesite' => 'Strict']);
if (!isset($_SESSION['lab_csrf'])) { $_SESSION['lab_csrf'] = bin2hex(random_bytes(32)); }
// Read-only RSOD summary shares the existing authenticated entry point.
if (($_GET['course'] ?? '') === 'rsod2026') {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); exit; }
    $date = $_GET['date'] ?? '';
    $group = $_GET['group'] ?? '';
    if (!is_string($date) || !is_string($group) ||
        !in_array($group, ['', '1', '2', '3'], true)) { http_response_code(422); exit('Niepoprawny filtr.'); }
    if ($date !== '') {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if (!$parsed || $parsed->format('Y-m-d') !== $date) { http_response_code(422); exit('Niepoprawna data.'); }
    }
    $sql = "SELECT * FROM lab_confirmations WHERE course = 'rsod2026' AND lab = 'lab01'";
    $params = [];
    if ($date !== '') { $sql .= ' AND class_date = ?'; $params[] = $date; }
    if ($group !== '') { $sql .= ' AND class_group = ?'; $params[] = $group; }
    $query = rsod_db()->prepare($sql . ' ORDER BY class_date DESC, class_group, email');
    $query->execute($params);
    $rows = $query->fetchAll(PDO::FETCH_ASSOC);
    $h = fn(string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    ?>
<!doctype html><html lang="pl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>RSOD — potwierdzenia laboratorium 1</title>
<style>body{font:16px system-ui;margin:24px;color:#203247}table{border-collapse:collapse;width:100%}th,td{border:1px solid #ccd8e4;padding:10px;text-align:left}input,select,button{font:inherit;padding:8px}nav a{margin-right:16px}</style></head><body>
<nav><a href="results.php">Testy</a><a href="results.php?view=labs">WDP — laboratoria</a><a href="results.php?view=labs&amp;course=rsod2026">RSOD — laboratoria</a></nav>
<h1>RSOD — laboratorium 1</h1>
<p>Potwierdzenia studentów po ustnym zaliczeniu przez prowadzącego. Bez zgłaszania do sprawdzenia i dodatkowego zatwierdzania w panelu.</p>
<form method="get"><input type="hidden" name="view" value="labs"><input type="hidden" name="course" value="rsod2026">
<label>Data (pusta = wszystkie): <input type="date" name="date" value="<?= $h($date) ?>"></label>
<label>Grupa: <select name="group"><option value="">Wszystkie</option><?php foreach (['1','2','3'] as $g): ?><option value="<?= $g ?>" <?= $group === $g ? 'selected' : '' ?>>Grupa <?= $g ?></option><?php endforeach; ?></select></label><button>Pokaż / odśwież</button></form>
<p><strong><?= count($rows) ?> potwierdzeń</strong>. Czas w strefie Europe/Warsaw. Lista odświeża się co 30 sekund.</p>
<table><thead><tr><th>Student</th><th>Grupa</th><th>Laboratorium</th><th>Data</th><th>Czas potwierdzenia</th><th>Status</th></tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr><td><?= $h($row['email']) ?></td><td>Grupa <?= $h($row['class_group']) ?></td><td>1 — pgAdmin</td><td><?= $h($row['class_date']) ?></td><td><?= $h((new DateTimeImmutable($row['confirmed_at']))->setTimezone(new DateTimeZone('Europe/Warsaw'))->format('H:i:s')) ?></td><td>Potwierdzone przez studenta po ustnym zaliczeniu</td></tr><?php endforeach; ?>
</tbody></table><?php if (!$rows): ?><p>Brak potwierdzeń dla wybranych filtrów.</p><?php endif; ?>
<script>setInterval(function(){if(!document.querySelector('input:focus,select:focus,button:focus')) location.reload();},30000);</script>
</body></html>
    <?php
    exit;
}
$today = (new DateTimeImmutable('now', new DateTimeZone('Europe/Warsaw')))->format('Y-m-d');
$date = $_GET['date'] ?? $today;
if (!is_string($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date)) {
    http_response_code(422); exit('Niepoprawna data.');
}
$db = rsod_db();
$db->exec('PRAGMA busy_timeout = 5000');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf'] ?? '';
    if (!is_string($csrf) || !hash_equals($_SESSION['lab_csrf'], $csrf)) {
        http_response_code(403); exit('Sesja formularza wygasła. Odśwież panel.');
    }
    $id = filter_var($_POST['participant'] ?? '', FILTER_VALIDATE_INT);
    $task = filter_var($_POST['task'] ?? '', FILTER_VALIDATE_INT);
    $status = $_POST['status'] ?? '';
    if (!$id || !$task || $task < 1 || $task > 15 || !in_array($status, ['approved', 'revision'], true)) {
        http_response_code(422); exit('Niepoprawna zmiana statusu.');
    }
    $update = $db->prepare("UPDATE lab_tasks SET status = ?, reviewed_at = ?, reviewed_by = ?
        WHERE participant_id = ? AND task = ? AND EXISTS
        (SELECT 1 FROM lab_participants p WHERE p.id = participant_id AND p.course = 'wdplab2026'
        AND p.lab = 'cpp01' AND p.class_date = ?)");
    $update->execute([$status, gmdate('c'), $teacher, $id, $task, $date]);
    header('Location: results.php?view=labs&date=' . rawurlencode($date), true, 303);
    exit;
}
$select = $db->prepare("SELECT * FROM lab_participants WHERE course = 'wdplab2026' AND lab = 'cpp01' AND class_date = ? ORDER BY started_at, email");
$select->execute([$date]);
$participants = $select->fetchAll(PDO::FETCH_ASSOC);
$select = $db->prepare("SELECT t.* FROM lab_tasks t JOIN lab_participants p ON p.id = t.participant_id
    WHERE p.course = 'wdplab2026' AND p.lab = 'cpp01' AND p.class_date = ?");
$select->execute([$date]);
$tasks = [];
foreach ($select->fetchAll(PDO::FETCH_ASSOC) as $row) { $tasks[$row['participant_id']][$row['task']] = $row; }
function lab_h(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function lab_time(?string $value): string {
    return $value ? (new DateTimeImmutable($value))->setTimezone(new DateTimeZone('Europe/Warsaw'))->format('H:i:s') : '—';
}
?>
<!doctype html><html lang="pl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Laboratoria — uczestnicy i zadania</title>
<style>
body{font:15px system-ui,sans-serif;margin:20px;color:#203247;background:#f3f6fa}nav a{margin-right:16px}table{border-collapse:collapse;background:white;width:100%}td,th{border:1px solid #ccd8e4;padding:10px;text-align:left;vertical-align:top}button,input{font:inherit;padding:7px;margin:3px}button{cursor:pointer}.scroll{overflow:auto}.pending{background:#fff3cb}.approved{background:#dff2e5}.revision{background:#ffe7e2}small{display:block}td form{min-width:135px}summary{cursor:pointer}th:first-child{min-width:230px}
</style></head><body>
<nav><a href="results.php">Testy</a><a href="results.php?view=assignments">Prace opisowe</a><a href="results.php?view=labs">Laboratoria</a></nav>
<p><a href="results.php?view=labs&amp;course=rsod2026">RSOD — potwierdzenia laboratoriów</a></p>
<h1>WDP — laboratorium 1</h1>
<p>Zalogowany prowadzący: <strong><?= lab_h($teacher) ?></strong></p>
<form method="get"><input type="hidden" name="view" value="labs"><label>Dzień zajęć: <input type="date" name="date" value="<?= lab_h($date) ?>" required></label><button>Pokaż / odśwież</button></form>
<p><strong><?= count($participants) ?> uczestników zgłosiło rozpoczęcie zajęć.</strong> To zgłoszenia e-mail, nie automatyczne potwierdzenie obecności. Lista odświeża się co 30 sekund, jeśli nie korzystasz z formularza.</p>
<p>Student zgłasza zadanie; prowadzący zatwierdza je po sprawdzeniu programu. „Do poprawy” umożliwia ponowne zgłoszenie. Kliknij zatwierdzone zadanie, aby skorygować omyłkowe zatwierdzenie.</p>
<div class="scroll"><table><thead><tr><th>Student / rozpoczęcie</th><th>Podsumowanie</th><?php for ($n=1; $n<=15; $n++): ?><th>Z<?= $n ?></th><?php endfor; ?></tr></thead><tbody>
<?php foreach ($participants as $p): $own = $tasks[$p['id']] ?? []; $approved = count(array_filter($own, fn($t) => $t['status'] === 'approved')); ?>
<tr><td><?= lab_h($p['email']) ?><small>Rozpoczęcie: <?= lab_time($p['started_at']) ?></small></td>
<td><?= $approved ?>/15 zatwierdzonych<small><?= count($own) ?>/15 zgłoszonych</small><small><?= $p['finished_at'] ? 'Zakończono: ' . lab_time($p['finished_at']) : 'Praca w toku' ?></small></td>
<?php for ($n=1; $n<=15; $n++): $t = $own[$n] ?? null; ?>
<td class="<?= $t ? lab_h($t['status']) : '' ?>"><?php if (!$t): ?>—<?php else: ?>
<?= ['pending'=>'Do sprawdzenia','approved'=>'Zatwierdzone','revision'=>'Do poprawy'][$t['status']] ?>
<small>Zgłoszono: <?= lab_time($t['submitted_at']) ?></small>
<?php if ($t['reviewed_by']): ?><small><?= lab_h($t['reviewed_by']) ?>, <?= lab_time($t['reviewed_at']) ?></small><?php endif; ?>
<details><summary><?= $t['status'] === 'pending' ? 'Sprawdź' : 'Zmień status' ?></summary><form method="post">
<input type="hidden" name="csrf" value="<?= lab_h($_SESSION['lab_csrf']) ?>"><input type="hidden" name="participant" value="<?= (int)$p['id'] ?>"><input type="hidden" name="task" value="<?= $n ?>">
<button name="status" value="approved">Zatwierdź</button><button name="status" value="revision">Do poprawy</button>
</form></details><?php endif; ?></td>
<?php endfor; ?></tr><?php endforeach; ?></tbody></table></div>
<script>setInterval(function(){if(!document.querySelector('details[open]') && !document.querySelector('input:focus,button:focus')) location.reload();},30000);</script>
</body></html>
