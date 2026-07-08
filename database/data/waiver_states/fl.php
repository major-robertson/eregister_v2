<?php

/*
 * Florida: Fla. Stat. § 713.20 (Waiver or release of liens).
 *
 * Subsections (4) and (5) prescribe safe-harbor forms ("the waiver or
 * release may be in substantially the following form") for progress and
 * final lien waivers. The statutory forms are unconditional on their face;
 * § 713.20(7) lets a lienor who executes a waiver in exchange for a check
 * condition the waiver on payment of the check, so the conditional kinds
 * add that lienor-elected condition sentence to the statutory text.
 * § 713.20(6) forbids anyone from requiring a lienor to furnish a waiver
 * different from the statutory forms; § 713.20(8) makes voluntarily agreed
 * non-conforming waivers enforceable per their terms. No notarization,
 * witness, warning notice, or typography rules appear in the statute.
 * Statutory text verified against flsenate.gov (2025 Florida Statutes,
 * July 2026); § 713.20 unamended since ch. 99-6 (1999). The widely blogged
 * 2025 "verbatim form" mandate (SB 658) died in session.
 */

return [
    'state' => 'FL',
    'state_name' => 'Florida',
    'family' => 'safe_harbor',
    'statute' => 'Fla. Stat. § 713.20',
    'compliance_standard' => 'substantial',
    'advance_waiver_note' => 'Fla. Stat. § 713.20(2): a right to claim a lien may not be waived in advance. A lien right may be waived only to the extent of labor, services, or materials furnished, and any advance waiver is unenforceable.',
    'ui_notes' => [
        'Florida\'s statutory forms are unconditional on their face: they release lien rights on execution even if the check later bounces. A lienor paid by check may elect to condition the waiver on payment of that check (Fla. Stat. § 713.20(7)); the conditional kinds here add that lienor-elected condition.',
        'A payor may not require a lienor to furnish a waiver different from the statutory forms (Fla. Stat. § 713.20(6)); conditioning under § 713.20(7) is the lienor\'s election, not something the payor can demand or forbid.',
        'The final-payment form has no retention carve-out: signing it waives the lien for everything furnished, including retainage. Only the progress form excludes retention and labor, services, or materials furnished after the date specified.',
        'These forms release only the construction lien on the real property. Payment-bond claim waivers use separate statutory forms (Fla. Stat. § 713.235 private; § 255.05(2) public works) that are not yet supported here.',
    ],
    'kinds' => [
        'conditional_progress' => [
            'template' => 'documents.lien.waivers.bodies.fl-conditional-progress',
            'title' => 'Conditional Waiver and Release of Lien Upon Progress Payment',
            'template_version' => 1,
        ],
        'unconditional_progress' => [
            'template' => 'documents.lien.waivers.bodies.fl-progress',
            'title' => 'Waiver and Release of Lien Upon Progress Payment',
            'template_version' => 1,
        ],
        'conditional_final' => [
            'template' => 'documents.lien.waivers.bodies.fl-conditional-final',
            'title' => 'Conditional Waiver and Release of Lien Upon Final Payment',
            'template_version' => 1,
        ],
        'unconditional_final' => [
            'template' => 'documents.lien.waivers.bodies.fl-final',
            'title' => 'Waiver and Release of Lien Upon Final Payment',
            'template_version' => 1,
        ],
    ],
    'landing' => [
        'headline' => 'Florida Lien Waivers: The Safe-Harbor Forms Under Fla. Stat. § 713.20',
        'summary' => 'Florida prescribes safe-harbor lien waiver forms in Fla. Stat. § 713.20(4) (progress payment) and § 713.20(5) (final payment), and no one may require a lienor to sign a waiver that differs from them. The statutory forms are unconditional on their face (they take effect on signing even if the check never clears), but § 713.20(7) lets a lienor paid by check condition the waiver on payment of that check, which is how Florida conditional waivers are built. No notarization or witness is required, advance waivers are void under § 713.20(2), and the final-payment form waives everything including retainage, so unpaid lienors should stick to conditional or progress forms until funds actually arrive.',
    ],
];
