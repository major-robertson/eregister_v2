<?php

/*
 * Georgia: O.C.G.A. § 44-14-366 (rewritten by SB 315, Ga. L. 2020, p. 576,
 * eff. Jan. 1, 2021; affidavit form conformed by SB 143, Ga. L. 2021).
 *
 * Two statutory forms only: the Waiver and Release of Lien and Payment Bond
 * Rights Upon Interim Payment (§ 44-14-366(d)) and Upon Final Payment
 * (§ 44-14-366(e)). SB 315 inserted a new subsection (a), shifting the
 * pre-2021 letters down one. Georgia has no conditional/unconditional
 * matrix: both forms are conditional on payment by operation of law
 * (§ 44-14-366(c), (g)(1)), but each conclusively becomes effective on the
 * earliest of actual receipt of the funds, a separate written acknowledgment
 * of payment in full, or 90 days after execution (even with no payment)
 * unless the claimant first files an Affidavit of Nonpayment in the county
 * where the property is located (§ 44-14-366(g)(2)); a timely affidavit
 * suspends the waiver until payment in full (§ 44-14-366(g)(5)).
 *
 * The waiver must substantially follow the statutory language, be in at
 * least 12 point font (boldface capitals no longer required), and carry the
 * all-caps NOTICE paragraph; omitting the notice renders the form
 * "unenforceable and invalid" as a waiver and release. Failure to complete
 * the blanks does not invalidate the form so long as the subject matter can
 * reasonably be determined. Executed under hand and seal with a witness
 * line on the form; no notary. Advance waivers are null, void, and
 * unenforceable (§ 44-14-366(b)).
 *
 * Text verified against the enrolled SB 315 (AS PASSED, legis.ga.gov
 * document 20192020/194229) and enrolled SB 143 (2021).
 */

return [
    'state' => 'GA',
    'state_name' => 'Georgia',
    'family' => 'statutory_two',
    'statute' => 'O.C.G.A. § 44-14-366',
    'compliance_standard' => 'substantial',
    'witness_required' => true,
    'esign_allowed' => false,
    'esign_disabled_reason' => 'Georgia statutory waivers are given under hand and seal with a witness signature line on the form itself. Print the waiver and have the claimant sign before a witness instead of sending it for e-signature.',
    'deemed_effective_days' => 90,
    'affidavit_of_nonpayment' => true,
    'advance_waiver_note' => 'O.C.G.A. § 44-14-366(b): a right to claim a lien or to claim upon a bond may not be waived in advance of furnishing labor, services, or materials; any purported advance waiver or release, including a contract clause, is null, void, and unenforceable.',
    'ui_notes' => [
        'A signed Georgia waiver conclusively becomes effective 90 days after its execution date even if payment never arrives (O.C.G.A. § 44-14-366(g)(2)). Calendar that deadline: filing an Affidavit of Nonpayment in the county where the property is located before the 90 days run suspends the waiver until payment in full is received.',
        'Within seven days of filing an Affidavit of Nonpayment, the claimant must send a copy to the property owner by registered or certified mail or statutory overnight delivery, and, if not in privity with the owner and a notice of commencement is on file, to the contractor at the address shown on the notice of commencement.',
        'Georgia does not use conditional/unconditional waiver families: only the two statutory forms are enforceable, and any other oral or written waiver statement is unenforceable (O.C.G.A. § 44-14-366(c)). On an interim waiver, lien priority (except as to retention) thereafter runs from the day after the date specified in the form.',
    ],
    'kinds' => [
        'conditional_progress' => [
            'template' => 'documents.lien.waivers.bodies.ga-interim',
            'title' => 'Waiver and Release of Lien and Payment Bond Rights Upon Interim Payment',
        ],
        'conditional_final' => [
            'template' => 'documents.lien.waivers.bodies.ga-final',
            'title' => 'Waiver and Release of Lien and Payment Bond Rights Upon Final Payment',
        ],
        'unconditional_progress' => [
            'enabled' => false,
            'disabled_reason' => 'Georgia has no unconditional waiver: every statutory waiver is conditional on payment by law, then becomes effective on its own 90 days after signing unless an Affidavit of Nonpayment is filed (O.C.G.A. § 44-14-366(g)). Use the interim waiver for progress payments.',
            'redirect_kind' => 'conditional_progress',
        ],
        'unconditional_final' => [
            'enabled' => false,
            'disabled_reason' => 'Georgia has no unconditional waiver: every statutory waiver is conditional on payment by law, then becomes effective on its own 90 days after signing unless an Affidavit of Nonpayment is filed (O.C.G.A. § 44-14-366(g)). Use the final waiver.',
            'redirect_kind' => 'conditional_final',
        ],
    ],
    'landing' => [
        'headline' => 'Georgia lien waivers: the two statutory forms of O.C.G.A. § 44-14-366',
        'summary' => 'Georgia enforces exactly two lien waiver forms (the statutory Waiver and Release of Lien and Payment Bond Rights Upon Interim Payment and Upon Final Payment), and any other purported waiver of lien or payment-bond rights is unenforceable. Both forms are conditional on payment by operation of law, but each conclusively becomes effective 90 days after execution even without payment unless the claimant files an Affidavit of Nonpayment in the county where the property is located. Every waiver must be in at least 12-point font, reproduce the statutory NOTICE paragraph on the form, and be signed under hand and seal before a witness.',
    ],
];
