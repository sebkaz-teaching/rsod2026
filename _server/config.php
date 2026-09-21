<?php
// Site-specific configuration. Not committed with real secrets — copy this
// file to config.local.php on the server and adjust; config.local.php is
// gitignored and, if present, wins over this file.

return [
    // Absolute filesystem path to the SQLite database file. Must be
    // writable by the PHP process (the containing directory too, since
    // SQLite needs to create -wal/-shm files next to it).
    'db_path' => __DIR__ . '/data/quizzes.db',

    // Exact origin allowed to call submit.php via fetch() (CORS). Must
    // match the GitHub Pages origin serving the course site (no trailing
    // slash, no path).
    'allowed_origin' => 'https://sebkaz-teaching.github.io',

    // Whitelist of lecture ids and their answer keys, used to RECOMPUTE
    // the score server-side (never trust a score sent by the browser —
    // it can be edited in devtools before the request is sent). Points
    // must match the client-side quiz config for each lecture.
    'answer_keys' => [
        'wyklad2' => [
            'q1' => ['correct' => 'B', 'points' => 1],
            'q2' => ['correct' => 'C', 'points' => 1],
            'q3' => ['correct' => 'B', 'points' => 2],
            'q4' => ['correct' => 'C', 'points' => 2],
            'q5' => ['correct' => 'B', 'points' => 3],
        ],
        // 'wyklad3' => [ ... ],  // add as each lecture's quiz goes live
    ],
];
