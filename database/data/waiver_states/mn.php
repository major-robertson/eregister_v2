<?php

/*
 * Minnesota: no statutory lien waiver form.
 *
 * Minn. Stat. § 337.10, subd. 2 voids provisions contained in, or executed in
 * connection with, a building and construction contract that require a
 * contractor, subcontractor, or material supplier to waive mechanics lien or
 * payment bond rights before the person has been paid, but the waiver can
 * still bind as to a third party who detrimentally relies on it. No form,
 * typography, or notarization requirements: the generic house forms apply.
 */

return [
    'state' => 'MN',
    'state_name' => 'Minnesota',
    'family' => 'generic',
    'advance_waiver_note' => 'Minn. Stat. § 337.10, subd. 2: provisions contained in, or executed in connection with, a building and construction contract requiring a contractor, subcontractor, or material supplier to waive mechanics lien or payment bond rights before the person has been paid are void and unenforceable, though such a waiver may still be valid as to a third party who detrimentally relies on it.',
    'ui_notes' => [
        'Minnesota voids waivers of lien or payment bond rights signed before payment (Minn. Stat. § 337.10, subd. 2). Use a conditional waiver until payment is actually in hand, and remember an unpaid waiver can still bind against a third party who detrimentally relied on it.',
    ],
    'landing' => [
        'headline' => 'Minnesota Lien Waivers: No Statutory Form, but Pre-Payment Waivers Are Void',
        'summary' => 'Minnesota does not prescribe a statutory lien waiver form, so our general-purpose conditional and unconditional waivers for progress and final payments apply, with no notarization or witness required. Minn. Stat. § 337.10, subd. 2 voids any provision requiring a contractor, subcontractor, or material supplier to waive mechanics lien or payment bond rights before being paid, which makes conditional waivers the safe choice until payment actually arrives. Note the statutory exception: an unpaid waiver may still be enforced by a third party who detrimentally relied on it.',
    ],
];
