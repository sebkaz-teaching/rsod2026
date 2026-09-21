# RSOD quiz backend (PHP + SQLite)

Minimal write endpoint for the per-lecture quizzes, meant to be uploaded
via FTP to `https://sebastianzajac.pl/rsod-quiz/` (or any subdirectory —
adjust `allowed_origin`/paths if you rename it).

## Deploy

1. FTP-upload the whole `_server/` folder contents to `rsod-quiz/` on
   the host (i.e. `submit.php`, `results.php`, `db.php`, `config.php`,
   `schema.sql`, `.htaccess` all sit directly in `rsod-quiz/`).
2. Copy `config.php` to `config.local.php` on the server and adjust
   `db_path` if needed (default: `rsod-quiz/data/quizzes.db`).
   `config.local.php` is gitignored — keep any real secrets only there,
   never in `config.php`.
3. Make sure `rsod-quiz/data/` is writable by PHP (SQLite needs to
   create the `.db` file plus `-wal`/`-shm` siblings there):
   ```
   mkdir data && chmod 770 data
   ```
4. Create the Basic Auth password file protecting `results.php`:
   - **If your hosting panel has it** (most Polish shared-hosting panels
     do, usually under Security): use its "Password Protect Directory"
     feature on `rsod-quiz/` — it creates the password file and
     `.htaccess` rule for you. Skip the rest of this step.
   - **No SSH, no such panel feature**: upload `tools/gen-htpasswd.php`,
     open it once in the browser, fill in a username/password, copy the
     printed `user:{SHA}...` line into a new plain-text file named
     `passwd` created via File Manager (outside `rsod-quiz/` if your
     panel allows a path above the web root; otherwise inside it — the
     `.htaccess` here already denies direct requests for a file named
     `passwd`). **Delete `tools/gen-htpasswd.php` from the server
     immediately after** — it briefly handles a plaintext password and
     must not stay reachable.
   - **Over SSH** (if you ever get it): `htpasswd -c ~/.htpasswds/rsod-quiz/passwd sebastian`.

   Whichever way you made it, edit `.htaccess` and point `AuthUserFile`
   at that file's actual path on the server (ask your panel's File
   Manager for the full path, or check its "server path" / "document
   root" info page).
5. Test:
   ```
   curl -i -X OPTIONS https://sebastianzajac.pl/rsod-quiz/submit.php
   curl -i -X POST https://sebastianzajac.pl/rsod-quiz/submit.php \
     -H 'Content-Type: application/json' \
     -d '{"email":"test@wat.edu.pl","lecture":"wyklad2","answers":{"q1":"B","q2":"C","q3":"B","q4":"C","q5":"B"}}'
   ```
   Expect `{"ok":true,"score":9,"maxScore":9}`.
6. Open `https://sebastianzajac.pl/rsod-quiz/results.php` in a browser
   — it should prompt for the Basic Auth login, then list that test
   submission.

## Adding a new lecture's quiz

1. Add its answer key to `answer_keys` in `config.local.php` on the
   server (points must match the client-side quiz config exactly).
2. Add the matching quiz block to the lecture's `.qmd` page (see
   `assets/js/quiz-engine.js` and the example at the bottom of
   `lectures/wyklad2.qmd`).

## Known trade-off

The correct answers are necessarily visible in the lecture page's
JavaScript source (needed for instant client-side feedback), so a
student who reads devtools can find them. The server still recomputes
and stores the authoritative score independently, so `results.php`
cannot be spoofed by editing the score in the browser before
submitting — only the client-side celebratory number could, in
principle, be forged locally, which does not affect your records.
This is judged acceptable for a formative, ungraded quiz; flag it if
that assumption changes (e.g. if this ever counts toward a grade).
