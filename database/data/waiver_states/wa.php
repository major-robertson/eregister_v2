<?php

/*
 * Washington: no statutory lien waiver form.
 *
 * RCW ch. 60.04 prescribes the claim-of-lien form (RCW 60.04.091) but no
 * waiver form, and contains no anti-waiver provision: advance waivers and
 * contract no-lien clauses can be given effect under the common-law rule if
 * proof of waiver is clear, so conditioning waivers on actual receipt of
 * payment is the practitioner-recommended pattern. Two adjacent rules:
 * RCW 60.04.071: upon payment and acceptance of the amount due and demand of
 * the payer, the claimant must "immediately" execute and deliver a release of
 * the paid lien rights (a court can compel delivery and award costs,
 * attorney fees, and damages for unjustified delay); RCW 60.04.035: coercion
 * to discourage giving the notice of right to claim a lien or filing a lien
 * claim is a Consumer Protection Act violation. No notarization for waivers.
 * The generic house forms apply.
 */

return [
    'state' => 'WA',
    'state_name' => 'Washington',
    'family' => 'generic',
    'advance_waiver_note' => null,
    'ui_notes' => [
        'Washington has no anti-waiver statute: advance waivers and contract no-lien clauses can be enforced, so review your contract and use conditional waivers until payment clears. Once paid and a release is demanded, RCW 60.04.071 requires you to immediately execute and deliver a release of the paid lien rights; courts can compel delivery and award fees and damages for unjustified delay.',
    ],
    'landing' => [
        'headline' => 'Washington Lien Waivers: No Statutory Form and No Anti-Waiver Statute',
        'summary' => 'Washington prescribes no lien waiver form (RCW 60.04.091 dictates the claim-of-lien form, not waivers) and requires no notarization, so our general-purpose conditional and unconditional waivers for progress and final payments apply. RCW ch. 60.04 contains no anti-waiver provision, meaning advance waivers and contract no-lien clauses can be given effect, which is why Washington practitioners recommend conditioning any waiver on actual receipt of payment. Once a claimant has been paid and the payer demands it, RCW 60.04.071 requires the claimant to immediately execute and deliver a release of the paid lien rights, with courts empowered to compel delivery and award costs, attorney fees, and damages for unjustified delay.',
    ],
];
