<?php

/*
 * Jackson County, Missouri (Recorder of Deeds, Kansas City / Independence).
 *
 * Learned from a 2026-07-18 e-recording rejection of a two-document batch:
 * "GRANTOR/GRANTEE NAMES MUST BE INDEXED AS DESIGNATED ON 1ST PG" and
 * "DOCUMENT DOES NOT CONTAIN A 3" VERTICAL SPACE; INCOMPLETE NOTARY
 * ACKNOWLEDGEMENT". A sequential batch fails as a whole when one document is
 * rejected. Recorded 2026-07-22 for $27 per three-page instrument ($21 for the
 * first page plus $3 per additional page). Missouri's first-page index block
 * (RSMo 59.310) is set statewide in lien_documents/mo.php; this file only
 * carries the county's office facts.
 */

return [
    'county' => 'Jackson',
    'recording' => [
        'filing_office' => [
            'label' => 'Jackson County Recorder of Deeds',
            'method' => 'erecord',
            'address_lines' => ['112 W Lexington Ave, Suite 30', 'Independence, MO 64050'],
            'vendor' => 'CSC (ep.erecording.com)',
        ],
        'index_block' => true,
        'index_roles' => ['grantor' => 'owner', 'grantee' => 'claimant'],
        // "Does not contain a 3" vertical space": nothing at all in the top
        // three inches, so the preparer block prints below the rule.
        'preparer_in_space' => false,
        'fee_note' => '$21 for the first page plus $3 per additional page (2026).',
    ],
    'notes' => [
        'A sequential e-recording batch is rejected as a whole when any document in it fails; submit the affidavit of service as its own document.',
        'The recorder wants the grantor (owner) and grantee (claimant) named on page 1 exactly as they are indexed.',
        'Circuit clerk question: RSMo § 429.080 says to file the lien with the clerk of the circuit court, here the 16th Judicial Circuit (liens on Kaw township property go to the clerk at Kansas City, § 478.483); the $21 clerk fee in the staff research note is not verified, and the court\'s civil fee schedule (16thcircuit.org/civil_fees, September 2026) lists only "Mechanic Lien $5.00" under Execution/Garnishment.',
    ],
];
