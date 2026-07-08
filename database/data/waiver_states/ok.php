<?php

/*
 * Oklahoma: no statutory lien waiver form. Title 42 O.S. (the lien
 * statutes) contains no provision governing lien waivers, so the generic
 * house forms apply.
 *
 * There is no anti-waiver statute: Oklahoma follows freedom of contract,
 * and case law permits unconditional waivers absent fraud or
 * misrepresentation. Advance waivers are not statutorily void.
 * Conditioning waivers on actual receipt of payment is best practice, not
 * a statutory requirement. No notarization requirement.
 */

return [
    'state' => 'OK',
    'state_name' => 'Oklahoma',
    'family' => 'generic',
    'advance_waiver_note' => null,
    'landing' => [
        'headline' => 'Oklahoma lien waivers: no prescribed form and no statutory restrictions',
        'summary' => 'Oklahoma does not prescribe a statutory lien waiver form: Title 42 of the Oklahoma Statutes says nothing about waiver form or content, so our general-purpose conditional and unconditional progress and final waivers apply. Oklahoma also has no anti-waiver statute: an advance or unconditional waiver is generally enforceable as written absent fraud or misrepresentation. The safe pattern for claimants is a conditional waiver that becomes effective only when the identified payment is actually received.',
    ],
];
