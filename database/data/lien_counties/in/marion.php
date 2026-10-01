<?php

/*
 * Marion County, Indiana (Marion County Recorder, Indianapolis).
 *
 * Learned from a $1,050.24 painting lien recorded in September 2026 for $35
 * flat. The recorder's stamp landed in the top-right of the first-page space.
 * The statewide rules (the IC 36-2-11-15 affirmation and preparer statement,
 * the IC 36-2-11-16.5 margins) live in lien_documents/in.php; this file only
 * carries the county's office facts.
 */

return [
    'county' => 'Marion',
    'recording' => [
        'filing_office' => [
            'label' => 'Marion County Recorder',
            'method' => 'erecord',
        ],
        'fee_note' => '$35 flat per instrument (September 2026).',
    ],
    'notes' => [
        'The recorder\'s stamp goes in the top-right of the first-page space.',
        'Which e-recording vendor Marion County takes is not known yet; confirm it before submitting.',
    ],
];
