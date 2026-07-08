<?php

/*
 * Mississippi: Miss. Code Ann. §§ 85-7-419, 85-7-433 (S.B. 2622, Laws 2014,
 * ch. 487, eff. April 11, 2014; modeled on Georgia's O.C.G.A. § 44-14-366).
 *
 * Two statutory forms only: the Interim Waiver and Release Upon Payment
 * (§ 85-7-433(1), any payment other than final) and the Waiver and Release
 * Upon Final Payment (§ 85-7-433(2)). A requested waiver "shall substantially
 * follow" the applicable form (§ 85-7-419(2)-(3)); failure to correctly
 * complete a blank does not invalidate the form if the subject matter
 * reasonably may be determined. There is no conditional/unconditional matrix:
 * every executed waiver is binding "subject only to payment in full"
 * (§ 85-7-419(5)(a)) and the amount is CONCLUSIVELY DEEMED PAID sixty (60)
 * days after execution unless the claimant first files an Affidavit of
 * Nonpayment (§ 85-7-433(3)) in the county where the property lies and sends
 * the owner a copy within two (2) days of filing (§ 85-7-419(5)(b)). Both
 * forms must carry the all-caps 60-day NOTICE on their face (omitting it
 * renders the form "unenforceable and invalid as a waiver and release under
 * Section 85-7-419") and both end in a notary jurat ("SWORN TO AND
 * SUBSCRIBED BEFORE ME ... NOTARY PUBLIC"), so e-signature is disabled.
 * No font-size or boldface rule exists for the waiver forms; the statute
 * prints them (including the NOTICE) entirely in capital letters.
 * No amendments to §§ 85-7-419/433 from 2019 through the 2026 session.
 */

return [
    'state' => 'MS',
    'state_name' => 'Mississippi',
    'family' => 'statutory_two',
    'statute' => 'Miss. Code Ann. §§ 85-7-419, 85-7-433',
    'compliance_standard' => 'substantial',
    'notarization_required' => true,
    'esign_allowed' => false,
    'esign_disabled_reason' => 'Mississippi statutory waivers end in a notary jurat ("SWORN TO AND SUBSCRIBED BEFORE ME ... NOTARY PUBLIC"): the claimant must swear to the form before a notary public, so we generate a print-and-sign PDF for wet execution instead of offering e-signature.',
    'deemed_effective_days' => 60,
    'affidavit_of_nonpayment' => true,
    'advance_waiver_note' => 'Miss. Code Ann. § 85-7-419(1): a right to claim a lien or to claim upon a bond may not be waived in advance of the furnishing of labor, services, or materials; any purported waiver or release executed or made in advance is null, void, and unenforceable.',
    'ui_notes' => [
        'The 60-day time bomb: once executed, a Mississippi waiver is conclusively deemed PAID IN FULL sixty (60) days after the date it is executed, even if payment never arrives (Miss. Code Ann. § 85-7-419(5)(b)). An unpaid claimant must file an Affidavit of Nonpayment (§ 85-7-433(3)) in the county where the property is located before the 60-day period expires, and send the owner a copy within two (2) days of filing.',
        'The form\'s NOTICE says an "affidavit of nonpayment or a claim of lien" preserves your rights, but the operative statute (§ 85-7-419(5)) lists only the Affidavit of Nonpayment: file the affidavit as the safe route.',
        'Mississippi prescribes only two waiver forms: interim and final. There is no separate unconditional form: every waiver operates conditionally (binding subject only to payment in full) and becomes unconditional automatically 60 days after execution.',
    ],
    'kinds' => [
        'conditional_progress' => [
            'template' => 'documents.lien.waivers.bodies.ms-interim',
            'title' => 'Interim Waiver and Release Upon Payment',
        ],
        'unconditional_progress' => [
            'enabled' => false,
            'disabled_reason' => 'Mississippi has no unconditional waiver form. The statutory Interim Waiver and Release Upon Payment (§ 85-7-433(1)) is binding subject only to payment in full and becomes conclusively effective 60 days after execution by operation of law.',
            'redirect_kind' => 'conditional_progress',
        ],
        'conditional_final' => [
            'template' => 'documents.lien.waivers.bodies.ms-final',
            'title' => 'Waiver and Release Upon Final Payment',
        ],
        'unconditional_final' => [
            'enabled' => false,
            'disabled_reason' => 'Mississippi has no unconditional waiver form. The statutory Waiver and Release Upon Final Payment (§ 85-7-433(2)) is binding subject only to payment in full and becomes conclusively effective 60 days after execution by operation of law.',
            'redirect_kind' => 'conditional_final',
        ],
    ],
    'landing' => [
        'headline' => 'Mississippi Lien Waiver Forms: the Interim and Final Statutory Waivers Under Miss. Code § 85-7-433',
        'summary' => 'Mississippi prescribes just two lien waiver forms, an Interim Waiver and Release Upon Payment and a Waiver and Release Upon Final Payment (Miss. Code Ann. § 85-7-433), and any waiver given in exchange for payment must substantially follow them, carry the statutory 60-day NOTICE on its face, and be sworn before a notary public. Every Mississippi waiver releases both lien rights and labor/material bond rights and is conditional on payment, but the amount is conclusively deemed paid sixty days after execution unless the claimant first files an Affidavit of Nonpayment in the county where the property sits. Advance waivers (signed before the labor or materials are furnished) are null, void, and unenforceable.',
    ],
];
