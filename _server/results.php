<?php
declare(strict_types=1);
// Access to this file must be restricted via .htaccess (Basic Auth) —
// see _server/README.md. It is NOT protected on its own.

require __DIR__ . '/db.php';

$db = rsod_db();
$course = $_GET['course'] ?? null;
$lecture = $_GET['lecture'] ?? null;

$where = [];
$params = [];
if ($course) { $where[] = 'course = :course'; $params[':course'] = $course; }
if ($lecture) { $where[] = 'lecture = :lecture'; $params[':lecture'] = $lecture; }
$sql = 'SELECT * FROM submissions';
if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
$sql .= ' ORDER BY submitted_at DESC';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$courses = $db->query('SELECT DISTINCT course FROM submissions ORDER BY course')->fetchAll(PDO::FETCH_COLUMN);
$lecturesStmt = $course
    ? $db->prepare('SELECT DISTINCT lecture FROM submissions WHERE course = :course ORDER BY lecture')
    : $db->prepare('SELECT DISTINCT lecture FROM submissions ORDER BY lecture');
$lecturesStmt->execute($course ? [':course' => $course] : []);
$lectures = $lecturesStmt->fetchAll(PDO::FETCH_COLUMN);

function qs(array $overrides = []): string
{
    global $course, $lecture;
    $params = array_filter(['course' => $course, 'lecture' => $lecture] + $overrides);
    return $params ? '?' . http_build_query($params) : '';
}
?>
<!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<title>Wyniki testów</title>
<style>
  body{font:14px/1.5 system-ui,sans-serif;margin:24px;color:#1a2230;background:#f4f6f8;}
  h1{font-size:1.3rem;}
  nav{margin-bottom:6px;}
  nav a{margin-right:10px;font-size:.85rem;}
  nav a.active{font-weight:600;text-decoration:underline;}
  table{border-collapse:collapse;width:100%;background:#fff;margin-top:10px;}
  th,td{border:1px solid #d7dde4;padding:6px 10px;text-align:left;font-size:.85rem;}
  th{background:#eef1f5;}
  tr:nth-child(even){background:#f8f9fb;}
  .score{font-family:ui-monospace,monospace;}
</style>
</head>
<body>
<h1>Wyniki testów<?= $course ? ' — ' . htmlspecialchars($course) : '' ?><?= $lecture ? ' / ' . htmlspecialchars($lecture) : '' ?></h1>

<nav>
Przedmiot:
<a class="<?= !$course ? 'active' : '' ?>" href="results.php<?= qs(['course' => null, 'lecture' => null]) ?>">wszystkie</a>
<?php foreach ($courses as $c): ?>
<a class="<?= $course === $c ? 'active' : '' ?>" href="results.php?course=<?= urlencode($c) ?>"><?= htmlspecialchars($c) ?></a>
<?php endforeach; ?>
</nav>
<nav>
Wykład:
<a class="<?= !$lecture ? 'active' : '' ?>" href="results.php<?= qs(['lecture' => null]) ?>">wszystkie</a>
<?php foreach ($lectures as $l): ?>
<a class="<?= $lecture === $l ? 'active' : '' ?>" href="results.php<?= qs(['lecture' => $l]) ?>"><?= htmlspecialchars($l) ?></a>
<?php endforeach; ?>
</nav>

<p><?= count($rows) ?> zgłoszeń</p>
<table>
<thead><tr><th>Data</th><th>Przedmiot</th><th>Wykład</th><th>E-mail</th><th>Wynik</th><th>Odpowiedzi</th></tr></thead>
<tbody>
<?php foreach ($rows as $r): ?>
<tr>
  <td><?= htmlspecialchars($r['submitted_at']) ?></td>
  <td><?= htmlspecialchars($r['course']) ?></td>
  <td><?= htmlspecialchars($r['lecture']) ?></td>
  <td><?= htmlspecialchars($r['email']) ?></td>
  <td class="score"><?= (int)$r['score'] ?> / <?= (int)$r['max_score'] ?></td>
  <td class="score"><?= htmlspecialchars($r['answers']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</body>
</html>
