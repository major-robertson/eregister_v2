<?php

/*
 * West Virginia: no statutory lien waiver form.
 *
 * W. Va. Code ch. 38, art. 2 is silent on lien waivers: no prescribed form,
 * no anti-waiver statute, no notarization or sworn-statement requirement for
 * waivers (the sworn/recorded formalities in §§ 38-2-8 to 38-2-13 attach to
 * lien NOTICES, not waivers). No-lien clauses in job contracts are generally
 * considered permissible and some WV courts have acknowledged the
 * enforceability of advance contract waivers, so overbroad waiver language
 * will likely be enforced as written, and waivers should be scoped tightly.
 * (§ 38-2-37 concerns compelling release of a recorded lien, not waivers.)
 * A written, signed document with clear waiver intent suffices. The generic
 * house forms apply.
 */

return [
    'state' => 'WV',
    'state_name' => 'West Virginia',
    'family' => 'generic',
    'advance_waiver_note' => null,
    'ui_notes' => [
        'West Virginia has no anti-waiver statute: no-lien clauses and advance waivers in contracts are generally enforceable, and overbroad waiver language will likely be enforced as written. Scan your contract for no-lien clauses and keep every waiver scoped to the payment actually received.',
    ],
    'landing' => [
        'headline' => 'West Virginia Lien Waivers: No Statutory Form or Anti-Waiver Statute',
        'summary' => 'West Virginia\'s mechanics lien article (W. Va. Code ch. 38, art. 2) prescribes no waiver form and contains no anti-waiver provision, so our general-purpose conditional and unconditional waivers for progress and final payments apply; a written, signed document with clear intent to waive suffices, and no notarization is required (the sworn, recorded formalities apply to lien notices, not waivers). Because the law is permissive, contract no-lien clauses and advance waivers are generally considered enforceable, and an overbroad waiver will likely be enforced as written. That makes tightly scoped, conditional waivers the safe pattern until payment is actually received.',
    ],
];
