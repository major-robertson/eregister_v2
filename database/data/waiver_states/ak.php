<?php

/*
 * Alaska: no statutory lien waiver form.
 *
 * Alaska prescribes no waiver form and requires no notarization, so the
 * generic house forms apply. Two statutory guardrails: (1) AS 34.35.117(a)
 * makes a signed written waiver of lien or stop-lending-notice rights valid
 * without consideration, but the waiver "may not relate to labor, materials,
 * services, or equipment furnished after the date the waiver is signed by
 * the claimant": advance waivers of future work are void; (2) AS
 * 34.35.117(b) provides that an individual described in AS 34.35.120(10)
 * (wage-earning laborers/employees) may not waive the right to claim a lien
 * at all, and a waiver purporting to waive that individual's or class's
 * rights is void. Statute text verified against FindLaw's AS 34.35.117
 * (checked July 2026).
 */

return [
    'state' => 'AK',
    'state_name' => 'Alaska',
    'family' => 'generic',
    'advance_waiver_note' => 'AS 34.35.117 makes a signed written waiver of lien or stop-lending-notice rights valid without consideration, but the waiver may not relate to labor, materials, services, or equipment furnished after the date the waiver is signed: advance waivers of lien rights for future work are void. Separately, an individual described in AS 34.35.120(10) (wage-earning laborers and employees) may not waive the right to claim a lien at all; a waiver purporting to waive that individual\'s or class\'s rights is void.',
    'ui_notes' => [
        'Alaska limits waiver scope by statute: under AS 34.35.117 a lien waiver can only cover labor, materials, services, or equipment furnished on or before the date it is signed (waivers of future work are void), and wage-earning laborers and employees described in AS 34.35.120(10) cannot waive their lien rights at all.',
    ],
    'landing' => [
        'headline' => 'Alaska Lien Waivers: No Statutory Form, No Waiving Future Work',
        'summary' => 'Alaska does not prescribe a lien waiver form, so our general-purpose conditional and unconditional progress and final waiver forms apply, with no notarization required. Under AS 34.35.117 a signed written waiver is valid even without consideration, but it may not relate to labor, materials, services, or equipment furnished after the date it is signed: advance waivers of future lien rights are void. Wage-earning laborers and employees described in AS 34.35.120(10) cannot waive their lien rights at all.',
    ],
];
