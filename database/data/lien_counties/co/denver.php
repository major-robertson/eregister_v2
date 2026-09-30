<?php

/*
 * Denver (City and County of Denver, Office of the Clerk and Recorder,
 * Recording Division).
 *
 * From denvergov.org ("Record Documents", read 2026-09-30): eRecording is
 * encouraged and takes minutes, while paper sent by mail takes a week to 10
 * days; Denver contracts with four eRecording submitters (eRecording Partners
 * Network, Indecomm, CSC, Simplifile); $43 per document; paper goes to Clerk
 * & Recorder Recording Dept., 200 W. 14th Ave., Denver, CO 80204, with checks
 * payable to "Manager of Finance"; margins per C.R.S. § 30-10-406(3)(a).
 * Denver cannot reject or refund a document meant for another county. An
 * August 2026 notice of intent in the archive named the Denver County Clerk
 * and Recorder as the filing office.
 */

return [
    'county' => 'Denver',
    'recording' => [
        'filing_office' => [
            'label' => 'Denver Clerk and Recorder',
            'method' => 'erecord',
            'address_lines' => ['Recording Dept., 200 W. 14th Ave.', 'Denver, CO 80204'],
        ],
    ],
    'notes' => [
        'Denver takes e-recording through eRecording Partners Network, Indecomm, CSC and Simplifile; paper sent by mail takes a week to 10 days (denvergov.org, 2026).',
        'Paper filings go to Clerk & Recorder Recording Dept., 200 W. 14th Ave., Denver, CO 80204, with a check payable to "Manager of Finance".',
        'Denver cannot reject or refund a document sent to the wrong county; confirm the county before submitting.',
    ],
];
