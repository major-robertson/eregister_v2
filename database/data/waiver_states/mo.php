<?php

/**
 * Missouri: Mo. Rev. Stat. § 429.016 (residential notice-of-rights regime;
 * subsections 25–30 govern waivers).
 *
 * Missouri prescribes exactly ONE statutory waiver form: the "UNCONDITIONAL
 * FINAL LIEN WAIVER FOR RESIDENTIAL REAL PROPERTY" in § 429.016.27, which
 * "shall only be valid if it is on a form that is substantially as follows"
 * (substantial compliance, not verbatim). It applies ONLY to unconditional
 * final waivers on "residential real property" as defined in § 429.016.2
 * (new residential construction/development intended for sale or occupancy,
 * incl. condos/townhomes/co-ops), not owner-occupied repair/remodel of four
 * units or fewer (§ 429.013) and not commercial projects. All other waiver
 * kinds are freely draftable (§ 429.016.25), so they keep the generic house
 * forms; only unconditional_final swaps to the statutory body when the
 * project's property class is residential (via residential_template).
 *
 * Execution: no notary, no witness. The signer's name, title or position,
 * address, and telephone number must be typed or legibly printed immediately
 * above or below the signature, and the date immediately adjacent to the
 * signature (§ 429.016.27). The waiver is enforceable notwithstanding
 * claimant's failure to receive any promised payment (§ 429.016.29), and a
 * paid-in-full claimant who recorded a notice of rights must furnish the
 * waiver within 5 calendar days of a written request or face a $500 penalty
 * and presumed slander-of-title liability (§ 429.016.30).
 *
 * Text verified verbatim against revisor.mo.gov (July 2026); section
 * effective Aug. 28, 2010 (L. 2010 H.B. 1692 merged with H.B. 2058), never
 * amended.
 */

return [
    'state' => 'MO',
    'state_name' => 'Missouri',
    'family' => 'special',
    'statute' => 'Mo. Rev. Stat. § 429.016.27',
    'compliance_standard' => 'substantial',
    'advance_waiver_note' => 'Mo. Rev. Stat. § 429.005.1: an agreement to waive mechanic\'s lien rights in anticipation of and in consideration for the awarding of a contract or subcontract, whether express or implied, is against public policy and unenforceable.',
    'ui_notes' => [
        'Missouri prescribes only one statutory waiver form: the "Unconditional Final Lien Waiver for Residential Real Property" (§ 429.016.27). When the project is residential, the unconditional final waiver is generated on that statutory form automatically; all other waiver types use general-purpose forms, which § 429.016.25 permits.',
        'The § 429.016 regime covers new residential construction or development intended for sale or occupancy (including condominiums, townhomes, and co-ops). Repair or remodel work on owner-occupied residential property of four units or fewer falls under § 429.013 instead, and commercial projects have no statutory waiver forms.',
        'The unconditional final residential waiver is valid and enforceable even if the claimant never receives the promised payment (§ 429.016.29): sign it only after payment in full.',
        'A claimant who recorded a notice of rights and has been paid in full must furnish the unconditional final waiver no later than 5 calendar days after a written request; failure carries a $500 statutory penalty and presumed slander-of-title liability (§ 429.016.30).',
        'On the statutory form, the signer\'s name, title or position, address, and telephone number must be typed or legibly printed immediately above or below the signature, with the signing date immediately adjacent to the signature.',
    ],
    'kinds' => [
        'unconditional_final' => [
            'residential_template' => 'documents.lien.waivers.bodies.mo-unconditional-final-residential',
            'residential_title' => 'Unconditional Final Lien Waiver for Residential Real Property',
        ],
    ],
    'landing' => [
        'headline' => 'Missouri lien waivers: one statutory form, the unconditional final waiver for residential property',
        'summary' => 'Missouri prescribes a single statutory lien waiver form: the "Unconditional Final Lien Waiver for Residential Real Property" in Mo. Rev. Stat. § 429.016.27, which is only valid on a form substantially as set out in the statute. Every other waiver (conditional, progress, or anything on commercial property) has no prescribed form and is a matter of contract, though § 429.005 voids lien waivers demanded as the price of being awarded the contract. No notarization or witness is required, but beware: the statutory residential waiver is enforceable even if the promised payment never arrives (§ 429.016.29).',
    ],
];
