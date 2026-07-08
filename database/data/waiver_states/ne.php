<?php

/*
 * Nebraska: no statutory lien waiver form.
 *
 * Neb. Rev. Stat. § 52-144 is the opposite of an anti-waiver statute: a
 * written waiver signed by the claimant requires no consideration and binds
 * whether signed before or after the materials or services were contracted
 * for or furnished. The drafting trap is § 52-144's default scope: unless
 * the waiver is specifically limited to a particular lien right or a
 * particular portion of the services or materials, it waives ALL of the
 * claimant's construction lien rights as to the improvement. Waiving lien
 * rights does not affect the claimant's other contract rights. No form,
 * typography, or notarization requirements; the generic house forms apply.
 */

return [
    'state' => 'NE',
    'state_name' => 'Nebraska',
    'family' => 'generic',
    'advance_waiver_note' => 'Neb. Rev. Stat. § 52-144: a written waiver of construction lien rights signed by the claimant is valid without consideration and binds whether signed before or after the materials or services were contracted for or furnished; advance waivers are enforceable, and unless the waiver is specifically limited to a particular lien right or a particular portion of the services or materials, it waives all of the claimant\'s construction lien rights as to the improvement.',
    'ui_notes' => [
        'Nebraska enforces lien waivers even when signed in advance and without payment, and a waiver that is not specifically limited waives ALL construction lien rights for the improvement (Neb. Rev. Stat. § 52-144). Keep progress waivers expressly limited to the payment amount and period they cover.',
    ],
    'landing' => [
        'headline' => 'Nebraska Lien Waivers: No Statutory Form, and Unlimited Waivers Release Everything',
        'summary' => 'Nebraska does not prescribe a statutory lien waiver form, so our general-purpose conditional and unconditional waivers for progress and final payments apply, with no notarization or witness required. Unlike most states, Neb. Rev. Stat. § 52-144 makes a signed written waiver valid without consideration and enforceable whether signed before or after the work; advance waivers and no-lien clauses are permitted. The same statute treats any waiver that is not specifically limited as a waiver of all construction lien rights for the improvement, so progress waivers must expressly state the amount and period they cover.',
    ],
];
