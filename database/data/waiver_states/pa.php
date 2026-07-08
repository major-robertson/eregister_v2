<?php

/*
 * Pennsylvania: no statutory lien waiver form; no notarization
 * requirement. The generic house forms apply.
 *
 * 49 P.S. § 1401 (Mechanics' Lien Law of 1963, as amended by Act 52 of
 * 2006) splits by project type. Residential property: a contractor or
 * subcontractor may waive the right to file a claim by a written
 * instrument signed by him or by conduct operating equitably to estop the
 * filing (§ 1401(a)). Nonresidential buildings: a contractor's waiver is
 * against public policy, unlawful and void unless given in consideration
 * for payment and only to the extent that payment is actually received
 * (§ 1401(b)(1)); a subcontractor's waiver is void on the same terms
 * unless the contractor has posted a bond guaranteeing payment for labor
 * and materials provided by subcontractors (§ 1401(b)(2)). To bind
 * subcontractors on residential work, the stipulation against liens must
 * be noticed or filed with the prothonotary per 49 P.S. § 1402.
 */

return [
    'state' => 'PA',
    'state_name' => 'Pennsylvania',
    'family' => 'generic',
    'advance_waiver_note' => '49 P.S. § 1401: on nonresidential buildings, a contractor\'s waiver of lien rights is against public policy, unlawful and void unless given in consideration for payment and only to the extent that payment is actually received; a subcontractor\'s waiver is void on the same terms unless the contractor has posted a bond guaranteeing payment to subcontractors. On residential property, lien rights may be waived by a signed written instrument (or by estoppel conduct).',
    'ui_notes' => [
        'Pennsylvania voids advance lien waivers on nonresidential projects (49 P.S. § 1401): a waiver is only effective to the extent payment is actually received, or, for subcontractors, where the contractor has posted a payment bond. Exchange waivers for actual payment as the work progresses. Residential property is the exception, where lien rights can be waived by a signed written instrument.',
    ],
    'landing' => [
        'headline' => 'Pennsylvania lien waivers: payment-tied by statute on nonresidential projects',
        'summary' => 'Pennsylvania does not prescribe a statutory lien waiver form, so our general-purpose conditional and unconditional progress and final waivers apply. What the Mechanics\' Lien Law does regulate is timing: on nonresidential buildings, 49 P.S. § 1401 makes a waiver of lien rights void unless given in consideration for payment and only to the extent payment is actually received (for subcontractors, unless the contractor has bonded off payment). Residential property is different: there, lien rights may be waived by a signed written instrument, and a stipulation against liens can bind subcontractors if noticed or filed per 49 P.S. § 1402.',
    ],
];
