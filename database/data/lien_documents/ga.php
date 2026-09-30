<?php

/*
 * Georgia: O.C.G.A. Title 44, ch. 14, art. 8, part 3.
 *
 * Claim of lien: § 44-14-361.1(a)(2) (filed in the office of the clerk of the
 * superior court of the county where the property is located within 90 days
 * after the completion of the work or the furnishing of the material; the
 * claim "shall include a statement regarding its expiration pursuant to Code
 * Section 44-14-367 and a notice to the owner of the property on which a
 * claim of lien is filed that such owner has the right to contest the lien;
 * the absence of such statement or notice shall invalidate the lien"; the
 * claim "shall be in substance" the form in bodies/ga-claim-of-lien; a true
 * and accurate copy goes to the owner by registered or certified mail or
 * statutory overnight delivery no later than two business days after filing,
 * and also to the contractor when a notice of commencement was filed).
 * Expiration: § 44-14-367 ("Any lien filed after March 31, 2009, shall include
 * on the face of the lien the following statement in at least 12 point bold
 * font", quoted verbatim below; "Failure to include such language shall
 * invalidate the lien and prevent it from being filed"). Notice of contest:
 * § 44-14-368. Notice to contractor by a claimant without privity of contract
 * with the contractor: § 44-14-361.5 (within 30 days from the filing of the
 * notice of commencement or 30 days after first delivery, whichever is later;
 * contents in (c)). Verified against the 2025 Code of Georgia as published
 * on law.justia.com on 2026-09-29 (the LexisNexis official site blocks
 * automated reading; Justia reproduces the same text).
 */

return [
    'state' => 'GA',
    'state_name' => 'Georgia',
    'recording' => [
        'filing_office' => ['label' => 'Clerk of Superior Court (county where the property is located)', 'method' => 'erecord', 'vendor' => 'GSCCCA eFile'],
        'parcel_label' => 'Tax Parcel ID',
        'fee_note' => '$25 per instrument (2026).',
        'notes' => [
            'File within 90 days after the completion of the work or the furnishing of the material (O.C.G.A. § 44-14-361.1(a)(2)); the "claim became due" date on the face is the last date labor, services or materials were supplied.',
            'Send a true and accurate copy of the claim of lien to the owner no later than two business days after filing, by registered or certified mail or statutory overnight delivery; an entity owner may be served at its Secretary of State address or registered agent. When a notice of commencement was filed, also send a copy to the contractor at the address on it (O.C.G.A. § 44-14-361.1(a)(2)).',
            'A lien action must be commenced within 365 days of filing, with a notice of commencement of lien action filed with the clerk within 30 days after that (O.C.G.A. § 44-14-361.1(a)(3)).',
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
            'body' => 'documents.lien.instruments.bodies.ga-claim-of-lien',
            'template_version' => 1,
            'sections' => [
                'amount' => 'breakdown',
                'cancellation_block' => true,
                'gc' => false,
            ],
            'clauses' => [
                // § 44-14-367: verbatim, "on the face of the lien … in at least 12 point bold font";
                // § 44-14-361.1(a)(2): the lien must also tell the owner of the right to contest it.
                // The absence of either invalidates the lien.
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
                'Required only when a Notice of Commencement was filed with the clerk: a claimant without privity of contract with the contractor sends it to the owner (or the owner\'s agent) and the contractor, at the addresses on the notice of commencement, within 30 days from the filing of the notice of commencement or 30 days after first delivery, whichever is later (O.C.G.A. § 44-14-361.5(a), (c)).',
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
