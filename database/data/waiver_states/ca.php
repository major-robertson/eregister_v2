<?php

/*
 * California: Civil Code §§ 8132, 8134, 8136, 8138 (Division 4, Part 6,
 * Title 1, Chapter 3, "Waiver and Release"; added by Stats. 2010, Ch. 697
 * (SB 189), operative July 1, 2012; unamended since).
 *
 * Four prescribed forms (conditional/unconditional x progress/final). A waiver
 * given in exchange for or to induce payment is "null, void, and unenforceable
 * unless it is in substantially the following form." The bodies reproduce the
 * statutory text verbatim with only the blanks bound. Conditional forms open
 * with the receipt-of-payment NOTICE; unconditional forms carry the "NOTICE TO
 * CLAIMANT" warning, which must be in at least as large a type as the largest
 * type otherwise in the form (the shell caps all type at 12pt and renders the
 * notice at 12pt bold). No notarization or witness; e-sign permitted (Civ.
 * Code § 1633.3 does not exclude works-of-improvement waivers from UETA).
 */

return [
    'state' => 'CA',
    'state_name' => 'California',
    'family' => 'statutory_four',
    'statute' => 'Cal. Civ. Code §§ 8132–8138',
    'compliance_standard' => 'substantial',
    'advance_waiver_note' => 'Cal. Civ. Code §§ 8122 and 8126: a contract term or any other oral or written statement purporting to waive, affect, or impair a claimant\'s lien, stop payment notice, or payment bond rights is void and unenforceable unless and until the claimant executes and delivers a waiver and release in the statutory form.',
    'ui_notes' => [
        'California waivers must substantially follow the statutory forms in Civil Code §§ 8132–8138: this form reproduces the statutory text exactly, with only the blanks filled. Do not add conditions, indemnities, or a notary block; alterations risk voiding the waiver.',
        'An unconditional waiver binds the claimant once signed, even if payment is never received. Use the conditional form until the identified check has actually been paid by the bank it is drawn on.',
    ],
    'kinds' => [
        'conditional_progress' => [
            'template' => 'documents.lien.waivers.bodies.ca-conditional-progress',
            'title' => 'Conditional Waiver and Release on Progress Payment',
        ],
        'unconditional_progress' => [
            'template' => 'documents.lien.waivers.bodies.ca-unconditional-progress',
            'title' => 'Unconditional Waiver and Release on Progress Payment',
        ],
        'conditional_final' => [
            'template' => 'documents.lien.waivers.bodies.ca-conditional-final',
            'title' => 'Conditional Waiver and Release on Final Payment',
        ],
        'unconditional_final' => [
            'template' => 'documents.lien.waivers.bodies.ca-unconditional-final',
            'title' => 'Unconditional Waiver and Release on Final Payment',
        ],
    ],
    'landing' => [
        'headline' => 'California lien waiver forms: the four statutory releases, reproduced exactly',
        'summary' => 'California prescribes exactly four lien waiver forms in Civil Code sections 8132 through 8138: conditional and unconditional releases for progress and final payments. Any waiver signed in exchange for, or to induce, a payment is null, void, and unenforceable unless it substantially follows the statutory form, so we generate the statutory text verbatim and fill in only the blanks. Conditional waivers take effect only when the identified check is paid by the bank it is drawn on, while unconditional waivers bind the claimant on signing (even if payment never arrives), which is why each unconditional form carries the mandatory NOTICE TO CLAIMANT warning.',
    ],
];
