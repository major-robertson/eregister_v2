<?php

/*
 * Butler County, Pennsylvania: the Prothonotary of the Court of Common Pleas
 * keeps the mechanics' lien docket. The archive's Butler County samples carry
 * the same docket caption as the generated claim. Office, mailing address,
 * payment methods and the $27.00 claim fee checked on butlercountypa.gov (the
 * Prothonotary page and the fee sheet effective January 6, 2026) on 2026-09-30.
 */

return [
    'county' => 'Butler',
    'recording' => [
        'filing_office' => [
            'label' => 'Butler County Prothonotary',
            'method' => 'mail',
            'address_lines' => ['P.O. Box 1208', 'Butler, PA 16003-1208'],
            'vendor' => null,
        ],
        'fee_note' => '$27.00 to file a mechanics\' lien claim (fee sheet effective January 6, 2026).',
    ],
    'notes' => [
        'The office is on the first floor of the Government Center, 124 W. Diamond St., Butler, PA 16001; mail filings go to the P.O. box.',
        'The Prothonotary takes cash, money orders, business checks and credit cards (2.5% fee), not personal checks.',
        'The archive\'s Butler County samples use the same docket caption as the generated claim (court, Claimant v. Owner, number and year).',
    ],
];
