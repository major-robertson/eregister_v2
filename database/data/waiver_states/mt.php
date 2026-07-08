<?php

/*
 * Montana: no statutory lien waiver form.
 *
 * Mont. Code Ann. § 28-2-723 ("Construction contracts requiring lien or bond
 * waiver void") forbids construction contract provisions requiring a
 * contractor, subcontractor, or material supplier to waive construction lien
 * rights or payment bond claim rights before the party has been paid. The
 * anti-waiver rule lives in the contracts title (Title 28), not the
 * construction lien act (Title 71, ch. 3, part 5). No form, typography, or
 * notarization requirements: the generic house forms apply.
 */

return [
    'state' => 'MT',
    'state_name' => 'Montana',
    'family' => 'generic',
    'advance_waiver_note' => 'Mont. Code Ann. § 28-2-723: a construction contract may not contain provisions requiring a contractor, subcontractor, or material supplier to waive construction lien rights or payment bond claim rights before the party has been paid for the labor or materials furnished; such provisions are void.',
    'ui_notes' => [
        'Montana voids contract clauses requiring lien or payment bond rights to be waived before payment (Mont. Code Ann. § 28-2-723). Use a conditional waiver until payment is actually received.',
    ],
    'landing' => [
        'headline' => 'Montana Lien Waivers: No Statutory Form, and Pre-Payment Waiver Clauses Are Void',
        'summary' => 'Montana does not prescribe a statutory lien waiver form and imposes no format, content, or notarization requirements, so our general-purpose conditional and unconditional waivers for progress and final payments apply. Mont. Code Ann. § 28-2-723 voids construction contract provisions that require a contractor, subcontractor, or material supplier to waive lien or payment bond rights before being paid, so conditional waivers are the safe pattern until payment lands. After payment, a waiver is valid when it clearly states the amount waived, identifies the property, and is signed by the waiving party.',
    ],
];
