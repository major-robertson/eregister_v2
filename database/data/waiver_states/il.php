<?php

/*
 * Illinois: no statutory lien waiver form. The Mechanics Lien Act
 * (770 ILCS 60) prescribes no waiver format; the generic house forms apply.
 *
 * 770 ILCS 60/1(d) makes an agreement to waive (or subordinate) any right to
 * enforce or claim a lien under the Act against public policy and
 * unenforceable where the agreement is made "in anticipation of and in
 * consideration for the awarding of a contract or subcontract"; i.e.
 * no-lien clauses in the construction contract itself are void (subject to a
 * narrow subordination exception tied to 50% construction-loan disbursement).
 * Separately, 770 ILCS 60/5(a) requires the contractor to give the owner a
 * sworn statement listing all subcontractors, scopes, amounts, and balances
 * before the owner pays any money; Chicago/title-company practice collects
 * the sworn statement plus waivers at every draw.
 */

return [
    'state' => 'IL',
    'state_name' => 'Illinois',
    'family' => 'generic',
    'advance_waiver_note' => '770 ILCS 60/1(d): an agreement to waive or subordinate lien rights under the Illinois Mechanics Lien Act is against public policy and unenforceable where it is made in anticipation of and in consideration for the awarding of a contract or subcontract; no-lien clauses in the construction contract itself are void.',
    'ui_notes' => [
        'Illinois voids lien-waiver clauses agreed to in anticipation of and in consideration for the award of a contract or subcontract (770 ILCS 60/1(d)). Exchange waivers for payment as the work progresses instead of waiving in the contract. Owners and title companies also typically require a contractor\'s sworn statement of subcontractors and balances (770 ILCS 60/5) with the waivers at each draw.',
    ],
    'landing' => [
        'headline' => 'Illinois lien waivers: draw-package practice with no prescribed statutory form',
        'summary' => 'Illinois does not prescribe a statutory lien waiver form: the Mechanics Lien Act (770 ILCS 60) leaves the format to the parties, so our general-purpose conditional and unconditional progress and final waivers apply. What Illinois does regulate is advance waivers: under 770 ILCS 60/1(d), an agreement waiving lien rights made in anticipation of and in consideration for the award of a contract or subcontract is against public policy and unenforceable. In practice, owners and title companies pair waivers with the contractor\'s sworn statement required by 770 ILCS 60/5 at every payment draw.',
    ],
];
