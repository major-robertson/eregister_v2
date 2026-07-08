<?php

/*
 * Arizona: A.R.S. § 33-1008 (Waiver of lien).
 *
 * Subsection (D) prescribes four waiver-and-release forms (conditional and
 * unconditional x progress and final) and makes any claimant waiver
 * "unenforceable unless it follows substantially the following forms in the
 * following circumstances." Subsection (A) additionally requires, for a
 * conditional release, evidence of payment (bank-paid endorsed check or the
 * claimant's written acknowledgment of payment). No notarization or witness
 * appears anywhere in the statute; both unconditional forms must carry the
 * statutory NOTICE "in type at least as large as the largest type otherwise
 * on the document." Statutory text verified against azleg.gov (July 2026);
 * no amendments to § 33-1008 found for 2019-2026.
 */

return [
    'state' => 'AZ',
    'state_name' => 'Arizona',
    'family' => 'statutory_four',
    'statute' => 'A.R.S. § 33-1008',
    'compliance_standard' => 'substantial',
    'advance_waiver_note' => 'A.R.S. § 33-1008(A)-(B): a contract term purporting to waive or impair the claims or liens of other persons in advance is void, and no oral or written statement waives or impairs a claim unless it follows a statutory waiver-and-release form or the claimant has actually been paid in full.',
    'ui_notes' => [
        'Arizona\'s unconditional forms bind the claimant once signed, even if payment is never received: the statutory NOTICE says so on the form. Use a conditional form until the check has actually been paid by the bank.',
        'Do not add a notary block or extra terms: A.R.S. § 33-1008 requires no notarization or witness, and departing from the statutory text risks making the waiver unenforceable.',
        'Conditional waivers only bind on evidence of payment: the claimant\'s endorsement on a bank-paid check or a written acknowledgment of payment (A.R.S. § 33-1008(A)).',
    ],
    'kinds' => [
        'conditional_progress' => [
            'template' => 'documents.lien.waivers.bodies.az-conditional-progress',
            'title' => 'Conditional Waiver and Release on Progress Payment',
            'template_version' => 1,
        ],
        'unconditional_progress' => [
            'template' => 'documents.lien.waivers.bodies.az-unconditional-progress',
            'title' => 'Unconditional Waiver and Release on Progress Payment',
            'template_version' => 1,
        ],
        'conditional_final' => [
            'template' => 'documents.lien.waivers.bodies.az-conditional-final',
            'title' => 'Conditional Waiver and Release on Final Payment',
            'template_version' => 1,
        ],
        'unconditional_final' => [
            'template' => 'documents.lien.waivers.bodies.az-unconditional-final',
            'title' => 'Unconditional Waiver and Release on Final Payment',
            'template_version' => 1,
        ],
    ],
    'landing' => [
        'headline' => 'Arizona Lien Waivers: The Four Statutory Forms Under A.R.S. § 33-1008',
        'summary' => 'Arizona prescribes four lien waiver forms (conditional and unconditional versions for both progress and final payments), and a waiver is unenforceable unless it substantially follows the statutory text of A.R.S. § 33-1008(D). Conditional waivers become effective only when the identified check has been endorsed and paid by the bank, while unconditional waivers bind the claimant on signing even if payment never arrives, which is why the statute requires a prominent warning notice on every unconditional form. No notarization or witness is required, and each form releases mechanic\'s lien rights, state and federal statutory bond rights, private bond rights, and related payment claims.',
    ],
];
