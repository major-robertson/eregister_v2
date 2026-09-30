<?php

/*
 * Johnson County, Kansas (Register of Deeds, County Administration Building,
 * Olathe; run by the county's Treasury, Taxation and Vehicles department).
 *
 * Learned from the June 2026 filings: Kansas City Bath Remodelers v. Gear, an
 * original contractor's $6,000 lien on a bathroom remodel, was e-recorded with
 * the Register of Deeds and came back as Record Book 202606 Page 007342, $17
 * for five pages, with page 1's top three inches left clear. The TDR
 * Electrical (Olathe) drafts of June 11 and 15 carried the submitter legend
 * below, verbatim. E-recording needs the county's e-recording Memorandum of
 * Understanding and one of its four approved vendors. The county's document
 * requirements (jocogov.org, read 2026-09-30) ask for a 3" top margin on
 * page 1 and 1" margins elsewhere, letter-size paper, a legal description,
 * names printed under every signature and a complete notary certificate; the
 * statewide defaults already meet them. Whether this office or the clerk of
 * the district court perfects a Kansas lien is open (see lien_documents/ks.php).
 */

return [
    'county' => 'Johnson',
    'recording' => [
        'filing_office' => [
            'label' => 'Johnson County Register of Deeds',
            'method' => 'erecord',
            'address_lines' => ['111 S. Cherry St., Ste 1200', 'Olathe, KS 66061'],
            'vendor' => null,
        ],
        // The e-recording MOU legend, printed under the recorder rule.
        'legend' => 'Submitted electronically by eRegister in compliance with Kansas statutes governing recordable documents and the terms of the Memorandum of Understanding with the Johnson County Register of Deeds. K.S.A. 28-115.',
        // Nothing in the top three inches of page 1: the preparer block prints below the rule.
        'preparer_in_space' => false,
        'fee_note' => '$17 for a five-page instrument e-recorded (June 2026).',
    ],
    'notes' => [
        'Johnson County filings are set to e-record with the Register of Deeds, as the June 2026 lien was; K.S.A. 60-1102 names the clerk of the district court, so switch this county back if counsel says that office perfects the lien.',
        'The June 2026 lien printed the claimant\'s Johnson County contractor license number with its issue and expiry dates; put all three in the license number field of Document details.',
        'The June 2026 liens gave the 19-digit Kansas Uniform Parcel Number; enter it as the project\'s parcel ID.',
        'The county fee schedule (2026) lists $21 for the first page and $17 for each added page of a recorded instrument, and $17 flat for liens for materials and services under K.S.A. 58-201.',
    ],
];
