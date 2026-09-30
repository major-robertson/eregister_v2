<?php

/*
 * California: Civil Code, Title 2 (Private Works of Improvement), §§ 8000-8848.
 *
 * Claim of mechanics lien: § 8416 ((a) a written statement, signed and
 * verified by the claimant, with items (1)-(8); (7) a proof of service
 * affidavit by the person who served the copy on the owner; (8) the NOTICE OF
 * MECHANICS LIEN "printed in at least 10-point boldface type", last sentence in
 * uppercase except the CSLB address; (b) recorded without acknowledgment;
 * (c)-(e) serve a copy on the owner by registered, certified or first-class
 * mail with a certificate of mailing, or the lien is unenforceable). Record
 * within 90 days after completion, or 60 days after a recorded notice of
 * completion / cessation (§§ 8412, 8414). Preliminary notice: § 8202 (general
 * description, estimate of the total price, the NOTICE TO PROPERTY OWNER in
 * boldface), served within 20 days of first furnishing (§ 8204) on the owner,
 * the direct contractor and the construction lender (§ 8200), with a proof of
 * notice declaration (§ 8118). Statutory text verified against
 * leginfo.legislature.ca.gov on 2026-09-29.
 */

return [
    'state' => 'CA',
    'state_name' => 'California',
    'recording' => [
        'filing_office' => ['label' => 'County Recorder', 'method' => 'either'],
        'parcel_label' => 'APN',
        'notes' => [
            'Record within 90 days after completion of the work of improvement, or within 60 days after the owner records a notice of completion or cessation (Cal. Civ. Code §§ 8412, 8414).',
            'Serve a copy of the claim, with the Notice of Mechanics Lien, on the owner or reputed owner by registered, certified or first-class mail with a certificate of mailing (Cal. Civ. Code § 8416(c)); the proof of service affidavit is part of the claim (§ 8416(a)(7)).',
        ],
    ],
    'execution' => [
        'verification' => 'verified',
        'notary' => false,
        'notary_form' => null,
    ],
    'service' => [
        'recipients' => ['owner'],
        'days_after' => 0,
        'method' => 'certified_mail',
        'proof' => 'declaration',
        'certificate_on_instrument' => true,
        // A declaration executed outside California must still recite California law (CCP § 2015.5).
        'perjury_state' => 'CA',
    ],
    'kinds' => [
        'mechanics_lien' => [
            'title' => 'Claim of Mechanics Lien',
            'statute' => 'Cal. Civ. Code § 8416',
            'sections' => [
                'amount' => 'breakdown',
                'prior_notice' => true,
            ],
            'clauses' => [
                // § 8416(a)(8): structured (heading, three paragraphs, last sentence in uppercase).
                'notice_box' => 'documents.lien.instruments.clauses.ca-notice-of-mechanics-lien',
                'affirmations' => [
                    'The amount stated above is the claimant\'s demand after deducting all just credits and offsets.',
                ],
            ],
            'notes' => [
                'The Notice of Mechanics Lien block must be in at least 10-point boldface type, with the last sentence in uppercase (Cal. Civ. Code § 8416(a)(8)).',
                'The proof of service affidavit prints with the claim; fill the service date and method before recording.',
            ],
        ],
        'lien_release' => [
            'title' => 'Release of Mechanics Lien',
            'statute' => 'Cal. Civ. Code § 8000 et seq.',
            'execution' => [
                'verification' => 'acknowledged',
                'notary' => true,
                'notary_form' => 'acknowledgment',
                'notary_variant' => 'ca',
            ],
            'service' => ['certificate_on_instrument' => false],
            'clauses' => [
                'affirmations' => [
                    'The undersigned claimant releases the claim of mechanics lien described above and authorizes the County Recorder to note the release of record.',
                ],
            ],
        ],
        'prelim_notice' => [
            'title' => 'California Preliminary Notice',
            'statute' => 'Cal. Civ. Code §§ 8200-8216',
            'body' => 'documents.lien.letters.bodies.ca-preliminary-notice',
            'template_version' => 1,
            'sections' => [
                'amount' => 'estimate',
                'lender' => true,
                'gc' => true,
                'first_furnish' => true,
            ],
            'service' => [
                'recipients' => ['owner', 'gc', 'lender'],
                'days_after' => 20,
                'method' => 'certified_mail',
                'proof' => 'declaration',
            ],
            'clauses' => [
                // § 8202(a)(3), verbatim, "in boldface type".
                'notice_box' => 'documents.lien.letters.clauses.ca-notice-to-property-owner',
            ],
            'notes' => [
                'Serve within 20 days after first furnishing on the owner or reputed owner, the direct contractor and the construction lender, if any (Cal. Civ. Code §§ 8200, 8204); one copy per party.',
                'Keep the proof of notice declaration with the USPS certificate of mailing or receipts (Cal. Civ. Code § 8118).',
            ],
        ],
        'noi' => [
            'title' => 'Notice of Intent to Record a Mechanics Lien',
            'statute' => 'Cal. Civ. Code § 8000 et seq.',
            'sections' => ['demand_days' => 10],
            'notes' => [
                'California has no notice-of-intent step; the preliminary notice and the claim of lien are the statutory steps. This is a demand courtesy.',
            ],
        ],
    ],
];
