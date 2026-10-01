<?php

/*
 * Multnomah County, Oregon (Portland): the county clerk's recording office
 * (Division of Assessment, Recording and Taxation).
 *
 * Learned from a 2026 claim of lien by an original contractor for a labor-only
 * remodel. The first e-recording submission was rejected for an "incomplete
 * notary acknowledgement" (the draft's jurat did not name the person who
 * signed, which ORS 194.280(1)(d) requires) and for its first page (the amount
 * started on page 2). The second signed copy had to be brightened and resized
 * because the county rejected an oversize phone scan. The claim was then
 * recorded for $86 (three pages). The county's pages (multco.us, fetched
 * 2026-09-30) list e-recording through six vendors, a 4" x 2" space for the
 * recording label on page 1 (upper right preferred) and, for liens, $76 for
 * the first page and $5 for each additional page. The state file keeps the
 * statewide rules; this file carries the county's office facts.
 */

return [
    'county' => 'Multnomah',
    'recording' => [
        'filing_office' => [
            'label' => 'Multnomah County Clerk',
            'method' => 'erecord',
            'address_lines' => ['501 SE Hawthorne Blvd, Suite 175', 'Portland, OR 97214'],
        ],
        'fee_note' => '$76 for the first page and $5 for each additional page; a three-page claim of lien cost $86 (2026).',
    ],
    'notes' => [
        'Multnomah rejected a 2026 claim of lien for an "incomplete notary acknowledgement" (the draft\'s jurat did not name the person who signed, which ORS 194.280(1)(d) requires), because its amount started on page 2, and for an oversize phone scan of the signed copy.',
        'The county e-records through CSC, Simplifile, eRecording Partners Network, Indecomm, Hopdox and ValueCheck. Mail goes to Multnomah County Recorder, PO Box 5007, Portland, OR 97208-5007, with a check payable to Multnomah County Recorder.',
    ],
];
