<?php

/*
 * Illinois: Mechanics Lien Act, 770 ILCS 60.
 *
 * Claim for lien (mechanics_lien): § 60/7(a). To keep priority over other
 * creditors, incumbrancers and purchasers, the contractor must, "within 4
 * months after completion" (or after the completion of extra or additional
 * work or the final delivery of extra or additional material), sue or file
 * with the recorder of the county where the improvement is "a claim for lien,
 * verified by the affidavit of himself or herself, or his or her agent or
 * employee, which shall consist of a brief statement of the claimant's
 * contract, the balance due after allowing all credits, and a sufficiently
 * correct description of the lot, lots or tracts of land to identify the
 * same"; as to the owner the claim may be filed within 2 years after
 * completion. § 60/7(d): a contractor improving an owner-occupied
 * single-family residence gives the owner written notice within 10 days after
 * recording, or the lien is extinguished to the extent of the owner's
 * damages; it does not apply to subcontractors. § 60/1(a): the lien secures
 * the amount due "and interest at the rate of 10% per annum from the date the
 * same is due", extends to any estate or interest the owner has in the land
 * when the contract is made or later acquires, and "attaches as of the date
 * of the contract". Subcontractors: § 60/21(a) (a lien for the value "with
 * interest on such amount from the date the same is due", on the same
 * property as the contractor's) and § 60/28 (a claim for lien "within the
 * same limits as to time and in such other manner" as § 60/7). Suit within
 * two years after completion: § 60/9. Owner's written demand to sue within
 * 30 days: § 60/34. Contractor's sworn statement to the owner: § 60/5.
 *
 * Subcontractor's notice (prelim_notice): § 60/24(a), as amended by P.A.
 * 103-827 (eff. 2025-01-01). Within 90 days after completing the contract
 * with the contractor (or the extra or additional work or material), "a
 * written notice of his or her claim and the amount due or to become due
 * thereunder" goes to the owner of record (or the owner's agent or architect,
 * or the superintendent in charge) and to the lending agency, if known, by
 * registered or certified mail with a return receipt, a nationally recognized
 * delivery company with tracking, or personal service; it is served when
 * mailed or placed with the delivery company. "The form of such notice may be
 * as follows"; the form is verbatim in letters/bodies/il-subcontractor-notice.
 * § 60/25: a claim for lien recorded within the same 90 days is the notice to
 * an owner, agent or lender who cannot be found in the county or does not
 * live there. § 60/21(c): the separate 60-day notice to the occupant of an
 * existing owner-occupied single-family residence, which eRegister does not
 * generate.
 *
 * Release (lien_release): § 60/35. (a) Once the claim is paid with the cost
 * of filing it, the claimant acknowledges satisfaction or release in writing
 * within 10 days after a written demand, or owes the owner $2,500 plus costs
 * and reasonable attorney's fees; (b) the release filed with the recorder
 * discharges the claim; (c) the release must have imprinted on it, "in bold
 * letters at least 1/4 inch in height", the statement verbatim in
 * instruments/clauses/il-release-filing-statement.
 *
 * Notice of intent (noi): no Illinois statute; a courtesy demand.
 *
 * Recording: 55 ILCS 5/3-5018.2(c)(5) (a standard document is on 8.5 by 11
 * inch white paper of at least 20-pound weight, in black ink, with margins of
 * at least one-half inch, nothing stapled, 5 or fewer PINs or document
 * numbers, and "a blank space, measuring at least 3 inches by 5 inches, from
 * the upper right corner" of page 1; anything else is recorded as a
 * nonstandard document). It covers counties of the first and second class,
 * which is every county but Cook (55 ILCS 5/4-1001); the Cook County Clerk
 * publishes the same standards. The former § 3-5018 was repealed by P.A.
 * 103-400 (eff. 2024-01-01). 55 ILCS 5/3-5020.5 (the name and address the
 * document is returned to, and the document number of any instrument it
 * refers to) and 3-5022 (the preparer's name and address on its face).
 *
 * Notarial certificates: 5 ILCS 312/6-103 (signed and dated by the notary,
 * naming the jurisdiction, with the official seal) makes the § 6-105 short
 * forms "sufficient", not required, so the shared generic certificate stays.
 * The notary's rubber stamp seal: 5 ILCS 312/3-101(a).
 *
 * Verified against ilga.gov (the Mechanics Lien Act, Counties Code Divisions
 * 3-5 and 4-1, and the Illinois Notary Public Act) on 2026-09-30.
 */

return [
    'state' => 'IL',
    'state_name' => 'Illinois',
    'recording' => [
        'filing_office' => ['label' => 'County Recorder', 'method' => 'erecord'],
        'parcel_label' => 'PIN',
        // 55 ILCS 5/3-5018.2(c)(5)(D) does not say which side of the 3 by 5 inch space is the
        // width; with the preparer block below the rule, page 1 is blank across its top three inches.
        'preparer_in_space' => false,
        'notes' => [
            'Record the claim for lien with the recorder of the county where the property is located within four months after completion, or after the completion of extra or additional work, to keep its priority over other creditors, incumbrancers and purchasers; against the owner it may be recorded up to two years after completion (770 ILCS 60/7(a)). A subcontractor\'s claim has the same time limits (770 ILCS 60/28). eRegister\'s Illinois deadline is the four months.',
            'Suit to enforce the lien must be filed within two years after completion (770 ILCS 60/9). An owner can shorten that with a written demand: suit must then be filed within 30 days or the lien is forfeited (770 ILCS 60/34), so tell the client the day a demand arrives.',
            'In most counties the county clerk is also the recorder. In Cook County, the Recorder of Deeds office merged into the Cook County Clerk\'s office in December 2020, and the Clerk\'s Recordings Division now records.',
            'Recorders charge their standard fee only for a document on 8.5 by 11 inch white paper of at least 20-pound weight, in black ink, with margins of at least one-half inch, nothing stapled, 5 or fewer PINs or document numbers, and a blank space of at least 3 by 5 inches in the upper right corner of page 1; anything else pays the nonstandard fee (55 ILCS 5/3-5018.2(c)(5), which covers every county but Cook; the Cook County Clerk applies the same standards). The statute does not say which side of that space is the width, so page 1 stays blank across its top three inches and the preparer block prints below the rule; a county file can ask for more room (Clinton County wants 3 inches wide by 4 inches high).',
            'Every recorded document must show who prepared it and where it is returned, with names and addresses (55 ILCS 5/3-5022, 3-5020.5(1)); the preparer block does both.',
        ],
    ],
    'execution' => [
        // "Verified by the affidavit" (770 ILCS 60/7(a)): sworn before a notary.
        'verification' => 'sworn',
        'notary' => true,
        'notary_form' => 'jurat',
    ],
    'service' => [
        'recipients' => ['owner'],
        'days_after' => 10,
        'method' => 'certified_mail',
    ],
    'kinds' => [
        'mechanics_lien' => [
            'title' => 'Claim for Lien',
            'statute' => '770 ILCS 60/7 and 60/28',
            'body' => 'documents.lien.instruments.bodies.generic-lien',
            'template_version' => 1,
            'sections' => [
                'amount' => 'breakdown',
                'gc' => true,
                // The parties, the contract date, the description of work and the amount breakdown
                // are the § 60/7(a) "brief statement of the claimant's contract".
                'hiring_party' => true,
                'contract_date' => true,
                // No written-or-oral line: its "a copy is attached" would record the contract,
                // which Illinois does not require.
                'contract_type' => false,
                // Every subcontractor must have served the § 60/24 notice; a blank date means it is missing.
                'prior_notice' => true,
            ],
            'service' => [
                // 770 ILCS 60/7(d): notice to the owner within 10 days after recording.
                'recipients' => ['owner'],
                'days_after' => 10,
                'method' => 'certified_mail',
            ],
            'clauses' => [
                // House wording for 770 ILCS 60/7(a), 60/1(a) and 60/21(a), inside the sworn statement.
                'affirmations' => [
                    'This claim for lien is filed within four months after the completion of Claimant\'s contract, or of the extra or additional work or materials furnished under it (770 ILCS 60/7(a), 60/28).',
                    'The amount claimed is the balance due to Claimant after allowing all credits (770 ILCS 60/7(a)).',
                    'Claimant also claims interest on the amount claimed at the rate of 10% per annum from the date it became due (770 ILCS 60/1(a), 60/21(a)).',
                    'The lien extends to every estate, right of redemption or other interest the owner had in the land when the contract was made or acquires later, and it attaches as of the date of the contract (770 ILCS 60/1(a)).',
                ],
            ],
            'notes' => [
                'The claim must be sworn by the claimant or its agent or employee and give a brief statement of the claimant\'s contract, the balance due after allowing all credits, and a description of the land that identifies it (770 ILCS 60/7(a)). The contract date comes from Document details.',
                'A subcontractor files the same claim under the same time limits as a contractor (770 ILCS 60/28), so one title serves both.',
                'A contractor improving an owner-occupied single-family residence must give the owner written notice within 10 days after recording, or the lien is lost to the extent the owner is harmed by the delay (770 ILCS 60/7(d)); this does not apply to subcontractors. Mail every owner a copy within 10 days anyway.',
                'For a subcontractor, the prior-notice line names the Subcontractor\'s Notice of Claim (90-Day Notice) with its service date and method; fill both in Document details.',
                'The May 2026 Clinton County draft swore to its statements only "to the best of Claimant\'s knowledge and belief" and called its jurat an acknowledgment. 770 ILCS 60/7(a) wants the claim "verified by the affidavit" of the claimant or its agent or employee, so the signer now swears before a notary that the statements are true of his or her own knowledge.',
                'Undecided: whether a claim recorded more than four months after completion, which still binds the owner for two years, should drop the sentence saying it is filed within four months; until counsel says otherwise, record every Illinois claim within the four months.',
                'Undecided: whether the 10% rate of 770 ILCS 60/1(a) applies to a subcontractor, since 770 ILCS 60/21(a) gives a subcontractor interest from the date due without naming a rate; until counsel says otherwise, the claim states 10% for every claimant.',
            ],
        ],
        'lien_release' => [
            'title' => 'Release and Satisfaction of Mechanic\'s Lien',
            'statute' => '770 ILCS 60/35',
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
            'clauses' => [
                'affirmations' => [
                    'The claim for lien described above has been paid, and Claimant acknowledges its satisfaction and release under 770 ILCS 60/35.',
                ],
                // § 60/35(c): "in bold letters at least 1/4 inch in height". It prints after the
                // notary certificate: above the title it pushed the caption and index line off page 1.
                'after_execution' => ['documents.lien.instruments.clauses.il-release-filing-statement'],
            ],
            'notes' => [
                'Once the claim is paid with the cost of recording it, the claimant must give a written satisfaction or release within 10 days after a written demand from the owner, a lienor or anyone with an interest in the property, or owe the owner $2,500 plus the costs and reasonable attorney\'s fees of collecting it (770 ILCS 60/35(a)).',
                'Recording the release with the recorder that holds the claim discharges the claim for good (770 ILCS 60/35(b)). The release must show the recorder\'s document number of the claim (55 ILCS 5/3-5020.5(2)), so set the original lien\'s recording reference in Document details.',
                'The statement for the owner\'s protection must be in bold letters at least 1/4 inch high (770 ILCS 60/35(c)); it prints in 28-point bold after the notary certificate.',
            ],
        ],
        'prelim_notice' => [
            'title' => 'Subcontractor\'s Notice of Claim (90-Day Notice)',
            'statute' => '770 ILCS 60/24',
            'body' => 'documents.lien.letters.bodies.il-subcontractor-notice',
            'template_version' => 1,
            'sections' => [
                'amount' => 'single',
                'gc' => true,
                'first_furnish' => false,
                'last_furnish' => false,
            ],
            'service' => [
                'recipients' => ['owner', 'lender'],
                'days_after' => null,
                'method' => 'certified_mail',
            ],
            'notes' => [
                'A subcontractor, or anyone furnishing labor, materials or services to the contractor, serves it within 90 days after completing its contract with the contractor, or after the completion or final delivery of extra or additional work or material (770 ILCS 60/24(a)).',
                'Serve the owner of record (or the owner\'s agent or architect, or the superintendent in charge of the building) and the lending agency, if known, by registered or certified mail with a return receipt, by a nationally recognized delivery company with tracking, or in person. It counts as served when it is mailed or handed to the delivery company (770 ILCS 60/24(a), as amended by P.A. 103-827 effective January 1, 2025).',
                'It is not needed when the contractor\'s sworn statement to the owner (770 ILCS 60/5) already showed the amount due to the claimant; eRegister sends it in every case.',
                'When the owner, agent or lender cannot be found in the county or does not live there, a claim for lien recorded within the same 90 days serves as the notice to them (770 ILCS 60/25).',
                'For a sub-subcontractor or a supplier to a subcontractor, the notice names the party the claimant contracted with and the original contractor that party works under.',
                'On an existing owner-occupied single-family residence, a subcontractor must also notify the occupant within 60 days after first furnishing, in person or by certified mail with a return receipt, with the NOTICE TO OWNER warning of 770 ILCS 60/21(c) in 10-point bold type; a later notice keeps the lien only to the extent the owner has not paid the contractor in the meantime. eRegister does not generate that notice.',
                'Not needed when the claimant contracted directly with the owner.',
            ],
        ],
        'noi' => [
            'title' => 'Notice of Intent to File a Claim for Lien',
            'statute' => '770 ILCS 60/1 et seq.',
            'sections' => ['demand_days' => 10],
            'service' => [
                'recipients' => ['owner', 'gc'],
                'days_after' => null,
                'method' => 'certified_mail',
            ],
            'notes' => [
                'Illinois has no notice-of-intent step; the subcontractor\'s 90-day notice and the claim for lien are the statutory steps. This is a demand courtesy.',
                'Send it early enough that the 10-day demand ends well inside the four months to record the claim for lien (770 ILCS 60/7(a)).',
            ],
        ],
    ],
];
