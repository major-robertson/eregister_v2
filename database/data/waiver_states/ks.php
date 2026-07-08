<?php

/*
 * Kansas: no general statutory lien waiver form. The mechanic's lien
 * article (K.S.A. 60-1101 et seq.) prescribes no waiver format and waivers
 * need no notarization; the generic house forms apply. (K.S.A. 60-1103b's
 * Judicial Council form governs releases of recorded residential notices of
 * intent to perform, not routine payment waivers.)
 *
 * Kansas Fairness in Private Construction Contract Act, K.S.A. 16-1803(c):
 * a provision in a private construction contract purporting to waive,
 * release, or extinguish mechanic's lien rights is against public policy,
 * void and unenforceable, except a contract may require a waiver or release
 * given in exchange for payment, limited to the amount actually received.
 * The Act does not cover residential projects of four or fewer units
 * (K.S.A. 16-1802).
 */

return [
    'state' => 'KS',
    'state_name' => 'Kansas',
    'family' => 'generic',
    'advance_waiver_note' => 'K.S.A. 16-1803(c) (Kansas Fairness in Private Construction Contract Act): a private construction contract provision purporting to waive, release, or extinguish mechanic\'s lien rights is against public policy, void and unenforceable, except a waiver or release given in exchange for payment, limited to the amount actually received. Residential projects of four or fewer units are outside the Act (K.S.A. 16-1802).',
    'ui_notes' => [
        'On Kansas private commercial projects, contract clauses waiving lien rights are void under K.S.A. 16-1803(c): a waiver holds up only when given in exchange for payment and only to the extent of the amount actually received, so match each waiver\'s amount to the payment it accompanies.',
    ],
    'landing' => [
        'headline' => 'Kansas lien waivers: payment-for-waiver only under the Fairness in Private Construction Contract Act',
        'summary' => 'Kansas prescribes no statutory lien waiver form and requires no notarization, so our general-purpose conditional and unconditional progress and final waivers apply. On covered private construction projects, K.S.A. 16-1803(c) voids any contract provision purporting to waive, release, or extinguish mechanic\'s lien rights: the only waiver a contract may require is one given in exchange for payment, limited to the amount actually received. Residential projects of four or fewer units fall outside the Act.',
    ],
];
