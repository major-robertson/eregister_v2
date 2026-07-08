<?php

/*
 * Rhode Island: no statutory lien waiver form; no notarization
 * requirement. The generic house forms apply.
 *
 * R.I. Gen. Laws § 34-28-1(b) makes any covenant, promise, agreement or
 * understanding in, in connection with, or collateral to a construction
 * contract purporting to bar the filing of a notice of intention or the
 * taking of any steps to enforce a lien against public policy, void and
 * unenforceable. The single statutory exception (mirroring NY Lien Law
 * § 34): a written waiver executed and delivered by the contractor,
 * subcontractor, material supplier, or laborer simultaneously with or
 * after payment for the labor performed or materials furnished. The
 * recorded notice of intention under § 34-28-4 is the lien-preservation
 * step this anti-waiver protection attaches to.
 */

return [
    'state' => 'RI',
    'state_name' => 'Rhode Island',
    'family' => 'generic',
    'advance_waiver_note' => 'R.I. Gen. Laws § 34-28-1(b): any covenant, promise, agreement or understanding in, in connection with, or collateral to a construction contract purporting to bar the filing of a notice of intention or the taking of any steps to enforce a lien is against public policy, void and unenforceable, except a written waiver executed and delivered simultaneously with or after payment for the labor performed or materials furnished.',
    'ui_notes' => [
        'Rhode Island voids advance lien waivers (R.I. Gen. Laws § 34-28-1(b)): a waiver is only valid if written, executed, and delivered simultaneously with or after the payment it covers. Exchange each waiver at or after payment rather than waiving in the contract, and never waive the right to record the § 34-28-4 notice of intention up front.',
    ],
    'landing' => [
        'headline' => 'Rhode Island lien waivers: advance waivers are void, payment-time waivers are valid',
        'summary' => 'Rhode Island does not prescribe a statutory lien waiver form, so our general-purpose conditional and unconditional progress and final waivers apply. Timing, however, is regulated: R.I. Gen. Laws § 34-28-1(b) voids any agreement in or collateral to a construction contract that bars filing a notice of intention or enforcing a lien, as against public policy. The one exception is a written waiver executed and delivered simultaneously with or after payment for the labor or materials it covers, so waivers should be exchanged payment by payment, not signed up front.',
    ],
];
