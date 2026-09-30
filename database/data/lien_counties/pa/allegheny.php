<?php

/*
 * Allegheny County, Pennsylvania (Pittsburgh): the Department of Court Records,
 * Civil/Family Division, keeps the civil records of the Court of Common Pleas
 * of Allegheny County and takes mechanics' lien claims.
 *
 * Learned from Revelation Plumbing v. Balch (July 2026, $4,712.34): the
 * county's e-filing desk said a mechanics' lien claim without an attorney may
 * be e-filed pro se through the Department of Court Records portal, with a
 * "received" email and then an "accepted and processed" email carrying the
 * docket number, and that the affidavit of service is filed after the claim.
 * Office name, address, e-filing rules (mandatory only for attorneys since
 * November 13, 2023) and the $102.75 claim fee checked on dcr.alleghenycounty.us
 * and alleghenycounty.us on 2026-09-30.
 */

return [
    'county' => 'Allegheny',
    'recording' => [
        'filing_office' => [
            'label' => 'Allegheny County Department of Court Records, Civil/Family Division',
            'method' => 'erecord',
            'address_lines' => ['City-County Building, First Floor', '414 Grant Street', 'Pittsburgh, PA 15219-2469'],
            'vendor' => 'the county e-filing portal',
        ],
        'fee_note' => '$102.75 to file a mechanics\' lien claim (county fee schedule, 2026).',
    ],
    'notes' => [
        'E-file pro se through the Department of Court Records portal (dcr.alleghenycounty.us): register an account, upload the claim, then wait for two emails, "received" and "accepted and processed" with the docket number. No one has to appear in person. Paper filing in person or by mail is also accepted; e-filing is mandatory only for attorneys.',
        'After the notice of filing is served, file the affidavit of service in the same case within 20 days after service.',
        'The archive\'s Allegheny formatting list (black type, no handwriting except signatures, 8.5 x 11 pages printed on one side, every page numbered, 10-point type or larger, 20-pound white paper, no tape, staples or correction fluid, full names and addresses for every party) mixes the Department of Real Estate\'s deed rules with another county\'s lien instructions; the generated claim meets all of it with 1-inch margins.',
    ],
];
