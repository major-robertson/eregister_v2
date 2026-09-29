<?php

/*
 * Texas: Property Code ch. 53 (Mechanic's, Contractor's, or Materialman's
 * Lien), as amended by H.B. 2237 effective 2022-01-01.
 *
 * Affidavit claiming a lien: § 53.054(a) (signed by the claimant or on the
 * claimant's behalf; contents (1)-(8): a sworn statement of the amount, the
 * owner's name and last known address, the kind of work and, for a claimant
 * other than an original contractor, "a statement of each month in which the
 * work was done and materials furnished for which payment is requested", the
 * hiring party, the original contractor, a legally sufficient property
 * description, the claimant's addresses, and for a derivative claimant "a
 * statement identifying the date each notice of the claim was sent to the
 * owner and the method by which the notice was sent"). File with the county
 * clerk by the 15th day of the fourth month (residential: third month) after
 * the month the claimant's work was completed (§ 53.052). Copy to the owner
 * (and the original contractor when the claimant is not one) within five days
 * of filing (§ 53.055). Derivative claimants' notice of claim for unpaid labor
 * or materials: § 53.056 (to the owner and the original contractor by the 15th
 * day of the third month (residential: second month) after the month of the
 * work; the form in (a-2)). Release: § 53.152 (within 10 days of a written
 * request once paid). Statutory text verified against texas.public.law
 * (current through the 88th Legislature) on 2026-09-29.
 */

return [
    'state' => 'TX',
    'state_name' => 'Texas',
    'recording' => [
        'filing_office' => ['label' => 'County Clerk, Official Public Records', 'method' => 'erecord', 'vendor' => 'CSC'],
        'parcel_label' => 'Property ID',
        'fee_note' => '$29-$37 per instrument through CSC; most clerks add their own cover or back page (2026).',
        'notes' => [
            'File by the 15th day of the fourth month (residential: third month) after the month the claimant\'s work was completed, terminated or abandoned (Tex. Prop. Code § 53.052).',
            'Send a copy of the filed affidavit to the owner, and to the original contractor when the claimant is not the original contractor, within five days after filing (Tex. Prop. Code § 53.055).',
        ],
    ],
    'execution' => [
        'verification' => 'sworn',
        'notary' => true,
        'notary_form' => 'jurat',
    ],
    'service' => [
        'recipients' => ['owner', 'gc'],
        'days_after' => 5,
        'method' => 'certified_mail',
    ],
    'kinds' => [
        'mechanics_lien' => [
            'title' => 'Affidavit Claiming a Mechanic\'s Lien',
            'statute' => 'Tex. Prop. Code § 53.054',
            'sections' => [
                'amount' => 'breakdown',
                'retainage' => true,
                'months_of_work' => true,
                'prior_notice' => true,
                'gc' => true,
            ],
            'clauses' => [
                'affirmations' => [
                    'The amount stated above is the amount of the claimant\'s claim, sworn to as provided by Tex. Prop. Code § 53.054(a)(1).',
                ],
            ],
            'attachments' => [
                'Optional: a copy of the written agreement or contract and a copy of each notice sent to the owner (Tex. Prop. Code § 53.054(b)).',
            ],
            'notes' => [
                'For a claimant other than the original contractor, the affidavit lists each month of work for which payment is requested and the date and method of each notice of claim sent to the owner (Tex. Prop. Code § 53.054(a)(3), (a)(8)); both come from Document details.',
            ],
        ],
        'lien_release' => [
            'title' => 'Release of Lien',
            'statute' => 'Tex. Prop. Code § 53.152',
            'execution' => [
                'verification' => 'acknowledged',
                'notary' => true,
                'notary_form' => 'acknowledgment',
            ],
            'clauses' => [
                'affirmations' => [
                    'The claim secured by the affidavit claiming a mechanic\'s lien described above has been paid, and the claimant releases the lien and the affidavit of record.',
                ],
            ],
            'notes' => [
                'A claimant must furnish a release within 10 days after a written request once the claim is paid (Tex. Prop. Code § 53.152).',
            ],
        ],
        'prelim_notice' => [
            'title' => 'Notice of Claim for Unpaid Labor or Materials',
            'statute' => 'Tex. Prop. Code § 53.056',
            'body' => 'documents.lien.letters.bodies.tx-notice-of-claim',
            'template_version' => 1,
            'sections' => [
                'amount' => 'single',
                'months_of_work' => true,
                'gc' => true,
                'first_furnish' => false,
                'last_furnish' => false,
            ],
            'service' => [
                'recipients' => ['owner', 'gc'],
                'days_after' => null,
                'method' => 'certified_mail',
            ],
            'notes' => [
                'Sent by a claimant other than the original contractor to the owner and the original contractor, not later than the 15th day of the third month (residential: second month) after each month in which the labor or materials were provided (Tex. Prop. Code § 53.056(a-1)).',
                'The notice follows the form in § 53.056(a-2): date, project description and address, claimant, type of labor or materials, original contractor, party contracted with if different, claim amount, contact person and address. It may include an invoice or billing statement (§ 53.056(a-3)).',
            ],
        ],
        'noi' => [
            'title' => 'Notice of Intent to File a Lien Affidavit',
            'statute' => 'Tex. Prop. Code ch. 53',
            'sections' => ['demand_days' => 10],
            'notes' => [
                'Texas has no notice-of-intent step; the monthly notice of claim and the lien affidavit are the statutory steps. This is a demand courtesy.',
            ],
        ],
    ],
];
