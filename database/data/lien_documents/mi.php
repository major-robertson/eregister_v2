<?php

/*
 * Michigan: Construction Lien Act, 1980 PA 497, MCL 570.1101 to 570.1305.
 *
 * Claim of lien: MCL 570.1111 ((1) recorded with the register of deeds of
 * each county where the property is located within 90 days after the lien
 * claimant's last furnishing of labor or material; (2) "shall be in
 * substantially the following form", rendered verbatim by
 * bodies/mi-claim-of-lien with its own "Subscribed and sworn to before me"
 * jurat; (3) an assigned claim names the assignee; (4) a subcontractor,
 * supplier or laborer attaches a proof of service of its notice of
 * furnishing; (5) within 15 days after recording, a copy of the claim and of
 * any recorded proof of service goes to the designee, or to the owner or
 * lessee named in the notice of commencement when there is no designee,
 * personally or by certified mail, return receipt requested). The lien is
 * limited to the contract amount less payments (MCL 570.1107(1)).
 * Foreclosure within 1 year after recording (MCL 570.1117(1)); attorney fees
 * to a prevailing claimant, or to the owner when the action is vexatious
 * (MCL 570.1118(2)). Residential structures: MCL 570.1106(4), 570.1114,
 * 570.1118a. Contractor's sworn statement: MCL 570.1110(9)-(10).
 *
 * Notice of furnishing: MCL 570.1109 ((1) a subcontractor or supplier serves
 * the designee and the general contractor named in the notice of
 * commencement within 20 days after first furnishing, personally or by
 * certified mail; (2)-(3) laborers' deadlines; (4) the form, rendered
 * verbatim by letters/bodies/mi-notice-of-furnishing; (5)-(6) the effect of
 * a late notice). Deadline extensions when the notice of commencement is not
 * recorded or provided: MCL 570.1108(10)-(13), 570.1108a(9)-(10).
 *
 * Discharge once paid: MCL 570.1127(1) (the claimant delivers "a
 * certificate, witnessed and acknowledged in the same manner as a discharge
 * of mortgage, that the claim has been paid and is now discharged"; no form
 * is prescribed, so the generic release renders it). MCL 570.1112(1) calls
 * the recorded instrument a "certificate of discharge of lien". MCL 570.1128
 * is the county clerk's certificate that no foreclosure was started in time,
 * not this document.
 *
 * Recording: MCL 565.201(1) (names printed beneath signatures in black or
 * dark blue ink; the notary's name printed near the notary's signature;
 * 2-1/2 inches of unprinted space at the top of the first page and 1/2 inch
 * on the other sides; the recordable event on the first line of print;
 * 10-point type; the drafter's name and business address). Fee: MCL
 * 600.2567(1)(a), applied by MCL 570.1112(2). Notary statements: MCL
 * 55.287(2).
 *
 * Verified against legislature.mi.gov (Michigan Compiled Laws complete
 * through PA 103 of 2026) on 2026-09-30.
 */

return [
    'state' => 'MI',
    'state_name' => 'Michigan',
    'recording' => [
        'filing_office' => ['label' => 'Register of Deeds', 'method' => 'erecord'],
        'parcel_label' => 'Parcel Number',
        // MCL 565.201(1)(f)(i): 2-1/2 inches of unprinted space at the top of page 1
        // and at least 1/2 inch on every other side. The preparer block therefore
        // prints below the recorder's rule, and the shell's page number (about
        // 0.45 inch from the bottom edge) is turned off.
        'preparer_in_space' => false,
        'page_numbers' => false,
        'fee_note' => '$30 per document, any number of pages (MCL 600.2567(1)(a), MCL 570.1112(2)); a charter county may set its own fee.',
        'notes' => [
            'Page 1 needs 2-1/2 inches of unprinted space at the top, every page needs 1/2-inch margins and 10-point type, and signatures go in black or dark blue ink with each name printed beneath (MCL 565.201(1)); the preparer block prints below the recorder\'s rule and pages are not numbered, so nothing prints in those margins.',
            'A Michigan notary adds their name, "Notary public, State of Michigan, County of ___", the commission expiry, the county they are acting in if different, and the date, usually by stamp (MCL 55.287(2)).',
            'Undecided: whether a register will accept the recorder\'s rule and the preparer block printing above the title, since MCL 565.201(1)(f)(ii) wants the title on the first line of print; until counsel says otherwise, submit as generated and keep any rejection notice.',
            'Undecided: whether the preparer block must name the individual who drafted the instrument (MCL 565.201(1)(i)); until counsel says otherwise, it names eRegister and its business address.',
        ],
    ],
    'execution' => [
        'verification' => 'sworn',
        'notary' => true,
        'notary_form' => 'jurat',
    ],
    'service' => [
        'recipients' => ['owner'],
        'days_after' => 15,
        'method' => 'certified_mail',
        'proof' => 'affidavit',
    ],
    'kinds' => [
        'mechanics_lien' => [
            'title' => 'Claim of Lien',
            'statute' => 'MCL 570.1111',
            'body' => 'documents.lien.instruments.bodies.mi-claim-of-lien',
            'template_version' => 1,
            'sections' => [
                'amount' => 'single',
                'gc' => false,
                'prior_notice' => false,
            ],
            // The MCL 570.1111(2) form has no sworn paragraph; its jurat is the only
            // execution text, so the shared block prints the signature and the jurat.
            'execution' => ['statement' => false],
            'attachments' => [
                'Proof of service of the notice of furnishing, for a subcontractor, supplier or laborer (MCL 570.1111(4)).',
            ],
            'notes' => [
                'Record within 90 days after the claimant\'s last furnishing of labor or material, with the register of deeds of each county where the property is located (MCL 570.1111(1)).',
                'Within 15 days after recording, serve a copy of the recorded claim and of any recorded proof of service on the designee named in the notice of commencement, or the owner or lessee if none is named, personally or by certified mail, return receipt requested (MCL 570.1111(5)).',
                'Keep the proof of that service: it must be attached to any foreclosure complaint (MCL 570.1111(5)), and foreclosure must start within 1 year after recording (MCL 570.1117(1)).',
                'Use the legal description and the owner name as the notice of commencement gives them (MCL 570.1111(2)).',
                'The lien cannot exceed the contract amount less payments made on it (MCL 570.1107(1)), and the court may award the owner attorney fees if the action to enforce it is vexatious (MCL 570.1118(2)).',
                'A contractor cannot be paid or foreclose until it has given the owner a sworn statement (MCL 570.1110(9)); a subcontractor asked for one cannot foreclose until it provides it (MCL 570.1110(10)).',
                'On a residential structure, a contractor needs a written contract with the licensing statement (MCL 570.1114), and the lien does not attach to the extent the owner proves by affidavit that it paid the contractor (MCL 570.1118a).',
                'The form\'s laborer block (hourly rate and wages owed) does not print because eRegister has no laborer claimant type.',
            ],
        ],
        'lien_release' => [
            'title' => 'Certificate of Discharge of Lien',
            'statute' => 'MCL 570.1127',
            'execution' => [
                'verification' => 'acknowledged',
                'notary' => true,
                'notary_form' => 'acknowledgment',
            ],
            'service' => [
                'recipients' => ['owner'],
                'days_after' => null,
                'method' => 'certified_mail',
                'proof' => 'declaration',
            ],
            'clauses' => [
                // MCL 570.1127(1): a certificate "that the claim has been paid and is now discharged".
                'affirmations' => [
                    'The undersigned lien claimant certifies that the claim of lien described above has been fully paid and is now discharged.',
                ],
            ],
            'notes' => [
                'Once the claim is fully paid, deliver this certificate to the owner, lessee or other person who paid; if a foreclosure action is pending, also provide on request the papers to dismiss it and discharge the lis pendens (MCL 570.1127(1)).',
                'Record it with the register of deeds where the claim of lien was recorded (MCL 570.1112(1)).',
                'Undecided: whether the certificate still needs witnesses, since MCL 570.1127(1) says "witnessed" but Michigan deeds and mortgage discharges no longer need any (MCL 565.8); until counsel says otherwise, it is acknowledged before a notary without witnesses.',
            ],
        ],
        'prelim_notice' => [
            'title' => 'Notice of Furnishing',
            'statute' => 'MCL 570.1109',
            'body' => 'documents.lien.letters.bodies.mi-notice-of-furnishing',
            'template_version' => 1,
            'sections' => [
                'amount' => 'none',
                'first_furnish' => false,
                'last_furnish' => false,
            ],
            'service' => [
                'recipients' => ['owner', 'gc'],
                'days_after' => null,
                'method' => 'certified_mail',
                'proof' => 'affidavit',
            ],
            'attachments' => [
                'A copy of the notice of commencement, unless the liber and page where it was recorded are filled in (MCL 570.1109(4)).',
            ],
            'notes' => [
                'Serve within 20 days after first furnishing on the designee named in the notice of commencement, or the owner or lessee if none is named, and on the general contractor, personally or by certified mail; certified mail is complete on mailing, and a contractor hired directly by the owner or lessee does not need this notice (MCL 570.1109(1)).',
                'If the notice of commencement was not recorded, or not provided after a written request by certified mail, the 20 days run from when it is recorded or provided; a residential structure has no recorded notice, so request it from the owner and attach the copy (MCL 570.1108(10)-(11), MCL 570.1108a(5), (9)).',
                'A late notice still protects work done after it is served, and earlier work except to the extent the owner or lessee already paid the contractor for it under a sworn statement or lien waiver (MCL 570.1109(5)-(6)).',
                'Laborers have other deadlines: 30 days after unpaid wages were due, and the fifth day of the second month after unpaid fringe benefits or withholdings were due (MCL 570.1109(2)-(3)).',
                'Keep the proof of service: a subcontractor\'s or supplier\'s claim of lien must attach it (MCL 570.1111(4)).',
                'Undecided: how to address the notice when the notice of commencement names a designee other than the owner, since eRegister has no designee party; until counsel says otherwise, also send a copy to the designee.',
            ],
        ],
        'noi' => [
            'title' => 'Notice of Intent to Record a Claim of Lien',
            'statute' => 'MCL 570.1101 et seq.',
            'sections' => ['demand_days' => 10],
            'service' => [
                'recipients' => ['owner', 'gc'],
                'days_after' => null,
                'method' => 'certified_mail',
            ],
            'notes' => [
                'Michigan has no notice-of-intent step; the notice of furnishing and the claim of lien are the statutory steps. This is a demand courtesy.',
            ],
        ],
    ],
];
