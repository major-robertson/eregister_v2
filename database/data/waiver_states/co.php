<?php

/*
 * Colorado: no statutory lien waiver form, but a mandatory content rule.
 *
 * Colorado prescribes no waiver form, so the generic house forms apply.
 * C.R.S. § 38-22-119 is the guardrail: subsection (1) makes an agreement to
 * waive, abandon, or refrain from enforcing a lien binding only "as between
 * the parties to such contract" (and directs liberal construction of the
 * lien article), and subsection (2) requires EVERY agreement to waive lien
 * rights to contain a statement, by the person waiving, providing in
 * substance that all debts owed to any third party relating to the goods or
 * services covered by the waiver have been paid or will be timely paid.
 * Colorado is also one of only about two states (with Nebraska) permitting
 * lien rights to be waived in advance, including in the construction
 * contract before work begins; ambiguous waivers are construed in favor of
 * preserving lien rights. Statute text verified against
 * colorado.public.law's C.R.S. § 38-22-119 (checked July 2026).
 */

return [
    'state' => 'CO',
    'state_name' => 'Colorado',
    'family' => 'generic',
    'advance_waiver_note' => 'Colorado is one of the only states that permits lien rights to be waived in advance, including in the construction contract before work begins. C.R.S. § 38-22-119(1) makes an agreement to waive, abandon, or refrain from enforcing a lien binding only as between the parties to that contract, and § 38-22-119(2) requires every agreement to waive lien rights to contain a statement, by the person waiving, providing in substance that all debts owed to any third party relating to the goods or services covered by the waiver have been paid or will be timely paid. Ambiguous waivers are construed in favor of preserving lien rights.',
    'ui_notes' => [
        'C.R.S. § 38-22-119(2) requires every Colorado lien waiver to include a statement, in substance, that all debts owed to third parties relating to the goods or services covered by the waiver have been paid or will be timely paid, and Colorado is one of the only states where lien rights can be waived in advance, even in the construction contract itself, so review contract language carefully before signing.',
    ],
    // The § 38-22-119(2) content mandate: every agreement waiving lien rights
    // must contain this statement by the person waiving. "In substance" is the
    // standard, so this house wording satisfies it; appended to all four
    // generic waiver bodies.
    'extra_clauses' => [
        'The Claimant states that all debts owed to any third party by the Claimant relating to the goods or services covered by this waiver have been paid or will be timely paid (C.R.S. § 38-22-119(2)).',
    ],
    'landing' => [
        'headline' => 'Colorado Lien Waivers: No Statutory Form, One Mandatory Statement',
        'summary' => 'Colorado does not prescribe a lien waiver form, so our general-purpose conditional and unconditional progress and final waiver forms apply, but C.R.S. § 38-22-119(2) requires every agreement waiving lien rights to include a statement, in substance, that all debts owed to third parties for the covered goods or services have been paid or will be timely paid. Colorado is also one of the only states that allows lien rights to be waived in advance, even in the construction contract before work begins, though a waiver binds only the parties to it and ambiguous waivers are construed in favor of preserving lien rights.',
    ],
];
