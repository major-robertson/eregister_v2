<?php

/*
 * New Mexico: no statutory lien waiver form.
 *
 * The mechanics' and materialmen's lien statute (NMSA 1978, ch. 48, art. 2)
 * is silent on waivers: no prescribed form, no anti-waiver rule, no
 * notarization or sworn-statement requirement. NM courts have enforced lien
 * waivers even where the waiving party was never paid and where no
 * consideration was given, which makes conditional-on-payment language the
 * key claimant protection here. The generic house forms apply.
 */

return [
    'state' => 'NM',
    'state_name' => 'New Mexico',
    'family' => 'generic',
    'advance_waiver_note' => null,
    'landing' => [
        'headline' => 'New Mexico Lien Waivers: No Statutory Form and No Anti-Waiver Statute',
        'summary' => 'New Mexico\'s mechanics\' and materialmen\'s lien statute (NMSA 1978, ch. 48, art. 2) says nothing about lien waivers (there is no prescribed form, no anti-waiver rule, and no notarization requirement), so our general-purpose conditional and unconditional waivers for progress and final payments apply. Because New Mexico courts have enforced waivers even where the signer was never paid and received no consideration, claimants should favor conditional waivers that take effect only when payment actually arrives.',
    ],
];
