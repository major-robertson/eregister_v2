<?php

/*
 * Pennsylvania: Mechanics' Lien Law of 1963, Act of Aug. 24, 1963, P.L. 1175,
 * No. 497 (49 P.S. §§ 1101-1902; Purdon's § 1502 is section 502 of the act).
 *
 * Mechanics' lien claim: a court filing, not a recording. § 1502(a)(1): filed
 * with the prothonotary of the court of common pleas of the county where the
 * improvement is located within six months after the completion of the
 * claimant's work (§ 1201(8): its last labor or last delivery of materials
 * under its contract). § 1502(a)(2): serve written notice of the filing on the
 * owner within one month after filing, "giving the court, term and number and
 * date of filing of the claim", and file the affidavit of service or the
 * acceptance of service within 20 days after service; § 1502(c): served "by an
 * adult in the same manner as a writ of summons in assumpsit, or if service
 * cannot be so made then by posting upon a conspicuous public part of the
 * improvement". Contents: § 1503(1)-(8) (contractor or subcontractor; owner's
 * name and address; the date of completion of the claimant's work; for a
 * subcontractor, the person it contracted with and the date the formal notice
 * was given; the contract and a general statement for an agreed-sum contractor,
 * otherwise a detailed statement of the labor and materials with the prices
 * charged for each; the amount; the improvement and the property). The claim
 * is verified in the sense of Pa.R.C.P. 76 ("made subject to the penalties of
 * 18 Pa.C.S. § 4904 relating to unsworn falsification to authorities"); no
 * statute or rule requires a notary. Every filing carries the certification
 * of Sections 7.0(D) and 8.0(D) of the Case Records Public Access Policy of
 * the Unified Judicial System (204 Pa. Code § 213.81; Pa.R.C.P. 205.6).
 *
 * Formal notice of intention to file a claim: § 1501(b.1) (every subcontractor
 * claim, at least 30 days before filing), contents in (c), service in (d)
 * (first-class, registered or certified mail, or an adult as for a writ of
 * summons, or posting). § 1501(a), the preliminary notice in alteration and
 * repair cases, was deleted by Act 52 of 2006, so the preliminary notice here
 * is a courtesy; on searchable projects the statutory notice is the Notice of
 * Furnishing filed in the State Construction Notices Directory (§ 1501.3(b),
 * implemented at 47 Pa.B. 1828). Satisfaction: § 1704. Enforcement: § 1701
 * (an action within two years after filing; Pa.R.C.P. 1653 has it commenced by
 * a complaint under the claim's docket number). Exceptions: § 1301(a) (over
 * $500), § 1301(b) (owner-occupied residential property, subcontractors),
 * § 1303.
 *
 * Verified on 2026-09-30 against the act as published by the Pennsylvania
 * General Assembly (palegis.us, where the legis.state.pa.us links now
 * redirect), 18 Pa.C.S. § 4904 on the same site, and Pa.R.C.P. 76, 402, 1024,
 * 205.6, 1651-1661 and 204 Pa. Code § 213.81 on pacodeandbulletin.gov.
 */

return [
    'state' => 'PA',
    'state_name' => 'Pennsylvania',
    'recording' => [
        'filing_office' => ['label' => 'Prothonotary', 'method' => 'either'],
        // The docket caption (court, Claimant v. Owner, "No. ___ of 20__", title,
        // property, parcel) replaces the index line, and the 1.0" top margin leaves
        // no page-1 recorder space: the shell still prints the recorder rule at the
        // top of the page, and the prothonotary stamps the filing there.
        'caption' => 'docket',
        'index_line' => false,
        'top_margin_in' => 1.0,
        'preparer_in_space' => false,
        'page_numbers' => true,
        'parcel_label' => 'Parcel ID',
        'notes' => [
            'Pennsylvania claims are filed with the prothonotary of the court of common pleas of the county where the improvement is located, not recorded (49 P.S. §§ 1201(13), 1502(a)(1)). The prothonotary stamps the filing at the top of page 1, so there is no recorder space; the docket number goes in the caption once assigned, and the caption prints ruled blanks until then.',
            'The claim and the satisfaction are verified under 18 Pa.C.S. § 4904 (Pa.R.C.P. 76), not notarized. The archive\'s 2026 Allegheny draft used a notary jurat, which no Pennsylvania statute or rule requires for a claim.',
            'Every filing with the prothonotary carries the certificate of compliance with the Case Records Public Access Policy (Sections 7.0(D) and 8.0(D); Pa.R.C.P. 205.6). Keep Social Security numbers, financial account numbers (except the last four digits), driver license numbers and minors\' names and birth dates off the filing.',
            'Undecided: whether a business claimant may file its own claim without an attorney in every county (Allegheny\'s e-filing desk accepts pro se mechanics\' lien claims); until counsel says otherwise, file in the claimant\'s name as an unrepresented party.',
            'Undecided: who signs the certificate of compliance when eRegister staff submit the filing through a county e-filing portal; until counsel says otherwise, the claimant\'s signer signs it along with the verification.',
        ],
    ],
    'execution' => [
        'verification' => 'verified',
        'notary' => false,
        'notary_form' => null,
        // The § 4904 verification is a clause view (before_signature), so the
        // shared execution block prints only the signature lines.
        'statement' => false,
    ],
    'service' => [
        'recipients' => ['owner'],
        // "Within one (1) month after filing" (§ 1502(a)(2)).
        'days_after' => 30,
        // § 1502(c): by an adult as for a writ of summons, or by posting; not by mail.
        'method' => 'personal_delivery',
        'proof' => 'affidavit',
        // The affidavit is filed with the prothonotary and made by whoever serves the owner in Pennsylvania.
        'perjury_state' => 'PA',
    ],
    'kinds' => [
        'mechanics_lien' => [
            'title' => 'Mechanics\' Lien Claim',
            'statute' => '49 P.S. § 1502',
            'template_version' => 1,
            'sections' => [
                'amount' => 'itemized',
                'gc' => true,
                'hiring_party' => true,
                // § 1503(5): an agreed-sum contractor identifies its contract.
                'contract_date' => true,
                // § 1503(3): the date of completion of the claimant's work.
                'completion_date' => true,
                // § 1503(4) is recited by pa-formal-notice-recital; Pennsylvania has no preliminary notice.
                'prior_notice' => false,
            ],
            'clauses' => [
                'affirmations' => [
                    'Claimant completed its work on the date of completion stated above, and this claim is filed within six months after that date (49 P.S. § 1502(a)(1)).',
                    'A detailed statement of the kind and character of the labor and materials furnished, with the prices charged for each, is attached as Exhibit A (49 P.S. § 1503(6)).',
                    'Claimant will serve written notice of the filing of this claim on the owner within one month after filing, giving the court, term and number and date of filing, and will file the affidavit of service within 20 days after service (49 P.S. § 1502(a)(2)).',
                ],
                'before_signature' => [
                    // § 1503(1) contractor or subcontractor; § 1503(4) the formal notice date.
                    'documents.lien.instruments.clauses.pa-formal-notice-recital',
                    'documents.lien.instruments.clauses.pa-verification',
                ],
                'after_execution' => ['documents.lien.instruments.clauses.pa-certificate-of-compliance'],
            ],
            'attachments' => [
                'Exhibit A: detailed statement of the kind and character of the labor and materials furnished and the prices charged for each (49 P.S. § 1503(6)).',
                'Certificate of compliance with the Case Records Public Access Policy of the Unified Judicial System (prints as the last page).',
            ],
            'notes' => [
                'File within six months after the completion of the claimant\'s work, meaning its last labor or last delivery of materials under its contract (49 P.S. §§ 1201(8), 1502(a)(1)). The claim prints the project\'s completion date as that date.',
                'Within one month after filing, serve written notice of the filing on the owner, giving the court, term and number and date of filing. Service is by an adult in the same manner as a writ of summons (handed to the owner, to an adult member of the owner\'s household or the person in charge of the residence, or to the person in charge of the owner\'s office), or by posting on a conspicuous public part of the improvement if service cannot be made that way; mail alone is not a method the statute names (49 P.S. § 1502(a)(2), (c); Pa.R.C.P. 402(a)). The month runs by the calendar, so it is shorter than 30 days through February.',
                'File the affidavit of service, or the owner\'s acceptance of service, with the prothonotary within 20 days after service, stating the date and manner of service. Missing either deadline is ground to strike the claim (49 P.S. § 1502(a)(2)). The generated affidavit gives eRegister\'s address as the server\'s; when a process server or another adult serves the owner, that person signs one with their own address.',
                'Undecided: whether the generated cover letter with a stamped copy of the claim is enough written notice of filing, since the letter names the court only through the claim\'s caption and says the document was mailed; until counsel says otherwise, hand the owner both.',
                'A subcontractor\'s claim is valid only if the formal notice of intention to file was given to the owner at least 30 days before filing (49 P.S. § 1501(b.1)). Enter its date and method in Document details so the claim recites them (49 P.S. § 1503(4)).',
                'Exhibit A, the detailed statement of the labor and materials with the price charged for each, goes with every claim; only a contractor with an agreed-sum contract may identify the contract and describe the work generally instead (49 P.S. § 1503(5), (6)). The claim must also describe the improvement itself (for example, a single-family house), in the legal description or the description of work (49 P.S. § 1503(8)).',
                'No lien for a debt of $500 or less, or for labor or materials furnished for a purely public purpose (49 P.S. §§ 1301(a), 1303(b)). A subcontractor has no lien on an owner-occupied single townhouse or one- or two-unit residence when the owner paid the contractor the full contract price (49 P.S. § 1301(b)); a supplier to a supplier, or anyone who contracted with a subcontractor that was not in direct contract with the contractor, has no lien at all (49 P.S. § 1201(5)).',
                'For alterations and repairs, the lien is lost if the property is sold in good faith for value before the claim is filed (49 P.S. § 1303(c)).',
                'The action to obtain judgment is a complaint filed under the claim\'s docket number within two years after filing, unless the owner extends the time in writing; a verdict or judgment must follow within five years (49 P.S. § 1701(b), (d); Pa.R.C.P. 1653).',
            ],
        ],
        'lien_release' => [
            'title' => 'Satisfaction of Mechanics\' Lien Claim',
            'statute' => '49 P.S. § 1704',
            'template_version' => 1,
            'execution' => [
                'verification' => 'verified',
                'notary' => false,
                'notary_form' => null,
                'statement' => false,
            ],
            'service' => [
                'recipients' => ['owner'],
                'days_after' => null,
                'method' => 'certified_mail',
                'proof' => 'declaration',
                'perjury_state' => null,
            ],
            'clauses' => [
                'affirmations' => [
                    // The body above already directs the county's filing office by name.
                    'The claim described above has been paid, and Claimant directs that satisfaction of the claim be entered on the record (49 P.S. § 1704).',
                ],
                'before_signature' => ['documents.lien.instruments.clauses.pa-verification'],
                'after_execution' => ['documents.lien.instruments.clauses.pa-certificate-of-compliance'],
            ],
            'notes' => [
                'Once the claim is paid, the claimant must enter satisfaction on the record. If it does not do so within 30 days after a written request, the court may order the claim satisfied and impose a penalty of up to the amount of the claim (49 P.S. § 1704).',
                'Enter the claim\'s docket number and filing date in Document details (original lien) so the satisfaction names them. The caption\'s number line stays blank, so give the docket number to the e-filing portal or the clerk when filing.',
                'No service is required; send the owner a copy once the prothonotary marks the claim satisfied.',
            ],
        ],
        'prelim_notice' => [
            'title' => 'Notice of Furnishing (courtesy)',
            // No statute provides for this notice, so no citation prints.
            'statute' => null,
            'template_version' => 1,
            'sections' => [
                'amount' => 'estimate',
                'gc' => true,
                'first_furnish' => true,
            ],
            'service' => [
                'recipients' => ['owner'],
                'days_after' => null,
                'method' => 'certified_mail',
                'proof' => 'declaration',
            ],
            'notes' => [
                'Not required in Pennsylvania: the preliminary notice for alteration and repair work (the former 49 P.S. § 1501(a)) was deleted in 2006 (Act 52). This is a courtesy notice of furnishing.',
                'On a searchable project (an improvement costing $1,500,000 or more) where the owner filed and posted a Notice of Commencement, a subcontractor must file a Notice of Furnishing in the State Construction Notices Directory within 45 days after first furnishing or it forfeits its lien rights (49 P.S. § 1501.3(b), (c)). This letter does not file it.',
                'A subcontractor\'s statutory step before the claim is the formal notice of intention to file (the notice of intent), at least 30 days before filing.',
            ],
        ],
        'noi' => [
            'title' => 'Formal Notice of Intention to File Mechanics\' Lien Claim',
            'statute' => '49 P.S. § 1501(b.1)',
            'template_version' => 1,
            'sections' => [
                'demand_days' => 30,
                // § 1501(c)(5): the date of completion of the work for which the claim is made.
                'completion_date' => true,
            ],
            'clauses' => [
                // House wording tracking § 1501(b.1) and (c); the generic body prints (c)(1)-(6):
                // the claimant, the person it contracted with, the amount, the work, the completion
                // date and the property.
                'demand' => 'Claimant gives you formal written notice of its intention to file a mechanics\' lien claim against the property described above for the amount stated above, for the labor and materials described above, which Claimant completed on the date of completion stated above. Unless Claimant is paid in full first, it will file the claim with the prothonotary of the court of common pleas no sooner than 30 days after this notice is served (49 P.S. § 1501(b.1), (c)).',
            ],
            'service' => [
                'recipients' => ['owner'],
                'days_after' => null,
                'method' => 'certified_mail',
                'proof' => 'affidavit',
            ],
            'notes' => [
                'Required of every subcontractor, for new construction and for alterations and repairs alike: give it to the owner at least 30 days before the claim is filed, or the claim is invalid (49 P.S. § 1501(b.1)). It is not needed when the claim is filed in answer to a rule to file (49 P.S. § 1506).',
                'Serve it by first-class, registered or certified mail on the owner or the owner\'s agent, or by an adult in the same manner as a writ of summons, or by posting on a conspicuous public part of the improvement if service cannot be made those ways (49 P.S. § 1501(d)).',
                'The claim is due within six months after completion, so send this notice within five months. Enter its date and method in Document details so the claim recites them (49 P.S. § 1503(4)).',
                'For a contractor that contracted with the owner, it is a courtesy demand; no statute requires it.',
            ],
        ],
    ],
];
