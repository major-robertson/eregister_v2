<?php

/*
 * Boulder County, Colorado (Clerk and Recorder, Recording Division).
 *
 * From bouldercounty.gov ("Recording Documents" and "Recording Fees and
 * Requirements", read 2026-09-30): eRecording through Simplifile, eRecording
 * Partners, CSC and Indecomm (vendor fees apply); paper by mail or 24-hour
 * drop box; office and mailing address 1750 33rd St., Suite 201, Boulder, CO
 * 80301; $43 per document from July 1, 2025; checks payable to "Boulder
 * County Clerk & Recorder" (a check to the wrong payee returns the whole
 * package); the Recording Division is open Monday to Thursday, 7:30 a.m. to
 * 5 p.m., and closed Fridays. An April 2026 notice of intent in the archive
 * named the Boulder County Clerk and Recorder.
 */

return [
    'county' => 'Boulder',
    'recording' => [
        'filing_office' => [
            'label' => 'Boulder County Clerk and Recorder',
            'method' => 'erecord',
            'address_lines' => ['Recording Division, 1750 33rd St., Suite 201', 'Boulder, CO 80301'],
        ],
    ],
    'notes' => [
        'Boulder takes e-recording through Simplifile, eRecording Partners, CSC and Indecomm; paper goes by mail or a 24-hour drop box (bouldercounty.gov, 2026).',
        'The Recording Division is open Monday to Thursday and closed Fridays; do not leave a Boulder deadline to a Friday.',
        'Paper filings need a check payable to "Boulder County Clerk & Recorder"; a check to the wrong payee sends the whole package back.',
    ],
];
