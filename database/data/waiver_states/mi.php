<?php

/**
 * Michigan: Construction Lien Act, 1980 PA 497, § 115 (MCL 570.1115).
 *
 * MCL 570.1115(9) prescribes four waiver forms (partial/full crossed with
 * conditional/unconditional) introduced by "The following forms shall be
 * used in substantially the following format to execute waivers of
 * construction liens:" (substantial compliance, not strict verbatim).
 * Statutory titles: (a) PARTIAL UNCONDITIONAL WAIVER, (b) PARTIAL
 * CONDITIONAL WAIVER, (c) FULL UNCONDITIONAL WAIVER, (d) FULL CONDITIONAL
 * WAIVER. Mapping: partial ↔ progress, full ↔ final.
 *
 * Conditional waivers are effective upon payment of the amount indicated
 * (MCL 570.1115(4)); there is no check-clearing rule, no affidavit of
 * nonpayment, and no deemed-effective day count. No notarization or witness:
 * adding a notary block alters the statutory format and may invalidate
 * the waiver. Every form ends with the all-caps warning "DO NOT SIGN BLANK
 * OR INCOMPLETE FORMS. RETAIN A COPY."
 *
 * Text verified against legislature.mi.gov (MCL complete through PA 20 of
 * 2026); last substantive amendment 2007 PA 28.
 */

return [
    'state' => 'MI',
    'state_name' => 'Michigan',
    'family' => 'statutory_four',
    'statute' => 'MCL 570.1115(9)',
    'compliance_standard' => 'substantial',
    'advance_waiver_note' => 'MCL 570.1115(1): a contract for an improvement may not require that construction lien rights be waived in advance of work performed. A waiver obtained as part of such a contract is contrary to public policy and invalid, except to the extent payment for the labor and material was actually made to the person giving the waiver.',
    'ui_notes' => [
        'Both partial waiver forms carry a statutory "(circle one) does / does not" election stating whether this waiver, together with all previous waivers, covers all amounts due through the date shown: circle the correct option when executing the form.',
        'Do not add a notary or witness block: Michigan\'s statutory forms are executed by signature alone, and altering the prescribed format may invalidate the waiver.',
        'Conditional waivers are effective upon actual payment of the amount indicated in the waiver (MCL 570.1115(4)); Michigan has no affidavit-of-nonpayment filing or deemed-effective deadline to track.',
        'On residential projects, an owner or lessee may not rely on a waiver received from someone other than the lien claimant without first verifying its authenticity with the claimant; the verification paragraph is part of the statutory form text.',
    ],
    'kinds' => [
        'conditional_progress' => [
            'template' => 'documents.lien.waivers.bodies.mi-partial-conditional',
            'title' => 'Partial Conditional Waiver',
        ],
        'unconditional_progress' => [
            'template' => 'documents.lien.waivers.bodies.mi-partial-unconditional',
            'title' => 'Partial Unconditional Waiver',
        ],
        'conditional_final' => [
            'template' => 'documents.lien.waivers.bodies.mi-full-conditional',
            'title' => 'Full Conditional Waiver',
        ],
        'unconditional_final' => [
            'template' => 'documents.lien.waivers.bodies.mi-full-unconditional',
            'title' => 'Full Unconditional Waiver',
        ],
    ],
    'landing' => [
        'headline' => 'Michigan lien waivers: the four statutory forms of MCL 570.1115',
        'summary' => 'Michigan\'s Construction Lien Act prescribes four lien waiver forms at MCL 570.1115(9) (partial and full waivers, each in conditional and unconditional versions) and they must be used in substantially the statutory format. Conditional waivers take effect upon actual payment of the amount indicated, and no notarization or witness is required; every form must end with the capitalized warning "DO NOT SIGN BLANK OR INCOMPLETE FORMS. RETAIN A COPY." Advance waivers required as part of a construction contract are invalid under MCL 570.1115(1).',
    ],
];
