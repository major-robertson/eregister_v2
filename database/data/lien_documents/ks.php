<?php

/*
 * Kansas: K.S.A. chapter 60, article 11 (Liens for Labor and Material).
 *
 * Mechanic's lien statement: § 60-1101 gives a lien to anyone furnishing
 * labor, equipment, material or supplies under a contract with the owner (or
 * the owner's trustee, agent or spouse). § 60-1102(a): the claimant "shall
 * file with the clerk of the district court of the county in which property
 * is located, within four months after the date material, equipment or
 * supplies, used or consumed was last furnished or last labor performed under
 * the contract a verified statement showing: (1) The name of the owner, (2)
 * the name and address sufficient for service of process of the claimant, (3)
 * a description of the real property, (4) a reasonably itemized statement and
 * the amount of the claim", with a copy of the written instrument or
 * promissory note allowed in lieu of the itemized statement; (c) a notice of
 * extension stretches the time to five months on property other than
 * residential property. § 60-1103, subcontractors and suppliers: (a)(1) "The
 * lien statement must state the name of the contractor and be filed within
 * three months" (five with a notice of extension, (e)); (a)(2) attach the
 * affidavit that a required § 60-1103a warning statement was properly given;
 * (a)(3) a required § 60-1103b notice of intent to perform must have been
 * filed; (c) after filing, serve a copy on any one owner, any holder of a
 * recorded equitable interest and any party obligated to pay, personally, by
 * restricted mail (§ 60-103: a return receipt and delivery to the addressee
 * only) or, when no address can be found, by posting it on the premises. The
 * court annotations to § 60-1102 hold that an acknowledgment is not a
 * verification and that a "best knowledge and belief" verification is not
 * enough, so the statement is sworn before a notary (jurat).
 *
 * Warning statement (prelim_notice): § 60-1103a. A subcontractor or supplier
 * improving residential property may claim a lien only after mailing any one
 * owner the statement in (c) ("shall contain substantially the following
 * statement", verbatim in letters/bodies/ks-warning-statement), or holding a
 * copy signed and dated by an owner that the claimant or the general
 * contractor gave it ((b)(2), letters/clauses/ks-owner-acknowledgment); not
 * needed for a claim of $250 or less ((d)).
 *
 * Notice of intent (noi): no Kansas statute; a courtesy demand. The § 60-1103b
 * notice of intent to perform (new residential property, filed with the clerk
 * of the district court before the deed to a buyer is recorded) is a
 * different document and is not generated.
 *
 * Release: article 11 has no release section or form. A lien not foreclosed
 * within one year of filing (§ 60-1105(a)) is "considered canceled by
 * limitation of law" (§ 60-1108).
 *
 * Notarial certificates: K.S.A. 53-5a16 (jurisdiction, date, the officer's
 * signature, title and commission expiry, and the seal on paper); § 53-508,
 * which recorders still cite, was repealed effective January 1, 2022.
 *
 * Verified against ksrevisor.gov on 2026-09-30.
 */

return [
    'state' => 'KS',
    'state_name' => 'Kansas',
    'recording' => [
        // What K.S.A. 60-1102(a) says; see the first note and lien_counties/ks/johnson.php.
        'filing_office' => ['label' => 'Clerk of the District Court', 'method' => 'mail'],
        'parcel_label' => 'Parcel ID',
        'notes' => [
            'K.S.A. 60-1102(a) says the lien statement is filed with the clerk of the district court of the county where the property is located, but the archive\'s June 2026 Johnson County lien was e-recorded with the Register of Deeds under the eRegister MOU; confirm with counsel which office perfects the lien before the next Kansas filing.',
            'File within four months after the last furnishing for an original contractor (K.S.A. 60-1102(a)) and within three months for a subcontractor or supplier (K.S.A. 60-1103(a)(1)); on property other than residential property, a notice of extension filed within that time extends it to five months (K.S.A. 60-1102(c), 60-1103(e)).',
            'A suit to foreclose must be brought within one year after the statement is filed, or the lien is canceled by limitation of law (K.S.A. 60-1105(a), 60-1108).',
        ],
    ],
    'execution' => [
        'verification' => 'sworn',
        'notary' => true,
        'notary_form' => 'jurat',
    ],
    'service' => [
        // days_after stays the seeded default: K.S.A. 60-1103(c) sets no number of days.
        'recipients' => ['owner'],
        'method' => 'certified_mail',
    ],
    'kinds' => [
        'mechanics_lien' => [
            'title' => 'Mechanic\'s Lien Statement',
            'statute' => 'K.S.A. 60-1102 and 60-1103',
            'sections' => [
                'amount' => 'itemized',
                'license' => true,
                'contract_date' => true,
                'gc' => true,
                'hiring_party' => true,
                // The warning statement exists only for residential property, where the
                // K.S.A. 60-1103(a)(2) affidavit in attachments proves it was given.
                'prior_notice' => false,
            ],
            'clauses' => [
                // House wording for the K.S.A. 60-1102(a) and 60-1103(a)(1) contents, inside the verified statement.
                'affirmations' => [
                    'Claimant\'s address stated above is sufficient for service of process (K.S.A. 60-1102(a)(2)).',
                    'A reasonably itemized statement of the claim, or a copy of the written instrument or promissory note that evidences it, is attached as Exhibit A and is part of this statement (K.S.A. 60-1102(a)(4)).',
                    'If Claimant is a subcontractor or supplier, the contractor is named above, either as the person who contracted with Claimant or as the original (general) contractor (K.S.A. 60-1103(a)(1)).',
                    'This statement is filed within the time K.S.A. 60-1102 and 60-1103 allow after Claimant last furnished labor, equipment, material or supplies: four months for an original contractor, three months for a subcontractor or supplier, or five months after a timely notice of extension.',
                ],
            ],
            'attachments' => [
                'Exhibit A: itemized statement of the claim, or a copy of the written contract or promissory note it is evidenced by (K.S.A. 60-1102(a)(4)).',
                'Subcontractor or supplier on residential property: the affidavit that the K.S.A. 60-1103a warning statement was properly given (K.S.A. 60-1103(a)(2)).',
            ],
            'notes' => [
                'Kansas courts have held that an acknowledgment is not a verification and that a verification "to the best of my knowledge and belief" is not enough; the claimant swears to the statement as true before a notary.',
                'A subcontractor\'s or supplier\'s statement must name the original contractor or the lien fails (K.S.A. 60-1103(a)(1)); make sure the project has the general contractor party.',
                'On residential property, a subcontractor or supplier must have mailed the warning statement before filing and must attach an affidavit that it was properly given (K.S.A. 60-1103(a)(2)); on new residential property sold to a buyer, a notice of intent to perform must have been filed before the deed was recorded (K.S.A. 60-1103(a)(3), 60-1103b).',
                'After filing, a subcontractor or supplier serves a copy on any one owner, any holder of a recorded equitable interest and any party obligated to pay: in person, by restricted mail, or by posting it on the property when no address can be found (K.S.A. 60-1103(c)).',
                'Restricted mail means a return receipt and delivery to the addressee only (K.S.A. 60-103); Kansas courts have held plain certified mail is not enough, so use Certified Mail Restricted Delivery with a return receipt.',
                'K.S.A. 60-1103(c) sets no number of days, so serve as soon as the statement is filed; an original contractor has no service step, but mail the owner a copy anyway, as the June 2026 Johnson County filing did.',
            ],
        ],
        'lien_release' => [
            'title' => 'Release of Mechanic\'s Lien',
            'statute' => 'K.S.A. 60-1101 et seq.',
            'execution' => [
                'verification' => 'acknowledged',
                'notary' => true,
                'notary_form' => 'acknowledgment',
            ],
            'notes' => [
                'Kansas article 11 has no release section or form; file the release with the office that holds the lien statement.',
            ],
        ],
        'prelim_notice' => [
            'title' => 'Warning Statement (Notice to Owner)',
            'statute' => 'K.S.A. 60-1103a',
            'body' => 'documents.lien.letters.bodies.ks-warning-statement',
            'template_version' => 1,
            'sections' => [
                'amount' => 'none',
                'first_furnish' => false,
            ],
            'service' => [
                'recipients' => ['owner'],
                'days_after' => null,
                'method' => 'certified_mail',
            ],
            'clauses' => [
                // K.S.A. 60-1103a(b)(2): the owner-signed alternative to the mailing, after the claimant's signature.
                'after_execution' => ['documents.lien.letters.clauses.ks-owner-acknowledgment'],
            ],
            'notes' => [
                'Needed only from a subcontractor or supplier improving residential property: an existing home the owner lives in, used for no more than two families and not commercially (with its additions, garage, fence, pool or outbuilding), or new construction that becomes an individual owner\'s principal residence (K.S.A. 60-1103a(a)). Not needed for a claim of $250 or less (K.S.A. 60-1103a(d)).',
                'Mail it to any one owner before the lien statement is filed (K.S.A. 60-1103a(b)(1)); there is no deadline, but the owner can still pay the contractor safely until it arrives (K.S.A. 60-1103(d)(2)), so send it early.',
                'The statute only says "mailed"; certified mail gives the proof for the affidavit a subcontractor\'s or supplier\'s lien statement must attach (K.S.A. 60-1103(a)(2)).',
                'Instead of the mailing, the claimant may keep a copy signed and dated by any one owner stating that the claimant or the general contractor gave the warning statement (K.S.A. 60-1103a(b)(2)); the owner\'s acknowledgment at the foot is for that.',
                'Undecided: for a sub-subcontractor or a supplier to a subcontractor, "(name of contractor)" may mean the general contractor or the subcontractor the claimant\'s agreement is with (K.S.A. 60-1103a(c)); until counsel says otherwise the statement names the general contractor.',
            ],
        ],
        'noi' => [
            'title' => 'Notice of Intent to File a Mechanic\'s Lien Statement',
            'statute' => 'K.S.A. 60-1101 et seq.',
            'sections' => ['demand_days' => 10],
            'notes' => [
                'Kansas has no statutory notice of intent to file a lien; this is a demand courtesy sent before the lien statement is filed.',
                'This is not the K.S.A. 60-1103b notice of intent to perform, which a claimant on new residential property files with the clerk of the district court before the deed to a buyer is recorded; eRegister does not generate that notice.',
            ],
        ],
    ],
];
