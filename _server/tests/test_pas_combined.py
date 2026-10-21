"""Verify the combined PAS quiz without touching the deployed database."""
import copy
import json
from pathlib import Path
import shutil
import socket
import sqlite3
import subprocess
import tempfile
import time
import unittest
import urllib.error
import urllib.request

SOURCE = Path(__file__).resolve().parents[1]
POINTS = [1, 1, 2, 2, 3]


def configuration():
    return {
        'allowed_origin': 'https://sebkaz-teaching.github.io',
        'answer_keys': {
            'pas2026': {
                part: {f'q{i + 1}': {'correct': 'B', 'points': points}
                       for i, points in enumerate(POINTS)}
                for part in ('spotkanie2', 'spotkanie3')
            },
            'prba2026': {'wyklad2': {'q1': {'correct': 'C', 'points': 2}}},
        },
    }


class CombinedQuizTests(unittest.TestCase):
    def setUp(self):
        self.tmp = tempfile.TemporaryDirectory(prefix='pas-combined-')
        self.addCleanup(self.tmp.cleanup)
        self.root = Path(self.tmp.name)
        for name in ('db.php', 'schema.sql', 'submit.php', 'results.php'):
            shutil.copy(SOURCE / name, self.root / name)
        (self.root / 'config.php').write_text('<?php return [];')
        (self.root / 'config.local.php').write_text(
            "<?php return json_decode(file_get_contents(__DIR__ . '/fixture.json'), true);"
        )
        self.config = configuration()
        self.save_config()

    def save_config(self):
        self.config['db_path'] = str(self.root / 'test.db')
        (self.root / 'fixture.json').write_text(json.dumps(self.config))

    def loaded_config(self):
        output = subprocess.check_output(
            ['php', '-r', "require 'db.php'; echo json_encode(rsod_config());"],
            cwd=self.root,
        )
        return json.loads(output)

    def test_register_key_preserving_other_settings(self):
        loaded = self.loaded_config()
        new = loaded['answer_keys']['pas2026'].pop('test-po-wykladach')
        self.assertEqual(loaded, self.config)
        self.assertEqual(list(new), [f'q{i}' for i in range(1, 11)])
        self.assertEqual(sum(q['points'] for q in new.values()), 18)
        self.config['answer_keys']['pas2026']['spotkanie3']['q5']['correct'] = 'D'
        self.save_config()
        self.assertEqual(self.loaded_config()['answer_keys']['pas2026']
                         ['test-po-wykladach']['q10']['correct'], 'B')
        self.assertEqual(self.loaded_config()['answer_keys']['pas2026']
                         ['spotkanie3']['q5']['correct'], 'D')

    def test_explicit_combined_key_wins(self):
        custom = {'q1': {'correct': 'A', 'points': 7}}
        self.config['answer_keys']['pas2026']['test-po-wykladach'] = custom
        self.save_config()
        self.assertEqual(self.loaded_config(), self.config)

    def test_older_deployments_receive_the_complete_new_key(self):
        original = copy.deepcopy(self.config)
        del self.config['answer_keys']['pas2026']['spotkanie3']['q5']
        self.save_config()
        loaded = self.loaded_config()
        key = loaded['answer_keys']['pas2026'].pop('test-po-wykladach')
        self.assertEqual(len(key), 10)
        self.assertEqual(loaded, self.config)
        self.config = original
        del self.config['answer_keys']['pas2026']
        self.save_config()
        loaded = self.loaded_config()
        key = loaded['answer_keys'].pop('pas2026')['test-po-wykladach']
        self.assertEqual(sum(question['points'] for question in key.values()), 18)
        self.assertEqual(loaded, self.config)

    def test_one_row_scoring_legacy_results_and_rejection(self):
        with socket.socket() as sock:
            sock.bind(('127.0.0.1', 0))
            port = sock.getsockname()[1]
        server = subprocess.Popen(
            ['php', '-S', f'127.0.0.1:{port}', '-t', str(self.root)],
            stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL,
        )
        def stop_server():
            server.terminate()
            server.wait(timeout=5)
        self.addCleanup(stop_server)
        base = f'http://127.0.0.1:{port}/'

        def request(path, body=None):
            data = None if body is None else json.dumps(body).encode()
            req = urllib.request.Request(base + path, data=data, headers={
                'Content-Type': 'application/json',
                'Origin': self.config['allowed_origin'],
            })
            try:
                with urllib.request.urlopen(req, timeout=5) as response:
                    return response.status, response.read().decode()
            except urllib.error.HTTPError as response:
                with response:
                    return response.code, response.read().decode()

        for _ in range(50):
            try:
                request('results.php')
                break
            except urllib.error.URLError:
                time.sleep(0.05)
        else:
            self.fail('Local PHP server did not start')

        legacy = {'course': 'prba2026', 'lecture': 'wyklad2',
                  'email': 'legacy@example.test', 'answers': {'q1': 'C'}}
        self.assertEqual(request('submit.php', legacy)[0], 200)
        full = {'course': 'pas2026', 'lecture': 'test-po-wykladach',
                'email': 'combined@example.test',
                'answers': {f'q{i}': 'B' for i in range(1, 11)}}
        code, output = request('submit.php', full)
        self.assertEqual(code, 200)
        self.assertEqual(json.loads(output), {'ok': True, 'score': 18, 'maxScore': 18})
        with sqlite3.connect(self.root / 'test.db') as db:
            rows = db.execute('SELECT course, lecture, score, max_score, answers '
                              'FROM submissions ORDER BY id').fetchall()
        self.assertEqual(len(rows), 2)
        self.assertEqual(rows[1][:4], ('pas2026', 'test-po-wykladach', 18, 18))
        self.assertEqual(len(json.loads(rows[1][4])), 10)
        code, view = request('results.php?course=pas2026&lecture=test-po-wykladach')
        self.assertEqual(code, 200)
        self.assertIn('combined@example.test', view)
        self.assertNotIn('legacy@example.test', view)
        self.assertIn('18 / 18', view)
        partial = copy.deepcopy(full)
        partial['answers']['q6'] = 'A'
        partial['answers']['q10'] = 'A'
        partial['score'] = 999
        self.assertEqual(json.loads(request('submit.php', partial)[1])['score'], 14)
        self.assertEqual(request('submit.php', {**full, 'email': 'invalid'})[0], 422)
        self.assertEqual(request('submit.php', {**full, 'lecture': 'unknown'})[0], 422)
        with sqlite3.connect(self.root / 'test.db') as db:
            self.assertEqual(db.execute('SELECT COUNT(*) FROM submissions').fetchone()[0], 3)


if __name__ == '__main__':
    unittest.main()
