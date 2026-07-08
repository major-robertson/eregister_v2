<?php

/*
 * Delaware: no statutory lien waiver form; advance waivers void.
 *
 * Delaware prescribes no waiver form, so the generic house forms apply.
 * 25 Del. C. § 2706(b) makes any contract, agreement, or understanding
 * whereby the right to file or enforce a mechanics' lien is waived "void as
 * against public policy and wholly unenforceable," with two carve-outs:
 * (1) a written waiver executed and delivered simultaneously with or after
 * payment for the labor or materials has been made to the waiving
 * contractor, subcontractor, supplier, or laborer; and (2) a written
 * agreement to subordinate, release, or satisfy a lien made after a claim
 * statement has been filed under the chapter. § 2706(a) separately provides
 * that taking notes or other securities is not a waiver unless expressly
 * received as payment or waiver. Waivers must therefore be
 * payment-contemporaneous or post-payment; no notarization or
 * sworn-statement requirement in 25 Del. C. ch. 27. Statute text verified
 * against delcode.delaware.gov (checked July 2026).
 */

return [
    'state' => 'DE',
    'state_name' => 'Delaware',
    'family' => 'generic',
    'advance_waiver_note' => '25 Del. C. § 2706(b) makes any contract, agreement, or understanding waiving the right to file or enforce a mechanics\' lien void as against public policy and wholly unenforceable, with two carve-outs: a written waiver executed and delivered simultaneously with or after payment for the labor or materials, and a written agreement to subordinate, release, or satisfy a lien made after a claim statement has been filed. Delaware waivers must therefore be payment-contemporaneous or post-payment.',
    'ui_notes' => [
        '25 Del. C. § 2706 voids advance lien waivers as against public policy: a Delaware waiver is enforceable only if executed and delivered simultaneously with or after payment. Send conditional waivers while payment is pending, and sign and date unconditional waivers only at or after payment.',
    ],
    'landing' => [
        'headline' => 'Delaware Lien Waivers: No Statutory Form, Advance Waivers Void',
        'summary' => 'Delaware does not prescribe a lien waiver form, so our general-purpose conditional and unconditional progress and final waiver forms apply, with no notarization required. Under 25 Del. C. § 2706, any agreement waiving mechanics\' lien rights is void as against public policy unless the written waiver is executed and delivered simultaneously with or after payment for the labor or materials (or subordinates, releases, or satisfies a lien after a claim has been filed). Conditional waivers are the safe pattern before funds clear; unconditional waivers should be signed and dated at or after payment.',
    ],
];
