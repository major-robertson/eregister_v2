<?php

/*
 * Alabama: no statutory lien waiver form.
 *
 * The mechanics' lien act (Ala. Code § 35-11-210 et seq.) does not regulate
 * waiver form or content, so the generic house forms apply and the waiver's
 * own terms control (construed against the drafter, ambiguities resolved in
 * favor of preserving lien rights). Alabama is an outlier: it has NO
 * anti-waiver statute, and under Alabama case law lien rights may be waived
 * in advance of payment (including via a "no-lien" contract clause) so
 * long as the intent to waive is clear and explicit. No notarization or
 * sworn-statement requirement. Rule sourced from the condensed 50-state
 * research (Levelset/Siteline secondary sources); the lien chapter's silence
 * on waivers is the operative statutory fact.
 */

return [
    'state' => 'AL',
    'state_name' => 'Alabama',
    'family' => 'generic',
    'advance_waiver_note' => 'Alabama has no anti-waiver statute: the mechanics\' lien act (Ala. Code § 35-11-210 et seq.) does not regulate waiver form or content, and under Alabama case law lien rights may be waived in advance of payment, including through a "no-lien" contract clause, so long as the intent to waive is clear and explicit. Ambiguous waivers are construed against the drafter and in favor of preserving lien rights.',
    'ui_notes' => [
        'Alabama has no anti-waiver statute: unlike most states, lien rights can be waived in advance of payment (including by a "no-lien" clause in the construction contract) whenever the intent to waive is clear and explicit. Read waiver and contract language carefully; an unconditional waiver may bind the claimant even before payment arrives.',
    ],
    'landing' => [
        'headline' => 'Alabama Lien Waivers: No Statutory Form, the Waiver\'s Terms Control',
        'summary' => 'Alabama does not prescribe a lien waiver form: the mechanics\' lien act (Ala. Code § 35-11-210 et seq.) says nothing about waiver form or content, so our general-purpose conditional and unconditional progress and final waiver forms apply, with no notarization required. Because Alabama has no anti-waiver statute, lien rights can even be waived in advance of payment when the intent to waive is clear and explicit, making conditional waivers tied to actual payment the prudent choice for claimants. Ambiguities in a waiver are construed against the drafter and in favor of preserving lien rights.',
    ],
];
