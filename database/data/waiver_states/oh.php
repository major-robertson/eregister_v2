<?php

/*
 * Ohio: no statutory lien waiver form. The mechanics' lien chapter
 * (R.C. ch. 1311) is silent on waivers, so the generic house forms apply.
 *
 * Ohio has no anti-waiver statute and follows freedom of contract: lien
 * rights may be waived in advance by contract, even before furnishing
 * labor or materials. No notarization requirement. Adjacent machinery
 * (not a waiver formality): on home construction contracts a lending
 * institution may not pay the original contractor until it receives the
 * contractor's affidavit that subs, suppliers, and laborers have been
 * paid, except claims specifically identified (R.C. 1311.011(B)(4)).
 */

return [
    'state' => 'OH',
    'state_name' => 'Ohio',
    'family' => 'generic',
    'advance_waiver_note' => null,
    'landing' => [
        'headline' => 'Ohio lien waivers: no prescribed form, and advance waivers are enforceable',
        'summary' => 'Ohio does not prescribe a statutory lien waiver form: the mechanics\' lien chapter (R.C. ch. 1311) leaves waiver form and content to the parties, so our general-purpose conditional and unconditional progress and final waivers apply. Ohio also has no anti-waiver statute: courts enforce freedom of contract, so lien rights can be waived in advance, even in the construction contract before work begins. Claimants should therefore favor conditional waivers that take effect only when payment is actually received.',
    ],
];
