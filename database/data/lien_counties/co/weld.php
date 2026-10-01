<?php

/*
 * Weld County, Colorado (Clerk and Recorder, Recording Department, Greeley).
 *
 * From weld.gov ("Recording a Document", "Recording Department Fees" and the
 * Recording contact page, read 2026-09-30): eRecording through three vendors
 * (Simplifile, eRecording Partners, CSC); in person or by courier at 1250 H
 * Street, Greeley, CO 80631; by mail to Clerk & Recorder Recording
 * Department, PO Box 459, Greeley, CO 80632 (the contact page lists PO Box
 * 758 instead); $43 for a letter or legal size document regardless of the
 * number of pages. Weld's page reads C.R.S. § 30-10-406(3)(a) as a 1-inch top
 * and bottom margin. The archive's July 2026 self-serve statement was a Weld
 * County filing (see lien_documents/co.php on its verification).
 */

return [
    'county' => 'Weld',
    'recording' => [
        'filing_office' => [
            'label' => 'Weld County Clerk and Recorder',
            'method' => 'erecord',
            'address_lines' => ['Recording Department, 1250 H Street', 'Greeley, CO 80631'],
        ],
    ],
    'notes' => [
        'Weld takes e-recording through Simplifile, eRecording Partners and CSC (weld.gov, 2026).',
        'Paper filings by mail go to Clerk & Recorder Recording Department, PO Box 459, Greeley, CO 80632; the county contact page lists PO Box 758, so confirm with the Recording Department (970-304-6530) before mailing.',
        'Weld asks for a 1-inch top and bottom margin; these documents have 1-inch margins all around.',
    ],
];
