<?php

/*
 * Wisconsin: no statutory lien waiver form, but heavily regulated waiver
 * EFFECT (Wis. Stat. § 779.05) plus a contract anti-waiver rule
 * (Wis. Stat. § 779.135(1)).
 *
 * § 779.05(1): a signed waiver document is valid and binding whether or not
 * consideration was paid and whether signed before or after the work was
 * furnished or contracted for; any ambiguity is construed against the signer;
 * and the document is DEEMED to waive all of the signer's lien rights for
 * work furnished before its date "except to the extent that the document
 * specifically and expressly limits the waiver to apply to a particular
 * portion", so progress waivers must carry explicit express limitation
 * language (ours do). A claimant asked for a waiver is entitled to refuse
 * unless paid in full for the work the waiver relates to; a waiver waives
 * lien rights only, not contract rights. § 779.05(2): a promissory note or
 * other evidence of debt is not a waiver unless received as payment and
 * expressly declared to be one. § 779.135(1) voids construction contract
 * provisions requiring waiver of lien or payment-bond rights before the
 * claimant has been paid for the work. No notarization requirement. The
 * generic house forms apply.
 */

return [
    'state' => 'WI',
    'state_name' => 'Wisconsin',
    'family' => 'generic',
    'advance_waiver_note' => 'Wis. Stat. § 779.135(1) voids construction contract provisions requiring a person entitled to a construction lien to waive lien rights or payment-bond claims before being paid for the labor, services, materials, plans, or specifications furnished, and under Wis. Stat. § 779.05(1) a claimant is entitled to refuse to furnish a waiver unless paid in full for the work the waiver relates to. A signed standalone waiver document, however, is valid and binding whether or not consideration was paid and whether signed before or after the work is furnished.',
    'ui_notes' => [
        'Wisconsin deems a waiver to waive ALL of the signer\'s lien rights for the improvement unless the document "specifically and expressly" limits it to a particular portion, and any ambiguity is construed against the signer (Wis. Stat. § 779.05(1)). Our progress waivers state their limits expressly. Never sign a Wisconsin waiver broader than the payment received, and remember you may refuse a waiver until paid in full for the work it covers.',
    ],
    'landing' => [
        'headline' => 'Wisconsin Lien Waivers: No Statutory Form, but Waivers Are Construed Broadly Against the Signer',
        'summary' => 'Wisconsin prescribes no lien waiver form and requires no notarization, so our general-purpose conditional and unconditional waivers for progress and final payments apply, but Wisconsin heavily regulates waiver effect. Wis. Stat. § 779.05(1) makes a signed waiver valid even without consideration, deems it to waive all lien rights for the improvement unless it specifically and expressly limits itself to a particular portion, and construes any ambiguity against the signer, so the express limitation language in a progress waiver is critical. Wis. Stat. § 779.135(1) voids contract clauses requiring a waiver before payment, and a claimant may refuse to furnish a waiver until paid in full for the work it covers.',
    ],
];
