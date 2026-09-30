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

## PRBA open-ended coursework (hosting file manager)

Upload these three files directly into the existing `rsod-quiz/` directory:

- `assignment-submit.php` (new write endpoint)
- `schema.sql` (adds `assignment_submissions` with `CREATE TABLE IF NOT EXISTS`)
- `results.php` (adds the **Prace opisowe** tab and preserves quiz viewing)

Keep the existing `config.local.php`, `db.php`, `.htaccess`, password file and
`data/` directory. No configuration edit, shell command or manual migration is
needed: opening the protected results page creates the new table in the configured
SQLite database. Existing quiz rows and scores are preserved. Back up the database
using the hosting panel before deployment; do not replace it with an empty file.

Open `results.php?view=assignments&course=prba2026&lecture=wyklad1` using your
existing Basic Auth login. The same protected PHP page displays authors, emails,
server timestamps and escaped text responses. There is no public read endpoint.
Submissions are not automatically graded. Changed submissions produce new entries;
retrying the same request ID and content returns the original receipt. As with the
quizzes, student emails are self-declared, not verified identities.

Deploy PHP before asking students to use the new form on the PRBA site. Until the
endpoint is installed, the form will show a save failure and retain the entered
text. Students can download a plain-text copy; drafts do not survive page reloads.
Use synthetic data for a manual smoke test, verify the receipt in the protected
viewer, and avoid changing existing quiz configuration.

### Local regression check

Run `python3 _server/tests/test_assignments.py` from the repository root with PHP
CLI and PDO SQLite installed. The test starts a temporary localhost PHP server
on port 18763, creates a disposable database and sends synthetic HTTP requests.
It never connects to the production endpoint.
