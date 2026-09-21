<?php
// Shared quiz backend config — one submit.php/results.php serve every
// course site (rsod2026, prba2026, ...) since they're all published
// under the same GitHub Pages origin. Not committed with real secrets —
// copy this file to config.local.php on the server and adjust;
// config.local.php is gitignored and, if present, wins over this file.

return [
    // Absolute filesystem path to the SQLite database file. Must be
    // writable by the PHP process (the containing directory too, since
    // SQLite needs to create -wal/-shm files next to it).
    'db_path' => __DIR__ . '/data/quizzes.db',

    // Exact origin allowed to call submit.php via fetch() (CORS). Must
    // match the GitHub Pages origin serving the course sites (no
    // trailing slash, no path) — the same for every *.github.io repo
    // under this account, so one value covers all courses.
    'allowed_origin' => 'https://sebkaz-teaching.github.io',

    // Whitelist of course -> lecture -> answer key, used to RECOMPUTE
    // the score server-side (never trust a score sent by the browser —
    // it can be edited in devtools before the request is sent). Points
    // must match each course's client-side quiz config.
    //
    // Each lecture key is the PAGE the quiz is embedded on, not the
    // material it tests: rsod2026's quiz at the top of wyklad2.qmd is a
    // recap of wyklad1, the one on wyklad3.qmd recaps wyklad2, and so on.
    'answer_keys' => [
        'rsod2026' => [
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

        'prba2026' => [
            // Recap of wyklad 1, shown at the top of wyklad2.qmd
            'wyklad2' => [
                'q1' => ['correct' => 'B', 'points' => 1],
                'q2' => ['correct' => 'B', 'points' => 1],
                'q3' => ['correct' => 'B', 'points' => 2],
                'q4' => ['correct' => 'C', 'points' => 2],
                'q5' => ['correct' => 'C', 'points' => 3],
            ],
            // Recap of wyklad 2, shown at the top of wyklad3.qmd
            'wyklad3' => [
                'q1' => ['correct' => 'B', 'points' => 1],
                'q2' => ['correct' => 'B', 'points' => 1],
                'q3' => ['correct' => 'B', 'points' => 2],
                'q4' => ['correct' => 'C', 'points' => 2],
                'q5' => ['correct' => 'C', 'points' => 3],
            ],
            // Recap of wyklad 3, shown at the top of wyklad4.qmd
            'wyklad4' => [
                'q1' => ['correct' => 'B', 'points' => 1],
                'q2' => ['correct' => 'B', 'points' => 1],
                'q3' => ['correct' => 'B', 'points' => 2],
                'q4' => ['correct' => 'C', 'points' => 2],
                'q5' => ['correct' => 'B', 'points' => 3],
            ],
            // Recap of wyklad 4, shown at the top of wyklad5.qmd
            'wyklad5' => [
                'q1' => ['correct' => 'B', 'points' => 1],
                'q2' => ['correct' => 'B', 'points' => 1],
                'q3' => ['correct' => 'B', 'points' => 2],
                'q4' => ['correct' => 'B', 'points' => 2],
                'q5' => ['correct' => 'B', 'points' => 3],
            ],
            // Recap of wyklad 5, shown at the top of wyklad6.qmd
            'wyklad6' => [
                'q1' => ['correct' => 'B', 'points' => 1],
                'q2' => ['correct' => 'B', 'points' => 1],
                'q3' => ['correct' => 'C', 'points' => 2],
                'q4' => ['correct' => 'B', 'points' => 2],
                'q5' => ['correct' => 'C', 'points' => 3],
            ],
            // Recap of wyklad 6, shown at the top of wyklad7.qmd
            'wyklad7' => [
                'q1' => ['correct' => 'B', 'points' => 1],
                'q2' => ['correct' => 'B', 'points' => 1],
                'q3' => ['correct' => 'B', 'points' => 2],
                'q4' => ['correct' => 'B', 'points' => 2],
                'q5' => ['correct' => 'B', 'points' => 3],
            ],
        ],

        'pas2026' => [
            // Recap of wyklad 1, shown at the top of wyklad2.qmd
            'wyklad2' => [
                'q1' => ['correct' => 'B', 'points' => 1],
                'q2' => ['correct' => 'B', 'points' => 1],
                'q3' => ['correct' => 'B', 'points' => 2],
                'q4' => ['correct' => 'B', 'points' => 2],
                'q5' => ['correct' => 'B', 'points' => 3],
            ],
        ],

        'wdplab2026' => [
            // Recap of sesja 1, shown at the top of sesja 2
            'sesja02' => [
                'q1' => ['correct' => 'B', 'points' => 1],
                'q2' => ['correct' => 'B', 'points' => 2],
            ],
            // Recap of sesja 2, shown at the top of sesja 3
            'sesja03' => [
                'q1' => ['correct' => 'B', 'points' => 1],
                'q2' => ['correct' => 'B', 'points' => 2],
            ],
            // Recap of sesja 3, shown at the top of sesja 4
            'sesja04' => [
                'q1' => ['correct' => 'B', 'points' => 1],
                'q2' => ['correct' => 'B', 'points' => 2],
            ],
        ],
    ],
];
