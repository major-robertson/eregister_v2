<?php

/*
 * New Jersey: no statutory lien waiver form.
 *
 * The Construction Lien Law prescribes statutory forms for the lien claim
 * itself (N.J.S.A. 2A:44A-8) and the residential Notice of Unpaid Balance,
 * but NOT for waivers. N.J.S.A. 2A:44A-38 makes waivers of construction lien
 * rights against public policy, unlawful, and void unless given in
 * consideration for payment, and even then effective only upon and to the
 * extent the payment is actually received, so every private-project NJ
 * waiver is conditional-on-payment as a matter of law. No notarization or
 * typography requirements; the generic house forms apply.
 */

return [
    'state' => 'NJ',
    'state_name' => 'New Jersey',
    'family' => 'generic',
    'advance_waiver_note' => 'N.J.S.A. 2A:44A-38: waivers of construction lien rights are against public policy, unlawful, and void unless given in consideration for payment for the work, services, materials, or equipment, and even then they are effective only upon and to the extent that the payment is actually received. Advance waivers and no-lien contract clauses are unenforceable on private projects.',
    'ui_notes' => [
        'New Jersey waivers are effective only upon and to the extent payment is actually received (N.J.S.A. 2A:44A-38). Every NJ construction lien waiver operates as conditional-on-payment by law, and advance or no-lien contract clauses are void on private projects.',
    ],
    'landing' => [
        'headline' => 'New Jersey Lien Waivers: No Statutory Form; Waivers Bind Only to the Extent of Payment',
        'summary' => 'New Jersey\'s Construction Lien Law prescribes statutory forms for lien claims but none for lien waivers, so our general-purpose conditional and unconditional waivers for progress and final payments apply, with no notarization or witness required. Under N.J.S.A. 2A:44A-38, a waiver of construction lien rights is void unless given in consideration for payment, and it takes effect only upon and to the extent the payment is actually received. Every New Jersey waiver is conditional on payment as a matter of law. Advance waivers and no-lien clauses in construction contracts are unenforceable on private projects.',
    ],
];
