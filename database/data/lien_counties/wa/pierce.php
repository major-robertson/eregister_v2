<?php

/*
 * Pierce County, Washington (Auditor's Office, Tacoma).
 *
 * Learned from the March 2026 True Light Electric claim of lien (an apartment
 * project in Tacoma), which the client hand-delivered to the Auditor, and
 * checked on piercecountywa.gov on 2026-09-30: the office address and hours,
 * the three contracted e-recording vendors (the vendor collects the fee), the
 * first-page requirements of RCW 65.04.045, "No attachments are permitted
 * (i.e. stapled/taped notary acknowledgement, legals, etc)", and the fee sheet
 * effective July 27, 2025 (standard fee $303.50 for the first page, $1.00 per
 * additional page; $50.00 more for a non-standard document).
 */

return [
    'county' => 'Pierce',
    'recording' => [
        'filing_office' => [
            'label' => 'Pierce County Auditor',
            'method' => 'erecord',
            'address_lines' => ['2401 S. 35th St., Room 200', 'Tacoma, WA 98409'],
            'vendor' => null,
        ],
        'fee_note' => '$303.50 for the first page and $1.00 per additional page; $50.00 more for a document that misses the margin or type rules (fee sheet effective July 27, 2025).',
    ],
    'notes' => [
        'E-recording goes through the vendors under contract with the Auditor (CSC Erecording Solutions, eRecording Partners Network or Simplifile); the vendor collects the recording fee.',
        'Paper originals can be recorded in person (Monday to Friday, 8:30 a.m. to noon and 1:00 to 4:30 p.m.; an appointment is highly recommended) or by mail with a check payable to Pierce County Auditor.',
        'Nothing may be attached to the pages, not even a stapled notary certificate or legal description: use the certificate printed on the claim.',
    ],
    // How documents are signed here (EREG-142): only what this file already
    // states; null is unknown, and a known value wins over the state's.
    'signature_requirements' => [
        'esign_accepted' => null,
        'notary_required' => null,
        'wet_ink_original_required' => false,
        'ron_accepted' => null,
        'source' => 'E-recording goes through the vendors under contract with the Auditor (notes).',
    ],
    // The fee to record the lien or release here (EREG-143), in cents: one
    // value for both, or keyed by kind. Only a flat fee this file states; null
    // is unknown. Unknown: $303.50 for the first page plus $1.00 per additional page (fee_note).
    'recording_fee_cents' => null,
];
