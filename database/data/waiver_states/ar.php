<?php

/*
 * Arkansas: no statutory lien waiver form.
 *
 * The mechanics' and materialmen's lien chapter (Ark. Code Ann. § 18-44-101
 * et seq.) is silent on waivers entirely (no forms, no anti-waiver rule, no
 * notarization or sworn-statement requirement), so common law governs and
 * the generic house forms apply. Advance waivers (before work begins or
 * before payment) are permitted, and an unconditional waiver is likely
 * enforceable even if payment was never actually made, provided the intent
 * to waive is clearly expressed; courts construe ambiguities against the
 * party seeking to enforce the waiver. Rule sourced from the condensed
 * 50-state research (Levelset/Siteline secondary sources); the lien
 * chapter's silence on waivers is the operative statutory fact.
 */

return [
    'state' => 'AR',
    'state_name' => 'Arkansas',
    'family' => 'generic',
    'advance_waiver_note' => 'Arkansas has no lien waiver statute: the mechanics\' and materialmen\'s lien chapter (Ark. Code Ann. § 18-44-101 et seq.) is silent on waivers, so advance waivers signed before work begins or before payment are permitted, and an unconditional waiver is likely enforceable even if payment was never actually made, provided the intent to waive is clearly expressed. Courts construe ambiguities against the party seeking to enforce the waiver.',
    'ui_notes' => [
        'Arkansas has no lien waiver statute: advance waivers are permitted, and an unconditional waiver is likely enforceable even if the payment never actually arrives. Send conditional waivers while payment is pending and hold unconditional waivers until funds are in hand.',
    ],
    'landing' => [
        'headline' => 'Arkansas Lien Waivers: No Statutory Form or Waiver Rules',
        'summary' => 'Arkansas does not prescribe a lien waiver form: the mechanics\' and materialmen\'s lien chapter (Ark. Code Ann. § 18-44-101 et seq.) is silent on waivers entirely, so our general-purpose conditional and unconditional progress and final waiver forms apply, with no notarization required. Because there is no anti-waiver statute, advance waivers are permitted and an unconditional waiver may be enforced even if payment never arrives, which makes conditional waivers tied to actual payment the prudent default for claimants. Courts require the intent to waive to be clearly expressed and construe ambiguities against the party enforcing the waiver.',
    ],
];
