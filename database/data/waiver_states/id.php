<?php

/*
 * Idaho: no statutory lien waiver form; contract law governs.
 *
 * The mechanics' lien chapter (Idaho Code §§ 45-501 to 45-525) does not
 * regulate lien waivers at all (no forms, no conditional/unconditional
 * distinction, no notarization or sworn-statement requirement), and Idaho
 * has no statute voiding no-lien clauses, so waivers are ordinary contracts
 * and the generic house forms apply. Idaho case law tempers the freedom:
 * a waiver of lien rights will not be presumed or implied (the intent to
 * waive must clearly appear), and courts have declined to enforce advance
 * (pre-payment) waivers lacking consideration. Because a clear,
 * consideration-backed unconditional pre-payment waiver may still be
 * enforced, conditional waivers keyed to receipt of payment are the safe
 * default. Rules sourced from the condensed 50-state research (Levelset
 * secondary sources); the lien chapter's silence on waivers is the
 * operative statutory fact. No ui_notes: no notable waiver rule to banner.
 */

return [
    'state' => 'ID',
    'state_name' => 'Idaho',
    'family' => 'generic',
    'advance_waiver_note' => 'Idaho has no anti-waiver statute: the mechanics\' lien chapter (Idaho Code §§ 45-501 to 45-525) does not regulate lien waivers at all, and no Idaho statute voids no-lien clauses, so waivers are governed by ordinary contract law. Idaho courts will not presume or imply a waiver of lien rights: the intent to waive must clearly appear, and courts have declined to enforce advance, pre-payment waivers lacking consideration.',
    'landing' => [
        'headline' => 'Idaho Lien Waivers: No Statutory Form, Contract Law Governs',
        'summary' => 'Idaho does not prescribe a lien waiver form: the mechanics\' lien chapter (Idaho Code §§ 45-501 to 45-525) does not regulate waivers at all, so lien waivers are ordinary contracts and our general-purpose conditional and unconditional progress and final waiver forms apply, with no notarization required. There is no anti-waiver statute, but Idaho courts will not presume a waiver: the intent to waive must clearly appear, and advance pre-payment waivers unsupported by consideration have been refused enforcement. Conditional waivers keyed to receipt of payment remain the safe default.',
    ],
];
