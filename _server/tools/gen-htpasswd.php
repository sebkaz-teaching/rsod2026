<?php
// One-time helper: generates a line for a Basic Auth password file when
// the hosting panel has no "password protect directory" feature and you
// have no SSH to run `htpasswd`. Upload, open once in the browser, copy
// the output line, then DELETE THIS FILE from the server — it processes
// a plaintext password and must not be left reachable.
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);

$line = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim((string)($_POST['user'] ?? ''));
    $pass = (string)($_POST['pass'] ?? '');
    if ($user !== '' && $pass !== '' && strpos($user, ':') === false) {
        // {SHA} scheme — understood by Apache's mod_auth_basic without
        // needing crypt()/bcrypt support in the httpd build.
        $hash = '{SHA}' . base64_encode(sha1($pass, true));
        $line = $user . ':' . $hash;
    }
}
?>
<!doctype html>
<html lang="pl"><head><meta charset="utf-8"><title>htpasswd generator (usuń po użyciu)</title></head>
<body style="font:14px system-ui;max-width:520px;margin:40px auto;">
<h1>Generator linii do pliku haseł</h1>
<p><strong>Usuń ten plik z serwera zaraz po użyciu.</strong></p>
<form method="post">
  <p>Login: <input name="user" required></p>
  <p>Hasło: <input name="pass" type="password" required></p>
  <button type="submit">Generuj</button>
</form>
<?php if ($line): ?>
<p>Wklej poniższą linijkę do nowego pliku tekstowego (np. <code>passwd</code>), utworzonego przez File Manager <strong>poza</strong> katalogiem publicznym, jeśli panel na to pozwala — inaczej w <code>rsod-quiz/.htpasswds/passwd</code>:</p>
<pre style="background:#eee;padding:10px;user-select:all;"><?= htmlspecialchars($line) ?></pre>
<?php endif; ?>
</body></html>
