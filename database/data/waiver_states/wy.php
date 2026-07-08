<?php

/*
 * Wyoming: Wyo. Stat. § 29-10-101(b) (statutory LIEN WAIVER form).
 *
 * Wyoming prescribes a SINGLE statutory lien waiver form for everything:
 * "The form for waiver of a lien shall be completed in substantially the
 * following form": mandatory "shall," substantial (not verbatim)
 * compliance. There are no conditional/unconditional or progress/final
 * families. The one form is unconditional-on-payment in character ("In
 * consideration of the PAYMENT received to date") but carries built-in
 * partial-waiver mechanics: a retainage reservation blank, an unpaid-sum
 * blank ("The undersigned has not been paid the sum of $___ ... and retains
 * the right to file a lien"), and an automatic reservation for labor and
 * materials hereafter furnished. Both unconditional kinds therefore map to
 * the same body; the conditional kinds are disabled: Wyoming has no
 * conditional-waiver mechanism, no check-clearing statute, and no
 * affidavit-of-nonpayment procedure.
 *
 * The form itself contains the mandatory claimant warning note, the
 * TO/PROJECT/FROM/DATE/PAYMENT header, both dishonored-payment sentences
 * (the waiver survives dishonor of uncertified funds the claimant accepted,
 * but does not apply if payment tendered by the OWNER is dishonored or
 * revoked), a By/Title/Date signature block, and a notarial acknowledgment
 * ("This instrument was acknowledged before me on this ___ day of ___,
 * 20___ ... Notarial officer / My Commission Expires / Seal"). Wyoming is
 * one of the very few states requiring notarized lien waivers, so e-sign
 * delivery is disabled. Related: § 29-10-101(a) preliminary notice states
 * "A form of lien waiver is attached to this notice"; § 29-2-112(a)(i)
 * requires the preliminary notice to advise of the right to obtain a lien
 * waiver upon payment.
 *
 * Statutory text verified against the Wyoming Legislature's official Title
 * 29 statutes PDF (wyoleg.gov/statutes/compress/title29.pdf, fetched July
 * 2026), cross-checked against FindLaw's Wyo. Stat. § 29-10-101 (current
 * through January 1, 2024): identical. No amendments to the form
 * 2019–2026; it dates from the 2010 lien-law overhaul (2010 SF0025, Laws
 * 2010, ch. 92, effective July 1, 2011).
 */

return [
    'state' => 'WY',
    'state_name' => 'Wyoming',
    'family' => 'statutory_single',
    'statute' => 'Wyo. Stat. § 29-10-101(b)',
    'compliance_standard' => 'substantial',
    'notarization_required' => true,
    'esign_allowed' => false,
    'esign_disabled_reason' => 'Wyoming\'s statutory lien waiver form includes a notarial acknowledgment block as part of the form itself: the waiver must be signed before a notarial officer, so it has to be printed and wet-signed rather than e-signed.',
    'advance_waiver_note' => 'Wyoming has no statute expressly voiding advance lien waivers, but the statutory scheme presumes payment first: the form\'s stated consideration is "the PAYMENT received to date," and Wyo. Stat. § 29-2-112(a)(i) describes the waiver as something the owner or contractor obtains upon payment for services or materials. Wyo. Stat. § 29-2-106(b) separately provides that no contract between the owner and contractor may affect or restrict the lien rights of subcontractors or materialmen.',
    'ui_notes' => [
        'Wyoming prescribes ONE statutory "Lien Waiver" form for all payments (Wyo. Stat. § 29-10-101(b)). There are no conditional/unconditional or progress/final variants. To keep a partial payment partial, complete the form\'s reservation blanks: retainage withheld, and the sum not yet paid for which lien rights are retained.',
        'Notarization is required: the notarial acknowledgment block is part of the statutory form, and Wyoming is one of the few states where an un-notarized lien waiver is treated as ineffective. Print the waiver and sign it before a notarial officer.',
        'Bounced-check trap: by its own terms the waiver may be relied upon by the owner even if the claimant accepts payment in uncertified funds that are later dishonored or revoked. The waiver "shall remain in full force and effect." It does not apply only if payment tendered by the OWNER is dishonored or revoked. Insist on certified funds before signing.',
        'The form releases only lien rights against the project and the real property improvements; it says nothing about payment bond rights, and Wyoming has no stop-notice regime.',
    ],
    'kinds' => [
        'conditional_progress' => [
            'enabled' => false,
            'template' => 'documents.lien.waivers.bodies.wy-lien-waiver',
            'disabled_reason' => 'Wyoming prescribes a single statutory lien waiver form with no conditional variant; the form reserves any amounts not yet paid via its unpaid-sum blank. Use the Lien Waiver.',
            'redirect_kind' => 'unconditional_progress',
        ],
        'unconditional_progress' => [
            'enabled' => true,
            'template' => 'documents.lien.waivers.bodies.wy-lien-waiver',
            'title' => 'Lien Waiver',
            'template_version' => 1,
        ],
        'conditional_final' => [
            'enabled' => false,
            'template' => 'documents.lien.waivers.bodies.wy-lien-waiver',
            'disabled_reason' => 'Wyoming prescribes a single statutory lien waiver form with no conditional variant; the form reserves any amounts not yet paid via its unpaid-sum blank. Use the Lien Waiver.',
            'redirect_kind' => 'unconditional_final',
        ],
        'unconditional_final' => [
            'enabled' => true,
            'template' => 'documents.lien.waivers.bodies.wy-lien-waiver',
            'title' => 'Lien Waiver',
            'template_version' => 1,
        ],
    ],
    'landing' => [
        'headline' => 'Wyoming Lien Waivers: One Statutory Form Under Wyo. Stat. § 29-10-101(b)',
        'summary' => 'Wyoming prescribes a single statutory "Lien Waiver" form for every payment. There are no conditional or unconditional, progress or final variants. The form waives lien rights in consideration of the payment received to date while reserving retainage, any sum not yet paid, and all labor or materials furnished afterward, and it must be completed in substantially the statutory form. Wyoming is also one of the few states that requires lien waivers to be notarized (the acknowledgment block is part of the form), and the waiver generally survives a bounced check the claimant accepted in uncertified funds (unless payment tendered by the owner is dishonored), so claimants should insist on certified funds before signing.',
    ],
];
