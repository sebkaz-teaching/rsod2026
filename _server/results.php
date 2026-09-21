<?php
declare(strict_types=1);
// Access to this file must be restricted via .htaccess (Basic Auth) —
// see _server/README.md. It is NOT protected on its own.

require __DIR__ . '/db.php';

$db = rsod_db();
$lecture = $_GET['lecture'] ?? null;

if ($lecture) {
    $stmt = $db->prepare('SELECT * FROM submissions WHERE lecture = :lecture ORDER BY submitted_at DESC');
    $stmt->execute([':lecture' => $lecture]);
} else {
    $stmt = $db->query('SELECT * FROM submissions ORDER BY submitted_at DESC');
}
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$lecturesStmt = $db->query('SELECT DISTINCT lecture FROM submissions ORDER BY lecture');
$lectures = $lecturesStmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<title>RSOD — wyniki testów</title>
<style>
  body{font:14px/1.5 system-ui,sans-serif;margin:24px;color:#1a2230;background:#f4f6f8;}
  h1{font-size:1.3rem;}
  nav a{margin-right:10px;font-size:.85rem;}
  table{border-collapse:collapse;width:100%;background:#fff;}
  th,td{border:1px solid #d7dde4;padding:6px 10px;text-align:left;font-size:.85rem;}
  th{background:#eef1f5;}
  tr:nth-child(even){background:#f8f9fb;}
  .score{font-family:ui-monospace,monospace;}
</style>
</head>
<body>
<h1>RSOD — wyniki testów<?= $lecture ? ' — ' . htmlspecialchars($lecture) : '' ?></h1>
<nav>
<a href="results.php">wszystkie</a>
<?php foreach ($lectures as $l): ?>
<a href="results.php?lecture=<?= urlencode($l) ?>"><?= htmlspecialchars($l) ?></a>
<?php endforeach; ?>
</nav>
<p><?= count($rows) ?> zgłoszeń</p>
<table>
<thead><tr><th>Data</th><th>Wykład</th><th>E-mail</th><th>Wynik</th><th>Odpowiedzi</th></tr></thead>
<tbody>
<?php foreach ($rows as $r): ?>
<tr>
  <td><?= htmlspecialchars($r['submitted_at']) ?></td>
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
