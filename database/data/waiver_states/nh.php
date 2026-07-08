<?php

/*
 * New Hampshire: no statutory lien waiver form.
 *
 * RSA ch. 447 (Liens for Labor and Materials) contains no waiver provision at
 * all: no prescribed form, no anti-waiver rule, no notarization or
 * sworn-statement requirement. Waivers are ordinary contracts; NH Supreme
 * Court case law requires "an actual intention to forego a known right," so
 * waiver language must be clear and explicit. NH liens are perfected by court
 * attachment (RSA 447:9-:10), so waivers circulate as contract instruments,
 * not recorded releases. The generic house forms apply.
 */

return [
    'state' => 'NH',
    'state_name' => 'New Hampshire',
    'family' => 'generic',
    'advance_waiver_note' => null,
    'landing' => [
        'headline' => 'New Hampshire Lien Waivers: No Statutory Form or Anti-Waiver Statute',
        'summary' => 'New Hampshire\'s mechanics lien statute (RSA ch. 447) prescribes no waiver form and contains no anti-waiver provision, so our general-purpose conditional and unconditional waivers for progress and final payments apply and are treated as ordinary contracts. New Hampshire courts enforce a waiver only where it reflects an actual intention to forego a known right (waiver is never presumed), so clear, explicit waiver language matters. No notarization or witness is required.',
    ],
];
