<?php

/*
 * Kentucky: no statutory lien waiver form. KRS ch. 376 prescribes no
 * waiver format or exchange procedure; any writing clearly showing intent
 * to waive is effective, and no notarization is required (the KRS 376.080
 * subscribe-and-swear requirement applies to lien statements, a filing
 * requirement only). The generic house forms apply.
 *
 * Kentucky Fairness in Construction Act, KRS 371.405(2)(b): a
 * construction-contract provision purporting to waive, release, or
 * extinguish rights to claim a lien under KRS ch. 376 is void as against
 * public policy, except partial waivers of lien rights given in exchange
 * for payments actually made to the contractor or subcontractor. The Act
 * (KRS 371.400–371.425) applies to public and private construction but
 * excludes residential projects.
 */

return [
    'state' => 'KY',
    'state_name' => 'Kentucky',
    'family' => 'generic',
    'advance_waiver_note' => 'KRS 371.405(2)(b) (Kentucky Fairness in Construction Act): a construction-contract provision purporting to waive, release, or extinguish rights to claim a lien under KRS ch. 376 is void as against public policy, except partial waivers of lien rights given in exchange for payments actually made to the contractor or subcontractor. The Act excludes residential projects (KRS 371.400).',
    'ui_notes' => [
        'On covered (non-residential) Kentucky projects, contract clauses waiving lien rights are void under KRS 371.405(2)(b): only partial waivers given in exchange for payments actually made hold up, so tie each waiver to a payment actually received rather than waiving in the construction contract.',
    ],
    'landing' => [
        'headline' => 'Kentucky lien waivers: partial waivers for payments actually made under the Fairness in Construction Act',
        'summary' => 'Kentucky prescribes no statutory lien waiver form: KRS chapter 376 leaves the format to the parties and requires no notarization, so our general-purpose conditional and unconditional progress and final waivers apply. On covered non-residential projects, KRS 371.405(2)(b) voids any construction-contract provision purporting to waive, release, or extinguish lien rights, allowing only partial waivers given in exchange for payments actually made to the contractor or subcontractor. Matching each waiver to the payment it accompanies keeps it inside that exception.',
    ],
];
