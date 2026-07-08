<?php

/*
 * Virginia: no statutory lien waiver form.
 *
 * Va. Code § 43-3(C): lien rights "may be waived in whole or in part at any
 * time by any person entitled to such lien, except that a general contractor,
 * subcontractor, lower-tier subcontractor, or material supplier may not waive
 * or diminish his lien rights in a contract in advance of furnishing any
 * labor, services, or materials. A provision that waives or diminishes [those]
 * lien rights in a contract executed prior to providing any labor, services,
 * or materials is null and void." So contract-embedded advance waivers are
 * void; standalone waivers signed after furnishing begins are valid. No
 * notarization for waivers (the memorandum of mechanics' lien itself must be
 * sworn, but that is the lien claim, not the waiver). The generic house forms
 * apply.
 */

return [
    'state' => 'VA',
    'state_name' => 'Virginia',
    'family' => 'generic',
    'advance_waiver_note' => 'Va. Code § 43-3(C): lien rights may be waived in whole or in part at any time by a person entitled to the lien, except that a general contractor, subcontractor, lower-tier subcontractor, or material supplier may not waive or diminish lien rights in a contract in advance of furnishing any labor, services, or materials. A provision doing so in a contract executed before providing any labor, services, or materials is null and void. Standalone waivers signed after furnishing begins remain valid.',
    'ui_notes' => [
        'Virginia voids contract provisions that waive or diminish a contractor\'s, subcontractor\'s, or supplier\'s lien rights in advance of furnishing labor, services, or materials (Va. Code § 43-3(C)). Sign standalone waivers only after your work on the project has begun.',
    ],
    'landing' => [
        'headline' => 'Virginia Lien Waivers: No Statutory Form, but Pre-Work Contract Waivers Are Null and Void',
        'summary' => 'Virginia prescribes no lien waiver form and requires no notarization for waivers (only the memorandum of mechanics\' lien itself must be sworn), so our general-purpose conditional and unconditional waivers for progress and final payments apply. Under Va. Code § 43-3(C), lien rights may be waived in whole or in part at any time, but a general contractor, subcontractor, lower-tier subcontractor, or material supplier may not waive or diminish lien rights in a contract in advance of furnishing any labor, services, or materials. Such contract provisions are null and void. Standalone waivers signed after furnishing begins remain fully valid, which is exactly how these forms are meant to be used.',
    ],
];
