<?php

/*
 * Utah County, Utah (County Recorder, Provo).
 *
 * Learned from a July 2026 notice of construction lien on an industrial
 * parcel in Provo, recorded for $40 flat and mailed to the owner with a proof
 * of service. The recorder's pages (recorder.utahcounty.gov, read 2026-09-30)
 * list the Utah Code § 17-71-402(4) paper rules, including the 2-1/2" by
 * 4-1/2" space in the upper right corner of page 1 that lien_documents/ut.php
 * keeps clear, take documents in person, by mail or through an independent
 * third-party e-recording vendor (none named), and keep the $40 fee schedule.
 * The county's land records call the parcel number a "serial number" and
 * index recorded documents by entry number.
 */

return [
    'county' => 'Utah',
    'recording' => [
        'filing_office' => [
            'label' => 'Utah County Recorder',
            'method' => 'erecord',
            'address_lines' => ['100 East Center St, Suite 1300', 'Provo, UT 84606'],
        ],
        'fee_note' => '$40 per document, any number of pages, plus $2 for each description over ten (2026).',
    ],
    'notes' => [
        'Recorded for $40 flat in July 2026; the recorder\'s fee page says the $40 schedule stays in effect.',
        'The county calls the parcel number a serial number and finds recorded documents by entry number.',
        'E-recording goes through an independent third-party vendor; the recorder also takes documents in person or by mail.',
    ],
];
