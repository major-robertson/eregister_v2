<?php

/*
 * Utah: Utah Code § 38-1a-802 (Waiver or limitation of a lien right; Forms; Scope).
 *
 * Two statutory forms at § 38-1a-802(4)(b)-(c); a waiver "meets the requirements
 * of this section if it is in substantially the form provided" (§ 38-1a-802(4)(a)).
 * Both forms are self-conditioned: they become effective only once the claimant
 * endorses a check in the referenced Payment Amount and the check is paid by the
 * depository institution on which it is drawn. Utah prescribes NO unconditional
 * forms, and § 38-1a-802(3) voids any waiver (statutory-form or not) if the check
 * fails to clear for any reason. § 38-1a-105 voids all contractual/advance lien
 * waivers; the only exception is a waiver given in consideration of payment per
 * § 38-1a-802. No notarization, witness, or typography requirements. A restrictive
 * check endorsement per § 38-1a-802(4)(d)-(e) is an alternative waiver mechanism
 * (not generated here).
 */

return [
    'state' => 'UT',
    'state_name' => 'Utah',
    'family' => 'statutory_two',
    'statute' => 'Utah Code § 38-1a-802',
    'compliance_standard' => 'substantial',
    'advance_waiver_note' => 'Utah Code § 38-1a-105: a right or privilege under the lien chapter may not be waived or limited by contract, and any contract provision purporting to do so is void. The only exception is a waiver and release given in consideration of payment as provided in § 38-1a-802.',
    'ui_notes' => [
        'Utah has no unconditional waiver forms. Both statutory forms become effective only once the claimant endorses a check in the referenced Payment Amount and the check is paid by the depository institution on which it is drawn, and Utah Code § 38-1a-802(3) voids any waiver and release if the check fails to clear for any reason.',
        'The statutory forms are a safe harbor, not the only way to waive rights: under Lane Myers Constr. v. National City Bank, 2014 UT 58, signing any document containing release language and receiving payment can waive lien rights even if the document is not in the § 38-1a-802(4) form.',
    ],
    'kinds' => [
        'conditional_progress' => [
            'template' => 'documents.lien.waivers.bodies.ut-conditional-progress',
            'title' => 'Utah Conditional Waiver and Release Upon Progress Payment',
        ],
        'unconditional_progress' => [
            'enabled' => false,
            'disabled_reason' => 'Utah prescribes no unconditional waiver form. The statutory forms become effective on check endorsement and payment by the depository institution, and Utah Code § 38-1a-802(3) voids any waiver paid by a check that fails to clear. Use the Utah Conditional Waiver and Release Upon Progress Payment.',
            'redirect_kind' => 'conditional_progress',
        ],
        'conditional_final' => [
            'template' => 'documents.lien.waivers.bodies.ut-final',
            'title' => 'Utah Waiver and Release Upon Final Payment',
        ],
        'unconditional_final' => [
            'enabled' => false,
            'disabled_reason' => 'Utah prescribes no unconditional waiver form. Even the statutory final-payment waiver (§ 38-1a-802(4)(c)) is conditioned on check endorsement and payment by the depository institution, and § 38-1a-802(3) voids any waiver paid by a check that fails to clear. Use the Utah Waiver and Release Upon Final Payment.',
            'redirect_kind' => 'conditional_final',
        ],
    ],
    'landing' => [
        'headline' => 'Utah Lien Waiver Forms: the Statutory Waivers Under Utah Code § 38-1a-802',
        'summary' => 'Utah voids every contractual or advance lien waiver (Utah Code § 38-1a-105); a claimant\'s waiver is enforceable only if the claimant signs a waiver and release and actually receives payment of the identified amount (§ 38-1a-802(2)). The statute supplies two substantially-similar form templates (the Utah Conditional Waiver and Release Upon Progress Payment and the Utah Waiver and Release Upon Final Payment), both of which take effect only once the claimant endorses a check in the referenced Payment Amount and the check is paid by the bank on which it is drawn. Utah prescribes no unconditional forms: § 38-1a-802(3) voids any waiver paid by a check that fails to clear, preserving all lien, bond, contract, and other recovery rights.',
    ],
];
