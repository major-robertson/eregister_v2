<?php

/*
 * South Carolina: no statutory lien waiver form; no notarization
 * requirement (waivers need only be in writing). The generic house forms
 * apply.
 *
 * S.C. Code § 29-7-20: an agreement to waive the right to file or claim a
 * lien for labor and materials is against public policy and unenforceable
 * unless payment substantially equal to the amount waived is actually
 * made. The same section makes it a criminal offense (fine up to $5,000
 * or up to 60 days) for a contractor to falsely certify that laborers or
 * materialmen have been paid.
 */

return [
    'state' => 'SC',
    'state_name' => 'South Carolina',
    'family' => 'generic',
    'advance_waiver_note' => 'S.C. Code § 29-7-20: an agreement to waive the right to file or claim a lien for labor and materials is against public policy and is unenforceable unless payment substantially equal to the amount waived is actually made. Waivers signed before payment, including in the construction contract, are unenforceable.',
    'ui_notes' => [
        'South Carolina makes lien waivers unenforceable unless payment substantially equal to the amount waived is actually made (S.C. Code § 29-7-20). Exchange each waiver for the payment it covers rather than waiving in the contract. The same section makes falsely certifying that laborers or materialmen have been paid a criminal offense.',
    ],
    'landing' => [
        'headline' => 'South Carolina lien waivers: enforceable only against payment actually made',
        'summary' => 'South Carolina does not prescribe a statutory lien waiver form: waivers need only be in writing, so our general-purpose conditional and unconditional progress and final waivers apply. The state does police timing: under S.C. Code § 29-7-20, an agreement to waive lien rights is against public policy and unenforceable unless payment substantially equal to the amount waived is actually made, which nullifies contract-stage advance waivers. The same section criminalizes a contractor falsely certifying that laborers and materialmen have been paid.',
    ],
];
