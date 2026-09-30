<?php

/*
 * Oregon: Construction Lien Law, ORS 87.001 to 87.060 and 87.075 to 87.093.
 *
 * Claim of lien: ORS 87.035 ((1) perfected not later than 75 days after the
 * claimant ceased to provide labor, rent equipment or furnish materials or 75
 * days after completion of construction, whichever is earlier; (2) filed with
 * the recording officer of the county where the improvement is; (3) "A true
 * statement of demand, after deducting all just credits and offsets", the
 * owner or reputed owner, the person who employed the claimant or to whom it
 * furnished the materials, and a description of the property "sufficient for
 * identification, including the address if known"; (4) "verified by the oath
 * of the person filing or of some other person having knowledge of the facts,
 * subject to the criminal penalties for false swearing provided under ORS
 * 162.075"). Completion of construction: ORS 87.045. Notice of filing: mailed
 * to the owner and the mortgagee with a copy of the claim not later than 20
 * days after filing, or no costs, disbursements or attorney fees (ORS 87.039).
 * Suit within 120 days after filing (ORS 87.055); notice of intent to foreclose
 * at least 10 days before the suit (ORS 87.057); attorney fees (ORS 87.060(5)).
 * Notice of right to a lien: ORS 87.021 (given to the owner by anyone the owner
 * did not order from; protects only what was provided after a date eight days
 * before delivery or mailing, not counting Saturdays, Sundays and holidays;
 * commercial exception in (3)(b)) in the form of ORS 87.023 ("shall include,
 * but not be limited to, the following information and shall be substantially
 * in the following form"), printed verbatim by letters/bodies/
 * or-notice-of-right-to-lien (the front) and letters/clauses/
 * or-notice-of-right-to-lien-reverse (the reverse side, after the
 * signature). Notices are delivered in person or by registered or certified
 * mail (ORS 87.018). Chapter 87 has no release form for a paid lien (ORS
 * 87.088 covers a release after a bond or deposit), so the release is generic.
 *
 * Recording: 10-point type or larger on paper no larger than 8 1/2 x 14 inches
 * (ORS 205.232, 10-point since 2024); first-page contents and the cover sheet
 * (ORS 205.234); $20 penalty for a nonstandard instrument (ORS 205.327); $5 per
 * page (ORS 205.320(1)(d)) plus $71 per instrument (ORS 205.323). Notarial
 * certificates: ORS 194.280 (venue, date, the signer's name, the notary's
 * signature and title, commission expiration, official stamp); the ORS 194.285
 * short forms are "sufficient", not mandatory, so the shared generic
 * certificates stay. Verified against oregonlegislature.gov (ORS 2025 edition)
 * on 2026-09-30.
 */

return [
    'state' => 'OR',
    'state_name' => 'Oregon',
    'recording' => [
        'filing_office' => ['label' => 'County Clerk', 'method' => 'erecord'],
        'parcel_label' => 'Parcel Number',
        'fee_note' => '$5 per page plus $71 in state fees (ORS 205.320, 205.323), so $76 for the first page; check the county\'s schedule (2026).',
        'notes' => [
            'File the claim of lien with the county clerk of the county where the property is located (ORS 87.035(2)) not later than 75 days after the claimant ceased to provide labor, rent equipment or furnish materials, or 75 days after completion of construction, whichever is earlier (ORS 87.035(1), 87.045).',
            'Page 1 must show the title, the parties indexed as grantor and grantee, and the name and address the recorded claim is returned to (ORS 205.234(1)); the caption index line and the preparer block carry them, with the amount claimed. A missing item costs a cover sheet or a $20 nonstandard-instrument penalty (ORS 205.234(2), 205.327).',
            'Type must be 10-point or larger, on paper no larger than 8 1/2 by 14 inches that records cleanly (ORS 205.232). E-record a clean, letter-size scan of the signed original.',
            'The lien lapses 120 days after the claim is filed unless a suit to foreclose it is brought within that time (ORS 87.055).',
            'Deliver a notice of intent to foreclose to the owner and the mortgagee at least 10 days before the foreclosure suit is filed; without it no costs or attorney fees are allowed (ORS 87.057).',
        ],
    ],
    'execution' => [
        'verification' => 'sworn',
        'notary' => true,
        'notary_form' => 'jurat',
    ],
    'service' => [
        'recipients' => ['owner', 'lender'],
        'days_after' => 20,
        'method' => 'certified_mail',
    ],
    'kinds' => [
        'mechanics_lien' => [
            'title' => 'Claim of Lien',
            'statute' => 'ORS 87.035',
            'template_version' => 1,
            'sections' => [
                'amount' => 'breakdown',
                'gc' => true,
                'hiring_party' => true,
                // Not every claimant without privity gives the notice (ORS 87.021(3)(b) excuses some on
                // commercial improvements), so no "served its notice on ___" item; the affirmation covers it.
                'prior_notice' => false,
            ],
            'clauses' => [
                'affirmations' => [
                    'The amount claimed above is a true statement of Claimant\'s demand, after deducting all just credits and offsets (ORS 87.035(3)(a)).',
                    'This claim of lien is filed not later than 75 days after Claimant ceased to provide labor, rent equipment or furnish materials, and not later than 75 days after completion of construction (ORS 87.035(1)).',
                    'If Claimant did not contract with the owner, Claimant gave the owner any notice of right to a lien that ORS 87.021 requires.',
                ],
            ],
            'notes' => [
                'The claim is verified by oath (ORS 87.035(4)): the signer swears the statements are true of their own knowledge. The archived 2026 draft said "true and correct to the best of my knowledge, information, and belief", which is weaker; do not use that wording.',
                'Within 20 days after filing, mail the owner and the mortgagee a notice that the claim was filed, with a copy of the claim attached (ORS 87.039(1), 87.018); a party who skips it gets no costs, disbursements or attorney fees (ORS 87.039(2)). The mortgagee is any lender named in a recorded mortgage or trust deed (ORS 87.005(6)); add it as the lender party so it is served.',
                'A notice of right to a lien protects only what was provided after the date eight days before it was delivered or mailed, not counting Saturdays, Sundays and holidays (ORS 87.021(1)); claim only that part.',
                'Check eligibility first: an original contractor has no lien without a written contract that ORS 701.305 required (ORS 87.037), or on residential work over $2,000 without having delivered the Information Notice to Owner (ORS 87.093(6)); a subcontractor or supplier on a remodel of an owner-occupied home has none if the contractor it worked for was unlicensed when it first contracted or first delivered (ORS 87.036).',
            ],
        ],
        'lien_release' => [
            'title' => 'Release of Claim of Lien',
            'statute' => 'ORS 87.001 to 87.093',
            'execution' => [
                'verification' => 'acknowledged',
                'notary' => true,
                'notary_form' => 'acknowledgment',
            ],
            'service' => [
                'recipients' => ['owner'],
                'days_after' => null,
                'method' => 'certified_mail',
            ],
            'notes' => [
                'Chapter 87 has no release form for a paid claim of lien (ORS 87.088(3) covers a release after a bond or deposit), so this release is house wording, acknowledged before a notary.',
                'If the owner delivers a written demand to release the lien (ORS 87.076(4)), release it within 10 days or foreclose within the ORS 87.055 time; otherwise the claimant owes the owner\'s costs or $500, whichever is greater.',
            ],
        ],
        'prelim_notice' => [
            'title' => 'Notice of Right to a Lien',
            'statute' => 'ORS 87.021 and 87.023',
            'body' => 'documents.lien.letters.bodies.or-notice-of-right-to-lien',
            'template_version' => 1,
            'sections' => [
                // The ORS 87.023 form states no price and no dates.
                'amount' => 'none',
                'gc' => false,
                'lender' => false,
                'first_furnish' => false,
                'last_furnish' => false,
            ],
            'service' => [
                'recipients' => ['owner'],
                'days_after' => null,
                'method' => 'certified_mail',
            ],
            'clauses' => [
                // The front's last line and the form's reverse side, after the signature.
                'after_execution' => ['documents.lien.letters.clauses.or-notice-of-right-to-lien-reverse'],
            ],
            'notes' => [
                'Deliver it to the owner in person or by registered or certified mail (ORS 87.018), and write in the date of mailing if it printed blank. It protects only what was provided after the date eight days before it was delivered or mailed, not counting Saturdays, Sundays and holidays, so send it as soon as work starts (ORS 87.021(1), (3)).',
                'Needed by everyone who did not contract with the owner, except that on a commercial improvement a claimant who provided labor, or labor and materials, or rented equipment need not give it (ORS 87.021(3)(b)). Any building other than an owner-occupied residence of four or fewer units is a commercial improvement.',
                'Undecided: whether the IMPORTANT INFORMATION FOR YOUR PROTECTION must sit on the back of the same sheet, as the one-sheet ORS 87.023 form has it; until counsel says otherwise, mail every page as generated, with that information on its own last page after the signature.',
                'A materials supplier that wants priority over a recorded mortgage or trust deed must also deliver a copy to the mortgagee within eight days, not counting Saturdays, Sundays and holidays, after each delivery (ORS 87.025(3)).',
                'The Information Notice to Owner is a separate Construction Contractors Board form that an original contractor gives the owner on residential work over $2,000 (ORS 87.093); this notice does not replace it.',
            ],
        ],
        'noi' => [
            'title' => 'Notice of Intent to File a Claim of Lien',
            'statute' => 'ORS 87.001 to 87.093',
            'sections' => ['demand_days' => 10],
            'service' => [
                'recipients' => ['owner'],
                'days_after' => null,
                'method' => 'certified_mail',
            ],
            'notes' => [
                'Oregon has no notice of intent before the claim of lien; this is a courtesy demand. The statutory notice of intent to foreclose (ORS 87.057) is a different notice: it goes to the owner and the mortgagee after the claim is filed, at least 10 days before the foreclosure suit.',
            ],
        ],
    ],
];
