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

## PAS combined post-lecture quiz

The PAS page `test-po-wykladach.html` sends one submission with
`course=pas2026`, `lecture=test-po-wykladach` and ten answers (`q1` through
`q10`). Deploy the updated `db.php` into the existing `rsod-quiz/` directory.
Keep `config.local.php`, `submit.php`, `results.php`, `.htaccess` and the database.
No schema migration is needed.

The configuration loader registers a complete built-in key for the ten questions,
including on older deployments without any PAS keys. Questions 1-5 come from the
former lecture-two quiz; questions 6-10 come from the former lecture-three quiz.
The maximum score is 18. Stable correct option IDs are B; displayed letters are
shuffled by the client. An explicitly configured `test-po-wykladach` key takes
precedence. Existing course keys, settings and historical submissions are unchanged.

Use the existing protected viewer with
`results.php?course=pas2026&lecture=test-po-wykladach`. One successful submission
creates one row. The existing endpoint does not deduplicate quiz retries or limit
attempts per email; this change does not introduce either guarantee.

Run `python3 _server/tests/test_pas_combined.py` to verify key registration,
configuration precedence, legacy compatibility, HTTP scoring and the filtered
viewer using temporary files and a local PHP server on an ephemeral port.
Until `db.php` is deployed, the public test may report that it is not activated;
publishing the static page alone does not activate the server-side key.


## WDP laboratory attendance and task review

Deploy `lab-progress.php`, `lab-panel.php`, `results.php` and `schema.sql` to the
existing `/rsod-quiz/` directory. Keep the production `config.local.php`, database,
`db.php`, `.htaccess` and password file. Back up the live SQLite database before
uploading. The schema adds two tables on first access; it preserves all quiz and
assignment records. No new dependency or URL prefix is required.

Publish the updated WDP handout and `assets/js/lab-progress.js` together. The
handout uses `https://sebastianzajac.pl/rsod-quiz/lab-progress.php`.
Open `results.php?view=labs` with the existing instructor Basic Auth account.
The production server **must enforce the existing `.htaccess` protection of
`results.php`**, including password verification; PHP only consumes the identity
provided by that authentication. Direct requests to `lab-panel.php` return 404.
Teacher writes additionally require a session-bound CSRF token. Separate server
Basic Auth accounts allow the panel to record individual reviewers; a shared
account records the shared login.

A student's explicit start registers their self-declared email immediately.
The roster is filtered by class date in Europe/Warsaw and refreshes every 30 seconds
unless a review form is open/focused. Registration is not proof of physical
attendance. Emails are not verified. Each email has one registration per lab/day.
A random access token is saved in the student's tab sessionStorage; only its SHA-256
hash is stored in SQLite. Reloading that tab restores access, and retrying a start
with the same token does not create another participant. Clearing sessionStorage,
closing the tab, or switching browser can lose access; another token for the same
email/day is rejected rather than silently replacing an existing registration.
Do not share a student's browser tab. The server stores every task submission and
teacher review immediately; finishing is an idempotent summary marker, not a lock
on subsequent corrections. No source files are uploaded.

Teacher flow: find the student, inspect their program, open the submitted task's
**Sprawdź** control, then choose **Zatwierdź** or **Do poprawy**. Existing reviews can
be corrected through **Zmień status**. Students can resubmit tasks returned for
revision; they cannot grant or overwrite approvals. Review time and authenticated
reviewer are saved with the current task status.

Verify locally with `python3 _server/tests/test_lab_progress.py`. For deployment,
check an actual student start from the published WDP site, reload the tab, submit
a task, approve it in the protected panel, and verify that the student sees the
approval. Confirm anonymous access to `results.php?view=labs` is denied. Local PHP
HTTP tests emulate the web-server authentication rule; they do not prove the
hosting configuration.


## RSOD whole-laboratory confirmations

Publish `laboratorium1.qmd` and `assets/js/lab-confirmation.js` through Quarto.
Deploy the updated `lab-progress.php`, `lab-panel.php`, `schema.sql` and
`results.php` to the existing `/rsod-quiz/` hosting directory. Preserve production
configuration, authentication and the database; back up the live database first.
GitHub Pages publishes static files only and does not deploy this PHP backend.
The additive `lab_confirmations` table leaves WDP registrations and reviews intact.

Students enter a WAT email and group 1, 2 or 3 after oral approval. One record per
course/lab/email stores the original group, Warsaw date and server timestamp;
retries preserve it across days. No review queue or per-task approval is created.
This is a student declaration, not an authenticated teacher approval. Emails are
not verified. A mistyped identity/group needs instructor intervention; students
cannot overwrite an existing record. No roster is exposed by the public endpoint.

Open `results.php?view=labs&course=rsod2026` with existing instructor Basic Auth.
The default summary includes all dates/groups; filters select a date or group.
Verify the live endpoint from the published handout and confirm unauthenticated
summary access is denied. A Git push alone does not activate confirmations.
