<?php

/*
 * Louisiana: no statutory lien waiver form. The Private Works Act
 * (La. R.S. 9:4801 et seq., comprehensively revised effective Jan. 1, 2020)
 * prescribes no waiver format, defines no conditional/unconditional
 * framework, contains no anti-waiver section, and requires no notarization
 * (an ordinary signed waiver suffices; no authentic act needed). The
 * generic house forms apply.
 *
 * Advance waivers are effectively invalid under Louisiana case law rather
 * than statute: waiver requires an existing right, knowledge of its
 * existence, and an actual intention to relinquish it, so courts decline to
 * enforce waivers of privileges signed before the work is performed. Note
 * the vocabulary: the Louisiana encumbrance is a "privilege," asserted by
 * a statement of claim or privilege; owners and lenders commonly also ask
 * for a clear lien certificate from the clerk of court before final payment
 * (custom, not statute).
 */

return [
    'state' => 'LA',
    'state_name' => 'Louisiana',
    'family' => 'generic',
    'advance_waiver_note' => 'The Private Works Act (La. R.S. 9:4801 et seq.) has no anti-waiver statute, but Louisiana case law makes advance waivers effectively unenforceable: waiver requires an existing right, knowledge of its existence, and an actual intention to relinquish it, so a waiver of privileges signed before the work is performed waives nothing.',
    'ui_notes' => [
        'Louisiana has no anti-waiver statute, but its courts require an existing right and an actual intent to relinquish it before enforcing a waiver; waivers of privileges signed before the work is performed are effectively unenforceable, so exchange waivers at payment, not up front. Owners and lenders often also request a clear lien certificate from the clerk of court before final payment.',
    ],
    'landing' => [
        'headline' => 'Louisiana lien waivers: privilege waivers under the Private Works Act, no prescribed form',
        'summary' => 'Louisiana prescribes no lien waiver form: the Private Works Act (La. R.S. 9:4801 et seq.) is silent on waiver format and requires no notarization, so our general-purpose conditional and unconditional progress and final waivers apply (Louisiana calls the underlying encumbrance a privilege). While no statute bans advance waivers, Louisiana case law requires an existing right and actual intent to relinquish it, making waivers signed before the work is performed effectively unenforceable. Custom adds one more step at closeout: owners and lenders commonly require a clear lien certificate from the clerk of court alongside the final waiver.',
    ],
];
