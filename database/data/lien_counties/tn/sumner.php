<?php

/*
 * Sumner County, Tennessee (Register of Deeds, Gallatin).
 *
 * Learned from a June 2026 prime contractor's notice of lien ($2,112): mailed
 * to the register with a check for $5 per page plus the $2 data processing fee
 * (Tenn. Code Ann. § 8-21-1001(b), (c)), recorded, then a copy mailed to the
 * owner with a notice-of-recording letter. The client had notarized online, so
 * staff attached a notarized certificate of authenticity (§ 66-24-101(d)(3))
 * and the register recorded the copy; the original went back to whoever paid.
 * The register's own pages (deeds.sumnercounty.org, read 2026-09-30): $12 for
 * up to two pages and $5 for each additional page; every document needs the
 * preparer's name and address, signatures, a "complete notary acknowledgement"
 * and a dated, signed check with a phone number on it; a lien must name the
 * lienor and the owner and state the amount; a release cites the book and page
 * it releases; the recorded original is mailed back only in a self-addressed
 * stamped envelope; e-recording through Simplifile, CSC and ePN.
 */

return [
    'county' => 'Sumner',
    'recording' => [
        'filing_office' => [
            'label' => 'Sumner County Register of Deeds',
            'method' => 'mail',
            'address_lines' => ['355 N. Belvedere Dr., Suite 201', 'Gallatin, TN 37066'],
        ],
        'fee_note' => '$12 for up to two pages plus $5 for each additional page (2026).',
    ],
    'notes' => [
        'Mail the original instrument with a check payable to the Sumner County Register of Deeds; the check must be dated and signed and show a phone number.',
        'The register mails the recorded original back only in an enclosed self-addressed stamped envelope; in June 2026 it went back to whoever paid.',
        'An instrument notarized online goes in with the certificate of authenticity page; the register recorded the June 2026 notice of lien that way.',
        'Sumner County also e-records through Simplifile, CSC and ePN; an e-recorded scan needs the certificate of authenticity page too.',
    ],
];
