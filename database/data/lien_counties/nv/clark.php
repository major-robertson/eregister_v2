<?php

/*
 * Clark County, Nevada (County Recorder, Las Vegas).
 *
 * From the recorder's "Recordation Process and Requirements" and "Fees"
 * pages and its posted fee schedule (clarkcountynv.gov, read 2026-09-30):
 * the 11-digit parcel number at the top left corner of page 1, a blank
 * 3-by-3-inch space at the upper right of page 1, printed names under every
 * signature, a "Return Document to" name and address, liens listed among
 * the documents that must be notarized, and $42 per document. The recorder
 * offers a recording cover page that carries the APN, the title, "recording
 * requested by" and "return to" (NRS 111.312). No Nevada lien has been
 * filed through eRegister yet, so nothing here comes from a rejection.
 */

return [
    'county' => 'Clark',
    'recording' => [
        'filing_office' => [
            'label' => 'Clark County Recorder',
            'address_lines' => ['500 S. Grand Central Pkwy, 2nd Floor', 'Box 551510', 'Las Vegas, NV 89155-1510'],
        ],
        'fee_note' => '$42 per document (Clark County Recorder fee schedule, effective January 1, 2020, still posted in 2026).',
    ],
    'notes' => [
        'Clark County wants the 11-digit parcel number, as the Assessor numbers it, at the top left corner of page 1.',
        'Clark County lists liens among the documents that must be notarized before recording; the recorder\'s office has no notary.',
        'If a cover page is used, the document title on it must match page 1 exactly.',
    ],
];
