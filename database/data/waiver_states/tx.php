<?php

/*
 * Texas: Tex. Prop. Code ch. 53, subch. L (§§ 53.281–53.284, 53.286, 53.287).
 *
 * Four statutory forms at § 53.284(b)-(e); a waiver is unenforceable unless it
 * "substantially complies with the applicable form" (§ 53.284(a)) and is signed
 * by the claimant or its authorized agent (§ 53.281(b)). Unconditional forms
 * must carry the § 53.284(c)(1)/(e)(1) notice at the TOP of the document in
 * bold type at least as large as the largest type used in the document and not
 * smaller than 10-point. § 53.283 prohibits requiring an unconditional waiver
 * unless the claimant was actually paid in good and sufficient funds. HB 2237
 * (Acts 2021, 87th Leg., ch. 690, § 35, eff. 1/1/2022) removed the notarization
 * requirement from § 53.281(b) for original contracts entered into on or after
 * January 1, 2022 (non-retroactive).
 */

return [
    'state' => 'TX',
    'state_name' => 'Texas',
    'family' => 'statutory_four',
    'statute' => 'Tex. Prop. Code §§ 53.281–53.284',
    'compliance_standard' => 'substantial',
    'advance_waiver_note' => 'Tex. Prop. Code § 53.286: notwithstanding any other law and except as provided by § 53.282, any contract, agreement, or understanding purporting to waive the right to file or enforce any lien or claim created under Chapter 53 is void as against public policy. A waiver is enforceable only via the § 53.284 statutory forms.',
    'ui_notes' => [
        'Projects whose original (prime) contract was signed before January 1, 2022 may still require notarized waivers. HB 2237, which removed the notarization requirement, applies only to original contracts entered into on or after that date. We generate the modern unnotarized forms.',
        'Texas law prohibits requiring an unconditional waiver and release unless the claimant has actually been paid in good and sufficient funds (Tex. Prop. Code § 53.283). If payment is (or will be) by a single-payee or joint-payee check that has not cleared, use the conditional form (§ 53.284(b), (d)).',
    ],
    'kinds' => [
        'conditional_progress' => [
            'template' => 'documents.lien.waivers.bodies.tx-conditional-progress',
            'title' => 'Conditional Waiver and Release on Progress Payment',
        ],
        'unconditional_progress' => [
            'template' => 'documents.lien.waivers.bodies.tx-unconditional-progress',
            'title' => 'Unconditional Waiver and Release on Progress Payment',
        ],
        'conditional_final' => [
            'template' => 'documents.lien.waivers.bodies.tx-conditional-final',
            'title' => 'Conditional Waiver and Release on Final Payment',
        ],
        'unconditional_final' => [
            'template' => 'documents.lien.waivers.bodies.tx-unconditional-final',
            'title' => 'Unconditional Waiver and Release on Final Payment',
        ],
    ],
    'landing' => [
        'headline' => 'Texas Lien Waiver Forms: the Four Statutory Waivers Under Property Code § 53.284',
        'summary' => 'Texas makes a lien or payment-bond waiver unenforceable unless it substantially complies with one of the four statutory forms in Property Code § 53.284: conditional and unconditional versions for progress and final payments. Unconditional forms must carry a bold statutory warning at the top of the document, and no one may require an unconditional waiver until the claimant has actually been paid in good and sufficient funds. Since January 1, 2022 (HB 2237), waivers on new original contracts no longer need to be notarized; the claimant\'s (or its authorized agent\'s) signature is enough.',
    ],
];
