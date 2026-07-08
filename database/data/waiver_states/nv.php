<?php

/*
 * Nevada: NRS 108.2457 (term of contract that attempts to waive or impair
 * lien rights void; requirements for enforceability of waiver or release;
 * effect of two-party joint check; forms).
 *
 * Subsection 1 voids any contract term attempting to waive or impair the
 * lien rights of a contractor, subcontractor or supplier. Subsection 5
 * prescribes four waiver-and-release forms (conditional and unconditional x
 * progress and final) and makes any lien claimant's waiver "unenforceable
 * unless it is in the following forms in the following circumstances"; the
 * statute contains NO "substantially similar" allowance, so the forms are
 * reproduced verbatim with only the blanks bound. No notarization or witness
 * appears anywhere in the section (adding a notary block arguably deviates
 * from the prescribed form and could jeopardize enforceability, so none is
 * rendered). Each unconditional form must carry its statutory Notice "in
 * type at least as large as the largest type otherwise on the document";
 * note the two Notices differ: the progress version reads "enforceable
 * against you if you sign it to the extent of the Payment Amount or the
 * amount received" while the final version reads "enforceable against you
 * if you sign it, even if you have not been paid." Subsection 5(e): a waiver
 * given for a check, draft or other negotiable instrument that fails to
 * clear the bank is null, void and of no legal effect whatsoever. Statutory
 * text verified against leg.state.nv.us NRS chapter 108 (July 2026); added
 * 2003, last amended 2005, no 2019-2026 amendments.
 */

return [
    'state' => 'NV',
    'state_name' => 'Nevada',
    'family' => 'statutory_four',
    'statute' => 'NRS 108.2457',
    'compliance_standard' => 'verbatim',
    'advance_waiver_note' => 'NRS 108.2457(1): any term of a contract that attempts to waive or impair the lien rights of a contractor, subcontractor or supplier is void, and an owner, contractor or subcontractor may not obtain a waiver or impairment of lien rights by any term of a contract, or otherwise, except through a waiver and release in the exact form set forth in the statute.',
    'ui_notes' => [
        'Nevada allows no deviation from its waiver forms: NRS 108.2457(5) makes a waiver and release unenforceable unless it is in the statutory form; there is no "substantially similar" allowance, so the text is reproduced exactly with only the blanks filled.',
        'Do not add a notary or witness block: NRS 108.2457 requires neither, and adding execution formalities the statute does not prescribe arguably deviates from the mandatory form and could jeopardize enforceability.',
        'A conditional waiver binds only if the claimant actually receives the identified payment (NRS 108.2457(1)(b)), and under NRS 108.2457(5)(e) any waiver (conditional or unconditional) given for a check, draft or other negotiable instrument that fails to clear the bank is null, void and of no legal effect whatsoever.',
        'Unconditional forms bind on signing to the extent stated even if payment is never received. The statutory Notice must appear in type at least as large as the largest type otherwise on the document. Use a conditional form until the check has been paid by the bank.',
    ],
    'kinds' => [
        'conditional_progress' => [
            'template' => 'documents.lien.waivers.bodies.nv-conditional-progress',
            'title' => 'Conditional Waiver and Release Upon Progress Payment',
            'template_version' => 1,
        ],
        'unconditional_progress' => [
            'template' => 'documents.lien.waivers.bodies.nv-unconditional-progress',
            'title' => 'Unconditional Waiver and Release Upon Progress Payment',
            'template_version' => 1,
        ],
        'conditional_final' => [
            'template' => 'documents.lien.waivers.bodies.nv-conditional-final',
            'title' => 'Conditional Waiver and Release Upon Final Payment',
            'template_version' => 1,
        ],
        'unconditional_final' => [
            'template' => 'documents.lien.waivers.bodies.nv-unconditional-final',
            'title' => 'Unconditional Waiver and Release Upon Final Payment',
            'template_version' => 1,
        ],
    ],
    'landing' => [
        'headline' => 'Nevada Lien Waivers: The Four Exact Statutory Forms Under NRS 108.2457',
        'summary' => 'Nevada is one of the strictest lien waiver states in the country: NRS 108.2457 voids any contract term that attempts to waive or impair lien rights and makes a waiver unenforceable unless it uses one of the four forms printed in the statute (conditional and unconditional versions for progress and final payments) with no "substantially similar" allowance. Conditional waivers become effective only when the identified check has been endorsed and paid by the bank, and any waiver exchanged for a check that fails to clear is null and void. No notarization or witness is required, and each unconditional form must carry the statutory warning notice in type at least as large as the largest type on the document.',
    ],
];
