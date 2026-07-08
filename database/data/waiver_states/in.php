<?php

/*
 * Indiana: no statutory lien waiver form; waiver documents are unregulated
 * as to format and need no notarization. The generic house forms apply.
 *
 * Ind. Code § 32-28-3-16 voids any provision in a contract for the
 * improvement of real estate that requires a person furnishing labor,
 * materials, or machinery to waive lien rights or payment-bond rights. The
 * section does not apply to Class 2 structures (one- or two-dwelling-unit
 * buildings, Ind. Code § 22-12-1-5) or certain utility projects, where a
 * no-lien contract is enforceable if in writing, containing a legal
 * description, acknowledged like a deed, and recorded within five days
 * (Ind. Code § 32-28-3-1).
 */

return [
    'state' => 'IN',
    'state_name' => 'Indiana',
    'family' => 'generic',
    'advance_waiver_note' => 'Ind. Code § 32-28-3-16: any provision in a contract for the improvement of real estate requiring a person furnishing labor, materials, or machinery to waive lien or payment-bond rights is void, except on Class 2 structures (one- or two-dwelling-unit buildings) and certain utility projects, where a properly acknowledged no-lien contract recorded within five days is enforceable (Ind. Code § 32-28-3-1).',
    'ui_notes' => [
        'Indiana voids advance lien-waiver clauses in construction contracts on most projects (Ind. Code § 32-28-3-16); tie each waiver to work performed and payment received rather than waiving in the contract. The exception is one- or two-family dwellings (Class 2 structures) and certain utility projects, where a properly recorded no-lien contract can validly cut off lien rights.',
    ],
    'landing' => [
        'headline' => 'Indiana lien waivers: unregulated format, but advance waiver clauses are void on most projects',
        'summary' => 'Indiana prescribes no statutory lien waiver form and imposes no notarization requirement, so our general-purpose conditional and unconditional progress and final waivers apply. Advance waivers are another matter: Ind. Code § 32-28-3-16 voids any construction-contract provision requiring a supplier of labor, materials, or machinery to waive lien or payment-bond rights on most projects. The carve-out is one- or two-family dwellings and certain utility projects, where a written, acknowledged no-lien contract recorded within five days remains enforceable.',
    ],
];
