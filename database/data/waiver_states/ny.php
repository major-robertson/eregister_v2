<?php

/*
 * New York: no statutory lien waiver form.
 *
 * N.Y. Lien Law § 34 voids, as against public policy, any contract,
 * agreement, or understanding whereby the right to file or enforce a lien is
 * waived, with the express exception of a written waiver executed and
 * delivered simultaneously with or after payment for the labor or materials.
 * Waiver content and format are otherwise unregulated (no notarization, no
 * typography rules). Lien Law Art. 3-A trust-fund duties are why many NY
 * waivers recite trust language, but that is not a waiver formality. The
 * generic house forms apply.
 */

return [
    'state' => 'NY',
    'state_name' => 'New York',
    'family' => 'generic',
    'advance_waiver_note' => 'N.Y. Lien Law § 34: any contract, agreement, or understanding whereby the right to file or enforce a lien is waived is void as against public policy and wholly unenforceable, except a written waiver executed and delivered simultaneously with or after payment for the labor performed or materials furnished.',
    'ui_notes' => [
        'New York voids advance lien waivers (N.Y. Lien Law § 34). A written waiver is valid only if executed and delivered simultaneously with or after payment. Use a conditional waiver, or hold the unconditional waiver until payment changes hands.',
    ],
    'landing' => [
        'headline' => 'New York Lien Waivers: No Statutory Form, but Advance Waivers Are Void',
        'summary' => 'New York does not prescribe a lien waiver form and leaves content and format unregulated, so our general-purpose conditional and unconditional waivers for progress and final payments apply, with no notarization required. N.Y. Lien Law § 34 voids as against public policy any agreement waiving the right to file or enforce a lien, allowing only a written waiver executed and delivered simultaneously with or after payment, so waivers are effective only to the extent of payment actually made. That timing rule makes conditional waivers the safe default until funds are actually received.',
    ],
];
