<?php

/*
 * South Dakota: no general statutory lien waiver form. The mechanics'
 * lien chapter (SDCL ch. 44-9) is silent on waivers, so the generic house
 * forms apply.
 *
 * There is no express anti-waiver statute, but South Dakota has a
 * dedicated waiver chapter, "Construction Lien Waiver Agreements"
 * (SDCL 44-9A-1 to 44-9A-3), that frames waiver as tied to actual
 * payment: full or partial waiver by endorsement of a joint check (payees
 * = contractor/sub plus supplier) together with a separate written waiver
 * agreement between the check's maker and the waiving party, with a
 * conspicuous provision on the check's reverse side referencing the
 * agreement and stating that the payees, by endorsement and in
 * consideration of the payment, waive the claims covered. SD Supreme
 * Court dicta suggests waivers should have some connection to payment.
 */

return [
    'state' => 'SD',
    'state_name' => 'South Dakota',
    'family' => 'generic',
    'advance_waiver_note' => 'SDCL ch. 44-9 contains no express prohibition on advance lien waivers, but South Dakota\'s Construction Lien Waiver Agreements chapter (SDCL 44-9A-1 to 44-9A-3) frames waiver as occurring through endorsement of a joint check together with a separate written waiver agreement referenced on the check (i.e., tied to actual payment).',
    'ui_notes' => [
        'South Dakota has no anti-waiver statute, but its statutory waiver mechanism (SDCL ch. 44-9A) ties waivers to actual payment: a joint check endorsed by the payees plus a separate written waiver agreement referenced conspicuously on the check\'s reverse side. Tie each waiver to a payment actually received rather than waiving in the contract.',
    ],
    'landing' => [
        'headline' => 'South Dakota lien waivers: no prescribed form, with a payment-tied joint-check statute',
        'summary' => 'South Dakota does not prescribe a general statutory lien waiver form: SDCL chapter 44-9 says nothing about waiver form or content, so our general-purpose conditional and unconditional progress and final waivers apply. While there is no express anti-waiver statute, the Construction Lien Waiver Agreements chapter (SDCL ch. 44-9A) ties its statutory waiver mechanism to actual payment: a joint check endorsed by the payees together with a separate written waiver agreement referenced on the check. Keeping waivers connected to payment actually received is therefore the prudent South Dakota pattern.',
    ],
];
