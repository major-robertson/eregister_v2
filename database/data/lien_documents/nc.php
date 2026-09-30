<?php

/*
 * North Carolina: N.C. Gen. Stat. ch. 44A, art. 2.
 *
 * Claim of lien on real property: § 44A-12 ((a) filed with the clerk of
 * superior court of the county where the property is located; (b) within 120
 * days after last furnishing; (c) "must be filed using a form substantially as
 * follows", items (1)-(6) plus the G.S. 44A-11 service certification). Service
 * on the record owner: § 44A-11. Enforcement action within 180 days after last
 * furnishing: § 44A-13. Notice to lien agent: § 44A-11.2 (LiensNC). Register of
 * Deeds e-recording carries the submitter statement under § 47-14(a1)(5).
 * Statutory form verified against ncleg.gov on 2026-09-29.
 *
 * Practice note (not law): 2026 filings went both ways, by mail to the clerk
 * of superior court (cover sheet, money order, return envelope) and by
 * e-recording with the Register of Deeds. Which channel perfects the lien is
 * for Major and counsel, not this file.
 */

return [
    'state' => 'NC',
    'state_name' => 'North Carolina',
    'recording' => [
        'filing_office' => ['label' => 'Clerk of Superior Court (county where the property is located)', 'method' => 'mail'],
        'parcel_label' => 'PIN',
        'cover_sheet' => true,
        'fee_note' => 'Clerk of superior court about $6.50 by money order or certified check; Register of Deeds e-recording $26 (2026).',
        'notes' => [
            'File within 120 days after the last furnishing of labor or materials (G.S. 44A-12(b)); an action to enforce must be started within 180 days after the last furnishing (G.S. 44A-13).',
            'Serve the record owner (and the contractor when subrogation is asserted) per G.S. 44A-11; the certification on the form says it was done.',
            'Mecklenburg County mail filings need the clerk\'s coversheet MCSC-AD-034 and a money order or certified check; no cash.',
            'Projects with a lien agent: send the Notice to Lien Agent through LiensNC within 15 days of first furnishing (G.S. 44A-11.2).',
        ],
    ],
    'execution' => [
        'verification' => 'verified',
        'notary' => true,
        'notary_form' => 'jurat',
        'notary_variant' => 'nc',
    ],
    'service' => [
        'recipients' => ['owner', 'gc'],
        'days_after' => null,
        'method' => 'certified_mail',
    ],
    'kinds' => [
        'mechanics_lien' => [
            'title' => 'Claim of Lien on Real Property',
            'statute' => 'N.C. Gen. Stat. § 44A-12',
            'body' => 'documents.lien.instruments.bodies.nc-claim-of-lien',
            'template_version' => 1,
            'sections' => [
                'amount' => 'breakdown',
                'gc' => true,
                'lien_agent' => true,
            ],
            'clauses' => [
                // § 44A-12(c), verbatim from the statutory form.
                'affirmations' => [
                    'I hereby certify that I have served the parties listed in (2) above in accordance with the requirements of G.S. 44A-11.',
                ],
                'before_signature' => [
                    'This Claim of Lien on Real Property is filed within 120 days after the last furnishing of labor or materials at the site of the improvement (G.S. 44A-12(b)). An action to enforce this claim of lien must be commenced within 180 days after the last furnishing of labor or materials (G.S. 44A-13).',
                ],
            ],
            'notes' => [
                'The statutory form ends with "Filed this ____ day of ____ / Clerk of Superior Court"; the clerk fills that line.',
            ],
        ],
        'lien_release' => [
            'title' => 'Release of Claim of Lien on Real Property',
            'statute' => 'N.C. Gen. Stat. § 44A-16',
            'execution' => [
                'verification' => 'acknowledged',
                'notary' => true,
                'notary_form' => 'acknowledgment',
                'notary_variant' => 'nc',
            ],
            'clauses' => [
                'affirmations' => [
                    'The undersigned lien claimant acknowledges full payment and satisfaction of the claim of lien described above, releases the claim of lien, and directs the Clerk of Superior Court to cancel it of record.',
                ],
            ],
        ],
        'prelim_notice' => [
            'title' => 'Notice to Lien Agent',
            'statute' => 'N.C. Gen. Stat. § 44A-11.2',
            'body' => 'documents.lien.letters.bodies.nc-notice-to-lien-agent',
            'template_version' => 1,
            'sections' => [
                'amount' => 'none',
                'lien_agent' => true,
                'gc' => false,
                'first_furnish' => true,
            ],
            'service' => [
                'recipients' => ['owner'],
                'days_after' => 15,
                'method' => 'certified_mail',
            ],
            'notes' => [
                'Goes to the lien agent named in the Appointment of Lien Agent (LiensNC entry number), not to the owner; the owner copy is optional.',
                'Not required when the claimant contracted directly with the owner and no lien agent was appointed; projects of $40,000 or more must have one.',
            ],
        ],
        'noi' => [
            'title' => 'Notice of Intent to File a Claim of Lien',
            'statute' => 'N.C. Gen. Stat. ch. 44A',
            'sections' => ['demand_days' => 10],
            'notes' => [
                'Not required in North Carolina; a demand courtesy sent before the claim of lien is filed.',
            ],
        ],
    ],
];
