<?php

/*
 * Tennessee: no statutory lien waiver form.
 *
 * Tenn. Code Ann. § 66-11-124(b) voids, as against public policy, any
 * contract provision that purports to waive any right of lien under the lien
 * chapter, with unusually strict enforcement: a person solicited to sign
 * such a contract may report it to the State Board for Licensing Contractors,
 * which can discipline the offending contractor. Two adjacent nuances:
 * § 66-11-124(a): accepting a note or other evidence of debt is NOT a lien
 * waiver unless it is received as payment and expressly declared to be a
 * waiver; § 66-11-124(c): an owner can bar remote contractors' liens by
 * recording a payment bond equal to 100% of the prime contract price before
 * work begins. No form, typography, or notarization requirements; a waiver
 * must simply be express, in writing, with clear intent to waive. The generic
 * house forms apply.
 */

return [
    'state' => 'TN',
    'state_name' => 'Tennessee',
    'family' => 'generic',
    'advance_waiver_note' => 'Tenn. Code Ann. § 66-11-124(b): any contract provision that purports to waive any right of lien under the lien chapter is void and unenforceable as against the public policy of Tennessee. A person solicited to sign such a contract may report it to the State Board for Licensing Contractors. Standalone waivers given in exchange for payment for work already furnished remain the valid practice, and accepting a note or other evidence of debt is not a waiver unless received as payment and expressly declared to be one (§ 66-11-124(a)).',
    'ui_notes' => [
        'Tennessee voids contract clauses that purport to waive lien rights (Tenn. Code Ann. § 66-11-124(b)). Waive only through a separate written waiver given in exchange for payment after the work has been furnished. Accepting a note or check is not a waiver unless it is received as payment and expressly declared one (§ 66-11-124(a)).',
    ],
    'landing' => [
        'headline' => 'Tennessee Lien Waivers: No Statutory Form, but Contract Waiver Clauses Are Void',
        'summary' => 'Tennessee prescribes no lien waiver form and requires no notarization, so our general-purpose conditional and unconditional waivers for progress and final payments apply; a waiver simply must be express, in writing, and show clear intent to waive. Tenn. Code Ann. § 66-11-124(b) voids as against public policy any contract provision purporting to waive lien rights, with enforcement strict enough that soliciting such a clause can be reported to the State Board for Licensing Contractors. Standalone waivers signed in exchange for payment for work already furnished remain the normal, valid practice, and accepting a note or other evidence of debt is not a waiver unless received as payment and expressly declared to be one.',
    ],
];
