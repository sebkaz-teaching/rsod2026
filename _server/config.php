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
    //
    // Each key is the PAGE the quiz is embedded on, not the material it
    // tests: the quiz at the top of wyklad2.qmd is a recap of wyklad1,
    // the one on wyklad3.qmd recaps wyklad2, and so on.
    'answer_keys' => [
        // Recap of wyklad 1, shown at the top of wyklad2.qmd
        'wyklad2' => [
            'q1' => ['correct' => 'C', 'points' => 1],
            'q2' => ['correct' => 'B', 'points' => 1],
            'q3' => ['correct' => 'C', 'points' => 2],
            'q4' => ['correct' => 'C', 'points' => 2],
            'q5' => ['correct' => 'B', 'points' => 3],
        ],
        // Recap of wyklad 2, shown at the top of wyklad3.qmd
        'wyklad3' => [
            'q1' => ['correct' => 'B', 'points' => 1],
            'q2' => ['correct' => 'C', 'points' => 1],
            'q3' => ['correct' => 'B', 'points' => 2],
            'q4' => ['correct' => 'C', 'points' => 2],
            'q5' => ['correct' => 'B', 'points' => 3],
        ],
        // Recap of wyklad 3, shown at the top of wyklad4.qmd
        'wyklad4' => [
            'q1' => ['correct' => 'B', 'points' => 1],
            'q2' => ['correct' => 'B', 'points' => 1],
            'q3' => ['correct' => 'C', 'points' => 2],
            'q4' => ['correct' => 'B', 'points' => 2],
            'q5' => ['correct' => 'C', 'points' => 3],
        ],
        // Recap of wyklad 4, shown at the top of wyklad5.qmd
        'wyklad5' => [
            'q1' => ['correct' => 'B', 'points' => 1],
            'q2' => ['correct' => 'B', 'points' => 1],
            'q3' => ['correct' => 'B', 'points' => 2],
            'q4' => ['correct' => 'B', 'points' => 2],
            'q5' => ['correct' => 'B', 'points' => 3],
        ],
    ],
];
