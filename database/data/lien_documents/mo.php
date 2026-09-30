<?php

/*
 * Missouri: RSMo chapter 429 (Mechanics' and Materialmen's Liens), §§ 429.010
 * to 429.360.
 *
 * Statement of mechanic's lien: § 429.080 (within six months after the
 * indebtedness accrued, file "with the clerk of the circuit court of the proper
 * county a just and true account of the demand due him or them after all just
 * credits have been given", a true description of the property and the name of
 * the owner or contractor, or both, "verified by the oath of himself or some
 * credible person for him"). The clerk endorses the filing date and keeps an
 * abstract (§ 429.090); liens on property in Kaw township, Jackson County, are
 * filed with the circuit clerk at Kansas City (§ 478.483). Suit to enforce
 * within six months after filing (§ 429.170). Chapter 429 does not require
 * serving the filed lien on the owner.
 * Ten-day notice: § 429.100 (every person except the original contractor gives
 * the owner or agent ten days' notice before filing "that he holds a claim
 * against such building or improvement, setting forth the amount and from whom
 * the same is due"; served by an officer "or by any person who would be a
 * competent witness", proved by the officer's return or the server's
 * affidavit). A nonresident or absent owner's notice may be recorded with the
 * recorder of deeds instead (§ 429.110).
 * Notice to owner: § 429.012 (every original contractor gives the person it
 * contracts with, before receiving any payment, a written notice with the
 * disclosure "in ten-point bold type", verbatim in
 * letters/bodies/mo-notice-to-owner; a condition precedent to the original
 * contractor's lien). Owner-occupied residential repairs: § 429.013 (consent of
 * owner; not generated). Residential notice of rights: § 429.016.
 * Satisfaction: § 429.120 (the creditor files an acknowledgment of satisfaction
 * with the clerk of the circuit court when required; § 429.130 liability after
 * ten days). Recorder format: § 59.310 (3-inch top margin; title, date,
 * grantor and grantee names, statutory addresses and legal description on page
 * 1). Notary certificates: § 486.740 (required elements), § 486.750
 * (acknowledgment form), § 486.755 (jurat form).
 * Verified against revisor.mo.gov (Revised Statutes of Missouri) on 2026-09-30.
 */

return [
    'state' => 'MO',
    'state_name' => 'Missouri',
    'recording' => [
        // § 429.080 names the circuit clerk; the county file records where Jackson actually took it.
        'filing_office' => ['label' => 'Clerk of the Circuit Court', 'method' => 'mail'],
        // § 59.310.2: title, date, grantor and grantee names, addresses and legal description on page 1.
        'index_block' => true,
        'index_roles' => ['grantor' => 'owner', 'grantee' => 'claimant'],
        'notes' => [
            'Filing office is an open question: the July 2026 Jackson County lien was e-recorded with the Recorder of Deeds through CSC, but RSMo § 429.080 says the lien is filed with the clerk of the circuit court of the county where the property is located (so do §§ 429.090 and 429.120, and § 478.483 for Kaw township in Jackson County); confirm with counsel which office perfects the lien before the next Missouri filing.',
            'RSMo § 59.310 wants a 3-inch top margin reserved for the recorder and, on page 1 below it, the title, date, grantor and grantee names, statutory addresses and legal description (a recorder may refuse a document that does not comply, or record it for $25 more); the index block prints them on every Missouri lien and release, but not the grantor\'s marital status, which § 59.310.2(3) also lists.',
            'Whether the preparer block should move below the rule statewide is undecided; RSMo § 59.310.1(6) reserves the whole 3-inch top margin for the recorder, and Jackson County wanted the top three inches clear.',
        ],
    ],
    'execution' => [
        'verification' => 'sworn',
        'notary' => true,
        'notary_form' => 'jurat',
    ],
    'service' => [
        'recipients' => ['owner'],
        'days_after' => null,
        'method' => 'certified_mail',
    ],
    'kinds' => [
        'mechanics_lien' => [
            'title' => 'Statement of Mechanic\'s Lien',
            'statute' => 'RSMo § 429.080',
            'template_version' => 1,
            'sections' => [
                'amount' => 'itemized',
                'gc' => true,
                'hiring_party' => true,
                'contract_date' => true,
                // The generic "Prior notice" item names the prelim, which in Missouri is the
                // § 429.012 notice to owner; the § 429.100 ten-day notice has its own clause.
                'prior_notice' => false,
            ],
            'clauses' => [
                // House wording that tracks § 429.080 ("a just and true account of the demand
                // due ... after all just credits have been given", "within six months after
                // the indebtedness shall have accrued"); the sworn statement covers both.
                'affirmations' => [
                    'This statement, with the itemized account attached as Exhibit A, is a just and true account of the demand due Claimant after all just credits have been given (RSMo § 429.080).',
                    'This statement is filed within six months after the indebtedness accrued (RSMo § 429.080).',
                ],
                // § 429.100 recital; prints only when the claimant is not the original contractor.
                'before_signature' => ['documents.lien.instruments.clauses.mo-ten-day-notice'],
            ],
            'attachments' => [
                'Exhibit A: itemized account of the labor and materials furnished and the balance unpaid (RSMo § 429.080)',
            ],
            'notes' => [
                'File within six months after the indebtedness accrued (RSMo § 429.080), and sue to enforce within six months after filing or the lien ends (§ 429.170).',
                'Attach Exhibit A listing the labor and materials furnished with their prices, every credit and the balance; the annotations to RSMo § 429.080 show courts rejecting lump-sum accounts and accounts that only list invoice numbers.',
                'Every claimant except the original contractor must serve the ten-day Notice of Claim and Intent at least ten days before filing (RSMo § 429.100); enter its service date in Document details (notice served date) so the statement recites it.',
                'The lien of an original contractor (one who contracted with the owner) depends on the § 429.012 notice to owner, given before any payment (RSMo § 429.012.2).',
                'On a repair, remodel or addition to owner-occupied residential property of four units or less, anyone other than the original contractor must attach a copy of the Consent of Owner signed by an owner (RSMo § 429.013.3); eRegister does not generate it.',
                'Chapter 429 does not require serving the filed lien on the owner; staff mail a copy with the notice-of-recording letter.',
                'Missouri-worded certificates under RSMo §§ 486.750 and 486.755 are not added yet; the generic jurat prints the § 486.740 elements (signature, seal, state and county, date and the facts sworn).',
            ],
        ],
        'lien_release' => [
            'title' => 'Release of Mechanic\'s Lien',
            'statute' => 'RSMo § 429.120',
            'execution' => [
                'verification' => 'acknowledged',
                'notary' => true,
                'notary_form' => 'acknowledgment',
            ],
            'clauses' => [
                'affirmations' => [
                    'This release is Claimant\'s acknowledgment of satisfaction of the lien under RSMo § 429.120.',
                ],
            ],
            'notes' => [
                'Once the debt is paid, the claimant must file an acknowledgment of satisfaction with the clerk of the circuit court when asked (RSMo § 429.120); refusing for ten days after payment and a request makes the claimant liable for the resulting injury (§ 429.130).',
                'Missouri-worded certificates under RSMo §§ 486.750 and 486.755 are not added yet; the generic acknowledgment prints the § 486.740 elements.',
            ],
        ],
        'prelim_notice' => [
            'title' => 'Notice to Owner (Original Contractor\'s Disclosure)',
            'statute' => 'RSMo § 429.012',
            'body' => 'documents.lien.letters.bodies.mo-notice-to-owner',
            'template_version' => 1,
            'sections' => [
                'amount' => 'none',
                'gc' => false,
                'first_furnish' => false,
                'last_furnish' => false,
            ],
            'service' => [
                // § 429.012.1: "to the person with whom the contract is made or to the owner if there is no contract".
                'recipients' => ['customer'],
                'days_after' => null,
                'method' => 'certified_mail',
            ],
            'notes' => [
                'Only an original contractor (one who contracted with the owner) gives this notice, to the person it contracted with, before receiving any payment: when the contract is signed, when materials are delivered, when work starts, or with the first invoice (RSMo § 429.012.1).',
                'The notice is a condition precedent to the original contractor\'s lien (RSMo § 429.012.2); keep proof of delivery.',
                'The statute sets no delivery method: certified mail gives proof, and the notice may instead be handed over with the contract or sent with the first invoice.',
                'Subcontractors and suppliers do not give this notice; their step before filing is the ten-day Notice of Claim and Intent (RSMo § 429.100).',
                'The Consent of Owner for owner-occupied residential repairs (RSMo § 429.013) must be signed separately from this notice; eRegister does not generate it.',
            ],
        ],
        'noi' => [
            'title' => 'Notice of Claim and Intent to File Mechanic\'s Lien',
            'statute' => 'RSMo § 429.100',
            'sections' => ['demand_days' => 10],
            'clauses' => [
                // House wording that tracks § 429.100: the claim against the building or
                // improvement, the amount, from whom it is due, and the ten days before filing.
                'demand' => 'Under RSMo § 429.100, Claimant gives you notice that it holds a claim against the building or improvement on the property described above. The amount of the claim is the amount unpaid stated above. It is due from the person with whom Claimant contracted, named above. If the claim is not paid, Claimant intends to file its mechanic\'s lien against the property no sooner than ten days after this notice is served on you.',
            ],
            'service' => [
                'recipients' => ['owner'],
                'days_after' => null,
                'method' => 'certified_mail',
                'proof' => 'affidavit',
            ],
            'notes' => [
                'Every claimant except the original contractor serves this notice on the owner or the owner\'s agent at least ten days before filing the lien (RSMo § 429.100); do not file until ten full days after service.',
                'RSMo § 429.100 lets an officer who serves civil process, or any person who would be a competent witness, serve the notice, and proves service by the officer\'s return or the server\'s affidavit.',
                'Whether certified mail alone satisfies § 429.100 is undecided; until counsel says otherwise, have a competent adult serve it and sign the notarized affidavit of service.',
                'The ten days run before filing, which the service days field (days after recording) does not model, so it is left empty.',
                'If the owner lives outside Missouri with no agent in the county, or cannot be found, the notice may be recorded with the county recorder of deeds instead, with the same effect as service (RSMo § 429.110).',
                'RSMo § 429.016.14 excuses this notice for some residential property; send it anyway unless told otherwise.',
            ],
        ],
    ],
];
