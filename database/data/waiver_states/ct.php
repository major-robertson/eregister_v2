<?php

/*
 * Connecticut: no statutory lien waiver form; advance waivers void on
 * covered projects.
 *
 * Connecticut prescribes no waiver form, so the generic house forms apply.
 * Conn. Gen. Stat. § 42-158l voids any provision in a construction
 * contract, and any periodic lien waiver issued pursuant to one, that
 * purports to waive or release the right to claim a mechanic's lien or make
 * a claim against a payment bond "for services, labor or materials which
 * have not yet been performed and paid for." Chapter 742b (see § 42-158i)
 * covers private construction contracts entered into on or after October 1,
 * 1999, excluding public works/government contracts, HUD-funded or -insured
 * projects, contracts under $25,000, and residential buildings of four or
 * fewer units. Superior Court decisions apply the statute literally, so on
 * covered projects only conditional waivers (effective upon payment) or
 * waivers signed at/after payment for performed work are safe. Mechanic's
 * lien substance is C.G.S. § 49-33 et seq.; no notarization or
 * sworn-statement requirement. Statute text verified against the CGA's
 * chapter 742b page (checked July 2026).
 */

return [
    'state' => 'CT',
    'state_name' => 'Connecticut',
    'family' => 'generic',
    'advance_waiver_note' => 'Conn. Gen. Stat. § 42-158l voids any provision in a construction contract, and any periodic lien waiver issued pursuant to one, that purports to waive or release the right to claim a mechanic\'s lien or make a claim against a payment bond for services, labor, or materials which have not yet been performed and paid for. The rule covers private construction contracts under chapter 742b (entered into on or after October 1, 1999), excluding public works, HUD-funded projects, contracts under $25,000, and residential buildings of four or fewer units.',
    'ui_notes' => [
        'Conn. Gen. Stat. § 42-158l voids waivers of mechanic\'s-lien or payment-bond rights for services, labor, or materials not yet performed and paid for on covered commercial construction contracts. Use conditional waivers, or sign unconditional waivers only at or after payment for work already performed.',
    ],
    'landing' => [
        'headline' => 'Connecticut Lien Waivers: No Statutory Form, Advance Waivers Void',
        'summary' => 'Connecticut does not prescribe a lien waiver form, so our general-purpose conditional and unconditional progress and final waiver forms apply, with no notarization required. On covered commercial projects, Conn. Gen. Stat. § 42-158l voids any construction-contract provision (and any periodic lien waiver issued under one) that waives mechanic\'s-lien or payment-bond rights for services, labor, or materials which have not yet been performed and paid for. The safe pattern is conditional waivers effective upon payment, with unconditional waivers signed only at or after payment for work already performed.',
    ],
];
