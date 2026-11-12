"""Exercise student registration and authenticated teacher review over HTTP."""
import base64
import http.cookiejar
import json
from pathlib import Path
import re
import shutil
import socket
import sqlite3
import subprocess
import tempfile
import time
import unittest
import urllib.error
import urllib.parse
import urllib.request

SOURCE = Path(__file__).resolve().parents[1]


class LabProgressTests(unittest.TestCase):
    def test_registration_review_and_existing_results(self):
        with tempfile.TemporaryDirectory(prefix='wdp-lab-') as directory:
            root = Path(directory)
            for name in ('db.php', 'schema.sql', 'results.php', 'lab-panel.php',
                         'lab-progress.php', 'submit.php'):
                shutil.copy(SOURCE / name, root / name)
            (root / 'config.php').write_text("<?php return ['db_path'=>__DIR__.'/test.db',"
                "'allowed_origin'=>'https://sebkaz-teaching.github.io','answer_keys'=>[]];")
            # Emulate Apache's existing results.php Basic Auth rule, including password validation.
            (root / 'router.php').write_text("""<?php
if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/results.php' &&
    (($_SERVER['PHP_AUTH_USER'] ?? '') !== 'teacher' || ($_SERVER['PHP_AUTH_PW'] ?? '') !== 'test-password')) {
    header('WWW-Authenticate: Basic realm="Test"'); http_response_code(401); exit;
}
return false;
""")
            with socket.socket() as sock:
                sock.bind(('127.0.0.1', 0))
                port = sock.getsockname()[1]
            server = subprocess.Popen(['php', '-S', f'127.0.0.1:{port}', '-t', directory,
                                       str(root / 'router.php')], stdout=subprocess.DEVNULL,
                                      stderr=subprocess.DEVNULL)
            try:
                opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
                def request(path, body=None, teacher=False, form=False, origin='https://sebkaz-teaching.github.io'):
                    headers = {'Origin': origin}
                    if teacher:
                        headers['Authorization'] = 'Basic ' + base64.b64encode(b'teacher:test-password').decode()
                    if body is not None:
                        data = (urllib.parse.urlencode(body).encode() if form else json.dumps(body).encode())
                        headers['Content-Type'] = 'application/x-www-form-urlencoded' if form else 'application/json'
                    else:
                        data = None
                    req = urllib.request.Request(f'http://127.0.0.1:{port}/' + path, data=data, headers=headers)
                    try:
                        with opener.open(req, timeout=5) as response:
                            return response.status, response.read().decode()
                    except urllib.error.HTTPError as response:
                        with response:
                            return response.code, response.read().decode()
                for _ in range(50):
                    try:
                        request('lab-progress.php')
                        break
                    except urllib.error.URLError:
                        time.sleep(.05)
                rsod = {'course': 'rsod2026', 'lab': 'lab01', 'action': 'confirm',
                        'email': 'Rsod@STUDENT.WAT.EDU.PL', 'group': '2'}
                self.assertEqual(request('lab-progress.php', {**rsod, 'group': '4'})[0], 422)
                self.assertEqual(request('lab-progress.php', {**rsod, 'email': 'bad@example.org'})[0], 422)
                self.assertEqual(request('lab-progress.php', rsod, origin='https://evil.example')[0], 403)
                self.assertEqual(request('lab-progress.php', rsod)[0], 200)
                with sqlite3.connect(root / 'test.db') as db:
                    original = db.execute('SELECT * FROM lab_confirmations').fetchone()
                    self.assertEqual(db.execute('SELECT count(*) FROM lab_tasks').fetchone()[0], 0)
                self.assertEqual(request('lab-progress.php', {**rsod, 'group': '1'})[0], 200)
                with sqlite3.connect(root / 'test.db') as db:
                    self.assertEqual(db.execute('SELECT * FROM lab_confirmations').fetchall(), [original])
                summary = 'results.php?view=labs&course=rsod2026'
                self.assertEqual(request(summary)[0], 401)
                code, page = request(summary, teacher=True)
                self.assertEqual(code, 200, page)
                self.assertIn('rsod@student.wat.edu.pl', page)
                self.assertNotIn('rsod@student.wat.edu.pl', request(summary + '&group=1', teacher=True)[1])
                self.assertIn('rsod@student.wat.edu.pl', request(summary + '&group=2', teacher=True)[1])
                self.assertNotIn('rsod@student.wat.edu.pl', request(summary + '&date=2000-01-01', teacher=True)[1])
                self.assertEqual(request(summary + '&date=2026-02-30', teacher=True)[0], 422)
                self.assertEqual(request(summary, rsod, teacher=True)[0], 405)
                token = 'a' * 64
                student = {'token': token, 'action': 'start', 'email': 'Student@WAT.EDU.PL'}
                code, text = request('lab-progress.php', student)
                self.assertEqual(code, 200, text)
                registration = json.loads(text)
                self.assertEqual(registration['email'], 'student@wat.edu.pl')
                date = registration['class_date']
                self.assertEqual(request('lab-progress.php', student)[0], 200)
                self.assertEqual(request('lab-progress.php', {**student, 'token': 'b'*64})[0], 409)
                self.assertEqual(request('lab-progress.php', {**student, 'action': 'approve'})[0], 422)
                self.assertEqual(request('lab-progress.php', {**student, 'action': 'status', 'token': 'b'*64})[0], 403)
                self.assertEqual(request('lab-progress.php', student, origin='https://evil.example')[0], 403)
                self.assertEqual(request('lab-progress.php', {**student, 'email': 'bad'})[0], 422)
                for task in (0, 16, '1'):
                    self.assertEqual(request('lab-progress.php', {**student, 'action': 'submit', 'task': task})[0], 422)
                submit = {**student, 'action': 'submit', 'task': 1, 'status': 'approved'}
                saved = json.loads(request('lab-progress.php', submit)[1])
                self.assertEqual(saved['tasks'][0]['status'], 'pending')
                path = 'results.php?view=labs&date=' + date
                self.assertEqual(request(path)[0], 401)
                self.assertEqual(request('lab-panel.php', teacher=True)[0], 404)
                code, panel = request(path, teacher=True)
                self.assertEqual(code, 200, panel)
                self.assertIn('student@wat.edu.pl', panel)
                csrf = re.search(r'name="csrf" value="([a-f0-9]+)"', panel)[1]
                with sqlite3.connect(root / 'test.db') as db:
                    participant = db.execute('SELECT id FROM lab_participants').fetchone()[0]
                    self.assertEqual(db.execute('SELECT count(*) FROM lab_participants').fetchone()[0], 1)
                review = {'csrf': csrf, 'participant': participant, 'task': 1, 'status': 'approved'}
                self.assertEqual(request(path, {**review, 'csrf': 'bad'}, teacher=True, form=True)[0], 403)
                self.assertEqual(request(path, review, teacher=True, form=True)[0], 200)
                approved = json.loads(request('lab-progress.php', {**student, 'action': 'status'})[1])
                self.assertEqual(approved['tasks'][0]['status'], 'approved')
                self.assertEqual(json.loads(request('lab-progress.php', submit)[1])['tasks'][0]['status'], 'approved')
                request(path, {**review, 'status': 'revision'}, teacher=True, form=True)
                self.assertEqual(json.loads(request('lab-progress.php', submit)[1])['tasks'][0]['status'], 'pending')
                request(path, review, teacher=True, form=True)
                finished = json.loads(request('lab-progress.php', {**student, 'action': 'finish'})[1])
                self.assertTrue(finished['finished_at'])
                self.assertEqual(json.loads(request('lab-progress.php', {**student, 'action': 'finish'})[1])['finished_at'], finished['finished_at'])
                self.assertEqual(request('lab-progress.php', student)[0], 200)
                with sqlite3.connect(root / 'test.db') as db:
                    self.assertEqual(db.execute('SELECT reviewed_by FROM lab_tasks').fetchone()[0], 'teacher')
                for index, address in enumerate(('user@student.wat.edu.pl', 'user@lab.student.wat.edu.pl')):
                    self.assertEqual(request('lab-progress.php', {**student, 'email': address,
                        'token': str(index + 1) * 64})[0], 200, address)
                for address in ('user@example.test', 'user@evilwat.edu.pl',
                                'user@wat.edu.pl.example.com', 'user@studentwat.edu.pl'):
                    code, rejected = request('lab-progress.php', {**student, 'email': address})
                    self.assertEqual(code, 422, address)
                    self.assertEqual(json.loads(rejected)['error'], 'email_domain_not_allowed')
                self.assertIn('Laboratoria', request('results.php', teacher=True)[1])
                self.assertIn('Prace opisowe', request('results.php?view=assignments', teacher=True)[1])
                self.assertEqual(request('submit.php', {'course':'wdplab2026', 'lecture':'cpp02',
                    'email':'quiz@example.test', 'answers':{'q1':'B'}})[0], 200)
            finally:
                server.terminate()
                server.wait(timeout=5)


if __name__ == '__main__':
    unittest.main()
