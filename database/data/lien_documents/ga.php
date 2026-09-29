<?php

/*
 * Georgia: O.C.G.A. Title 44, ch. 14, art. 8, part 3.
 *
 * Claim of lien: § 44-14-361.1(a)(2) (filed in the office of the clerk of the
 * superior court of the county where the property is located within 90 days
 * after the last labor, services or materials; a copy sent to the owner and
 * the contractor within two business days of filing; the claim "shall include
 * a statement regarding its expiration pursuant to Code Section 44-14-367 and
 * a notice to the owner of the property on which a claim of lien is filed that
 * such owner has the right to contest the lien; failure to include such
 * statement and notice shall invalidate the lien"). Expiration: § 44-14-367
 * (395 days after filing unless a notice of commencement of lien action is
 * filed). Notice to contest: § 44-14-368. Notice to contractor by a claimant
 * without privity: § 44-14-361.5. The Georgia code is not on a site that
 * allows automated fetches; the 395-day sentence is the one filed on every
 * 2026 Georgia lien and matches § 44-14-361.1(a)(2) as reproduced by the
 * Georgia Superior Court Clerks' Cooperative Authority. Re-check both clauses
 * against the official code before the first Georgia PDF goes out.
 */

return [
    'state' => 'GA',
    'state_name' => 'Georgia',
    'recording' => [
        'filing_office' => ['label' => 'Clerk of Superior Court (county where the property is located)', 'method' => 'erecord', 'vendor' => 'GSCCCA eFile'],
        'parcel_label' => 'Tax Parcel ID',
        'fee_note' => '$25 per instrument (2026).',
        'notes' => [
            'File within 90 days after the last furnishing of labor, services or materials (O.C.G.A. § 44-14-361.1(a)(2)).',
            'Send a true and accurate copy of the claim of lien to the owner (and to the contractor, when the claimant has no privity with the owner) within two business days of filing, by registered or certified mail or statutory overnight delivery (O.C.G.A. § 44-14-361.1(a)(2)).',
            'Georgia legal descriptions carry the land lot, district and, where platted, lot, block, subdivision and plat book/page.',
        ],
    ],
    'execution' => [
        'verification' => 'sworn',
        'notary' => true,
        'notary_form' => 'jurat',
    ],
    'service' => [
        'recipients' => ['owner', 'gc'],
        'days_after' => 2,
        'method' => 'certified_mail',
    ],
    'kinds' => [
        'mechanics_lien' => [
            'title' => 'Claim of Lien',
            'statute' => 'O.C.G.A. § 44-14-361.1',
            'sections' => [
                'amount' => 'breakdown',
                'cancellation_block' => true,
                'gc' => false,
            ],
            'clauses' => [
                // § 44-14-361.1(a)(2): the expiration statement must appear on the face of the lien
                // in at least 12-point bold type, and the lien must tell the owner of the right to
                // contest it; leaving either out invalidates the lien.
                'bold_statement' => 'This claim of lien expires and is void 395 days from the date of filing of the claim of lien if no notice of commencement of lien action is filed in that time period.',
                'before_signature' => [
                    'NOTICE TO OWNER: Pursuant to O.C.G.A. § 44-14-368, the owner of the real property described herein, or the owner\'s agent or attorney, or the contractor or the contractor\'s agent or attorney, has the right to contest this claim of lien and to shorten the time within which a lien action must be commenced by filing a notice of contest of lien with the clerk of the superior court of the county in which this claim of lien is filed.',
                ],
                'affirmations' => [
                    'This claim of lien is filed for record in the office of the clerk of the superior court of the county in which the property is located within 90 days after the last furnishing of labor, services or materials by the claimant, pursuant to O.C.G.A. § 44-14-360 et seq.',
                ],
            ],
        ],
        'lien_release' => [
            'title' => 'Release and Cancellation of Claim of Lien',
            'statute' => 'O.C.G.A. § 44-14-362',
            'execution' => [
                'verification' => 'acknowledged',
                'notary' => true,
                'notary_form' => 'acknowledgment',
            ],
            'clauses' => [
                'affirmations' => [
                    'The claim of lien described above has been paid and satisfied. The undersigned releases it and authorizes and directs the Clerk of Superior Court to cancel it of record.',
                ],
            ],
        ],
        'prelim_notice' => [
            'title' => 'Notice to Contractor',
            'statute' => 'O.C.G.A. § 44-14-361.5',
            'body' => 'documents.lien.letters.bodies.ga-notice-to-contractor',
            'template_version' => 1,
            'sections' => [
                'amount' => 'estimate',
                'gc' => true,
                'first_furnish' => true,
            ],
            'service' => [
                'recipients' => ['owner', 'gc'],
                'days_after' => 30,
                'method' => 'certified_mail',
            ],
            'notes' => [
                'Required only when the owner or contractor filed a Notice of Commencement: a claimant without privity of contract with the contractor sends it to the owner and the contractor within 30 days after first furnishing (O.C.G.A. § 44-14-361.5).',
                'Georgia also has a Preliminary Notice of Lien Rights filed with the clerk within 30 days of first furnishing (O.C.G.A. § 44-14-361.3); it is not this document.',
            ],
        ],
        'noi' => [
            'title' => 'Notice of Intent to File a Claim of Lien',
            'statute' => 'O.C.G.A. § 44-14-360 et seq.',
            'sections' => ['demand_days' => 10],
            'notes' => [
                'Not required in Georgia; a demand courtesy sent before the claim of lien is filed.',
            ],
        ],
    ],
];
