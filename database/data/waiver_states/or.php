<?php

/*
 * Oregon: no statutory lien waiver form. ORS ch. 87 is silent on lien
 * waivers, so the generic house forms apply.
 *
 * There is no anti-waiver statute: Oregon does not prohibit waiving lien
 * rights prior to payment, and a clear signed waiver is generally binding
 * as written; conditional (payment-contingent) waivers are a drafting
 * best practice rather than a statutory requirement. Notarization is not
 * mandatory, but ORS 701.630 lets the original contractor or subcontractor
 * contractually require that waivers of lien be notarized, and ORS
 * 87.025(5) obliges a paid material supplier, on demand of the person
 * paying, to execute a waiver of lien rights to the extent of the payment.
 */

return [
    'state' => 'OR',
    'state_name' => 'Oregon',
    'family' => 'generic',
    'advance_waiver_note' => null,
    'landing' => [
        'headline' => 'Oregon lien waivers: no prescribed form and no anti-waiver statute',
        'summary' => 'Oregon does not prescribe a statutory lien waiver form: ORS chapter 87 is silent on waivers, so our general-purpose conditional and unconditional progress and final waivers apply. There is no anti-waiver statute either: a clear signed waiver is generally binding as written, which makes payment-contingent conditional waivers the prudent default. One Oregon wrinkle: waivers need not be notarized by law, but the paying contractor may contractually require notarized lien waivers under ORS 701.630.',
    ],
];
