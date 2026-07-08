<?php

/*
 * Vermont: no statutory lien waiver form.
 *
 * 9 V.S.A. § 1921(f): "A lien under this section may not be waived in advance
 * of the time such labor is performed or materials are furnished, and any
 * provision calling for such advance waiver shall not be enforceable." The
 * trigger is PERFORMANCE, not payment: once labor/materials have been
 * furnished, lien rights may be waived even before payment is received, so
 * unconditional waivers are possible (conditional remains the safer
 * practice). Waivers are otherwise essentially unregulated: clear intent to
 * waive plus consideration, no notarization or sworn-statement requirements.
 * The generic house forms apply.
 */

return [
    'state' => 'VT',
    'state_name' => 'Vermont',
    'family' => 'generic',
    'advance_waiver_note' => '9 V.S.A. § 1921(f): a lien may not be waived in advance of the time the labor is performed or materials are furnished, and any provision calling for such an advance waiver is unenforceable. The trigger is performance, not payment: once the work has been furnished, lien rights may be waived even before payment is received.',
    'ui_notes' => [
        'Vermont bars waiving a lien before the labor is performed or materials are furnished (9 V.S.A. § 1921(f)). Date the waiver after the work it covers, and prefer a conditional waiver until payment actually arrives.',
    ],
    'landing' => [
        'headline' => 'Vermont Lien Waivers: No Statutory Form, and Advance Waivers Are Unenforceable',
        'summary' => 'Vermont prescribes no lien waiver form and imposes no notarization or format requirements, so our general-purpose conditional and unconditional waivers for progress and final payments apply; a waiver only needs to clearly express intent to waive and be supported by consideration. 9 V.S.A. § 1921(f) makes a lien unwaivable in advance of the time the labor is performed or materials are furnished, and any provision calling for such an advance waiver is unenforceable. Because the trigger is performance rather than payment, lien rights may be waived once work is furnished even before payment, so conditional waivers are the prudent default until funds are received.',
    ],
];
