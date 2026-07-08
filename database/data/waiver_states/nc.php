<?php

/*
 * North Carolina: no statutory lien waiver form.
 *
 * Two anti-waiver statutes: (1) N.C. Gen. Stat. § 44A-12(f): an agreement to
 * waive the right to file a claim of lien on real property, or to serve a
 * notice of claim of lien upon funds, made in anticipation of and in
 * consideration for the awarding of the contract, is against public policy
 * and unenforceable. (2) N.C. Gen. Stat. § 22B-5 (S.L. 2022-1, s. 3(a)),
 * "Waiver of liens or claims as a condition of progress payment invalid":
 * requiring a lien/claim waiver as a condition of an interim or progress
 * payment is void and unenforceable unless the waiver is limited to the
 * specific interim or progress payment actually received; the rule does not
 * apply to final-payment waivers or to written settlements of disputed
 * claims identified in writing by the claimant (verified against ncleg.gov,
 * July 2026). No form, typography, or notarization requirements; the
 * generic house forms apply.
 */

return [
    'state' => 'NC',
    'state_name' => 'North Carolina',
    'family' => 'generic',
    'advance_waiver_note' => 'N.C. Gen. Stat. § 44A-12(f): an agreement to waive the right to file a claim of lien on real property, or to serve a notice of claim of lien upon funds, made in anticipation of and in consideration for the awarding of the contract is against public policy and unenforceable. N.C. Gen. Stat. § 22B-5 additionally voids any requirement that a waiver be furnished as a condition of an interim or progress payment unless the waiver is limited to the specific interim or progress payment actually received (final-payment waivers and written settlements of identified disputed claims are excepted).',
    'ui_notes' => [
        'North Carolina voids contract-stage advance lien waivers (N.C. Gen. Stat. § 44A-12(f)), and progress-payment waivers are unenforceable unless limited to the specific payment actually received (N.C. Gen. Stat. § 22B-5). Keep progress waivers scoped to the payment they cover; final-payment waivers are exempt from the § 22B-5 limit.',
    ],
    'landing' => [
        'headline' => 'North Carolina Lien Waivers: No Statutory Form, but Broad Progress Waivers Are Void',
        'summary' => 'North Carolina does not prescribe a lien waiver form and leaves content unregulated, so our general-purpose conditional and unconditional waivers for progress and final payments apply, with no notarization required. Advance waivers made in anticipation of and in consideration for the awarding of the contract are unenforceable under N.C. Gen. Stat. § 44A-12(f), and under N.C. Gen. Stat. § 22B-5 a waiver required as a condition of a progress payment is void unless limited to the specific payment actually received. Final-payment waivers and written settlements of disputed claims identified by the claimant sit outside the § 22B-5 restriction.',
    ],
];
