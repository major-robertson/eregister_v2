<?php

/*
 * Clinton County, Illinois (County Clerk & Recorder, Carlyle).
 *
 * From the county's own pages on 2026-09-30 (clintonco.illinois.gov): the
 * County Clerk & Recorder page (office and mailing address), the Recording
 * Fee Schedule effective February 1, 2025 ("Liens and Releases" are a
 * Standard Land Document at $70.00; a Non-Standard Land Document is $85.00)
 * and the "Recording Reminders/Requirements" sheet: a clear space in the top
 * right corner "a minimum for 3" wide by 4" high" (otherwise the county adds a
 * cover page for $1), the preparer's and the return-to names and addresses,
 * a notarized signature, "NO blank lines", the legal description, parcel
 * number and address on every land document, and the associated document
 * number on any release. CSC's Illinois availability page lists Clinton
 * County in its e-recording network. The archive's May 2026 claim for lien
 * was recorded here; its cover letter called the office the "Recorder of
 * Deeds".
 */

return [
    'county' => 'Clinton',
    'recording' => [
        'filing_office' => [
            'label' => 'Clinton County Clerk and Recorder',
            'method' => 'erecord',
            'address_lines' => ['850 Fairfax Street', 'Carlyle, IL 62231'],
            'vendor' => 'CSC',
        ],
        // "A minimum for 3" wide by 4" high" in the top right corner: page 1 stays blank
        // across its top four inches.
        'top_margin_in' => 4.0,
        'fee_note' => '$70 per lien or release as a standard land document; $85 if non-standard (effective February 1, 2025).',
    ],
    'notes' => [
        'Clinton County rejects documents with blank lines: before recording, fill in every line or line through any that does not apply, including the notary\'s identification line.',
        'Clinton County wants the legal description, the PIN and the property address on every land document, and a release must show the document number of the claim for lien it releases.',
    ],
];
