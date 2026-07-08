<?php

/*
 * Maryland: no statutory lien waiver form; ordinary contract-law
 * principles govern content and no notarization is required. The generic
 * house forms apply.
 *
 * Md. Code, Real Prop. § 9-113: an executory contract between a contractor
 * and any subcontractor may not waive, or require the subcontractor to
 * waive, the right to claim a mechanics' lien or to sue on a contractor's
 * or payment bond; a provision in violation is void as against Maryland
 * public policy. The same section keeps contingent-payment
 * (pay-if-paid/pay-when-paid) clauses from operating to abrogate or waive
 * the subcontractor's lien or bond rights. The prohibition reaches only
 * contractor–subcontractor executory contracts; separate waiver documents
 * remain enforceable even when signed before payment, which is why
 * unconditional waivers signed pre-payment are dangerous here.
 */

return [
    'state' => 'MD',
    'state_name' => 'Maryland',
    'family' => 'generic',
    'advance_waiver_note' => 'Md. Code, Real Prop. § 9-113: an executory contract between a contractor and any subcontractor may not waive or require the subcontractor to waive the right to claim a mechanics\' lien or to sue on a contractor\'s or payment bond; any such provision is void as against public policy, and contingent-payment clauses cannot operate to abrogate those rights. The prohibition covers only contractor–subcontractor executory contracts; separate waiver documents are enforceable even before payment.',
    'ui_notes' => [
        'Maryland voids waiver-of-lien provisions in contractor–subcontractor executory contracts (Md. Code, Real Prop. § 9-113), but a separate waiver document is enforceable even if signed before payment arrives: an unconditional waiver signed before the check clears is binding, so use conditional waivers until payment is actually in hand.',
    ],
    'landing' => [
        'headline' => 'Maryland lien waivers: subcontractor contract waivers void, standalone waiver documents enforceable',
        'summary' => 'Maryland prescribes no statutory lien waiver form and requires no notarization, so our general-purpose conditional and unconditional progress and final waivers apply. Md. Code, Real Prop. § 9-113 voids any provision in an executory contract between a contractor and a subcontractor that waives (or requires the subcontractor to waive) mechanics\' lien or payment-bond rights, and it stops pay-if-paid clauses from erasing those rights. That prohibition does not reach separate waiver documents, which are enforceable even when signed before payment, so conditional waivers are the safer exchange until funds actually arrive.',
    ],
];
