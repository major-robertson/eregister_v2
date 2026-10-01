<?php

/*
 * Queens County, New York (Queens County Clerk, Jamaica).
 *
 * Learned from two 2026 filings. July 2026 (a $4,500 lien by a contractor an
 * occupant hired, with the owner, a trustee, named separately, "fee simple" as
 * the owner's interest, and Block 1506 Lot 12): filed through the NYS Courts
 * Electronic Document Delivery System (EDDS), NYC County Clerk > Queens County
 * Clerk > "Personal/Property Judgments/Liens - Request Review", uploading the
 * notice of lien and the affidavit of service (sworn by staff in Kentucky,
 * mailed first-class and certified); the clerk reviewed the upload, then
 * emailed for payment ($30 + $5, plus a card fee). A4 phone scans were
 * accepted and NYSCEF registration was not needed. May 2026: mailed with the
 * filing cover sheet and a $35 money order to the Block Index room. The
 * clerk's page says liens must give the block and lot, must comply with N.Y.
 * Lien Law §§ 9 and 10, are notarized when filed, and that the office takes
 * only private-improvement liens.
 */

return [
    'county' => 'Queens',
    'recording' => [
        'filing_office' => [
            'label' => 'Queens County Clerk',
            'method' => 'erecord',
            'address_lines' => ['88-11 Sutphin Blvd', 'Jamaica, NY 11435'],
            'vendor' => 'NYS Courts EDDS',
        ],
        // EDDS takes an upload, so no mail-in cover sheet.
        'cover_sheet' => false,
        'fee_note' => '$30 for the notice of lien plus $5 for the affidavit of service, paid by card after the clerk reviews the upload (2026).',
    ],
    'notes' => [
        'File through EDDS: NYC County Clerk, then Queens County Clerk, then "Personal/Property Judgments/Liens - Request Review"; upload the notice of lien and the affidavit of service. The clerk reviews the upload and emails a payment request before filing.',
        'Upload the affidavit of service with the notice of lien. A4 phone scans were accepted in 2026, and no NYSCEF registration is needed.',
        'By mail instead: the filing cover sheet and a $35 money order to the Queens County Clerk, Block Index Room 106, 88-11 Sutphin Blvd, Jamaica, NY 11435.',
        'The Queens clerk requires the block and lot on every lien and takes only private-improvement liens.',
    ],
];
