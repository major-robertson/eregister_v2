<?php

/*
 * Alabama: Code of Alabama 1975, Title 35, ch. 11, art. 5, div. 8 (Mechanics
 * and Materialmen), §§ 35-11-210 to 35-11-234.
 *
 * Verified statement of lien: § 35-11-213 (filed in the office of the judge
 * of probate of the county where the property is situated; "a statement in
 * writing, verified by the oath of the person claiming the lien, or of some
 * other person having knowledge of the facts", stating the amount of the
 * demand after all just credits, a description of the property and the name
 * of the owner or proprietor; "Unless such statement is so filed the lien
 * shall be lost"; "Said verified statement may be in the following form,
 * which shall be deemed sufficient": the statement, the claimant's
 * signature, an affidavit before a notary public and a jurat, verbatim in
 * instruments/bodies/al-verified-statement-of-lien and
 * instruments/clauses/al-affidavit). Oath taken outside Alabama:
 * § 35-11-214. Time: § 35-11-215 (six months for an original contractor,
 * 30 days for a journeyman or day laborer, four months for every other
 * person, after the last item of work or material). Notice before filing by
 * everyone except the original contractor: § 35-11-218 (written notice to
 * the owner or proprietor, or his agent, that he claims a lien, "setting
 * forth the amount thereof, for what, and from whom it is owing"; no form,
 * no method, no waiting period). Materialman's notice before furnishing:
 * § 35-11-210 (a lien for the full price specified instead of only the
 * unpaid balance due the contractor; "The notice may be given in the
 * following form, which shall be sufficient", verbatim in
 * letters/bodies/al-materialman-notice). One acre outside a city or town:
 * §§ 35-11-210, 35-11-217. Suit within six months after the entire debt
 * matures: § 35-11-221. Satisfaction: § 35-11-231 (acknowledged on the
 * margin of the record in the probate office; at least $200 to anyone
 * injured when a paid holder fails for 30 days after a written demand).
 * Preparer statement: § 35-4-110. Acknowledgment forms: § 35-4-29; an
 * acknowledgment taken in another state in that state's form:
 * § 35-4-26(b). Notary seal: § 36-20-72. No margin or first-page rule for
 * recorded instruments was found in the Code, and the probate recording fee
 * (§ 12-19-90(b)(22), $3 a page) varies with local law (§ 12-19-90(d)), so
 * the recording defaults stand and no fee note is set. Verified against the
 * official Code of Alabama on alison.legislature.state.al.us (the
 * Legislature's ALISON site) on 2026-09-30.
 */

return [
    'state' => 'AL',
    'state_name' => 'Alabama',
    'recording' => [
        'filing_office' => ['label' => 'Judge of Probate', 'method' => 'either'],
        'parcel_label' => 'Parcel Number',
        'notes' => [
            'File the verified statement in the office of the judge of probate of the county where the property is situated; unless it is filed there in time, the lien is lost (Ala. Code §§ 35-11-213, 35-11-215).',
            'File within six months after the last item of work or material for an original contractor, 30 days for a journeyman or day laborer, and four months for everyone else (Ala. Code § 35-11-215).',
            'Everyone except the original contractor must give the owner written notice of the claim before filing the statement (Ala. Code § 35-11-218): the Notice of Claim and Intent to File Lien.',
            'A suit to enforce the lien must be started within six months after the entire debt matures (Ala. Code § 35-11-221).',
            'Undecided: whether the preparer block, which names eRegister and no person unless an attention line is configured, meets Ala. Code § 35-4-110 (the name and address of the individual who prepared the instrument); until counsel says otherwise, staff ask the probate office before filing.',
        ],
    ],
    // The § 35-11-213 form carries its own affidavit and jurat
    // (clauses/al-affidavit), so the shared sworn statement and notary
    // certificate stay off and the execution block prints only the
    // claimant's signature. The satisfaction overrides this with an
    // acknowledgment.
    'execution' => [
        'verification' => 'sworn',
        'notary' => false,
        'notary_form' => null,
        'statement' => false,
    ],
    'service' => [
        'recipients' => ['owner'],
        'days_after' => null,
        'method' => 'certified_mail',
    ],
    'kinds' => [
        'mechanics_lien' => [
            'title' => 'Verified Statement of Lien',
            'statute' => 'Ala. Code § 35-11-213',
            'body' => 'documents.lien.instruments.bodies.al-verified-statement-of-lien',
            'template_version' => 1,
            'sections' => [
                'amount' => 'single',
                'amount_in_words' => true,
                'gc' => true,
                'prior_notice' => false,
            ],
            'clauses' => [
                // § 35-11-213: the form's affidavit and jurat follow the claimant's signature.
                'after_execution' => ['documents.lien.instruments.clauses.al-affidavit'],
            ],
            'notes' => [
                'The signer is the affiant: the claimant\'s officer or another person with personal knowledge of the facts (Ala. Code § 35-11-213). Outside Alabama, any officer who takes acknowledgments there may give the oath (Ala. Code § 35-11-214).',
                'The affiant signs the affidavit before a notary public, who completes the jurat and seals it; the Signing line says no notary only because the form prints its own jurat instead of the shared certificate.',
                'Undecided: whether interest should run from the last furnishing date, which the statement uses, or from a later due date in the contract; until counsel says otherwise, staff use the last furnishing date.',
                'Outside a city or town the lien reaches the land under the building plus one acre, which the claimant may select before filing (Ala. Code §§ 35-11-210, 35-11-217); describe that acre in the legal description.',
                'Alabama requires no service of the filed statement; staff mail the owner a copy with the notice-of-recording letter.',
            ],
        ],
        'lien_release' => [
            'title' => 'Satisfaction of Lien',
            'statute' => 'Ala. Code § 35-11-231',
            'execution' => [
                'verification' => 'acknowledged',
                'notary' => true,
                'notary_form' => 'acknowledgment',
            ],
            'clauses' => [
                'affirmations' => [
                    'Claimant asks the Judge of Probate to note this satisfaction on the margin of the record of the Verified Statement of Lien (Ala. Code § 35-11-231(a)).',
                ],
            ],
            'notes' => [
                'Once the lien is fully satisfied, the holder must acknowledge satisfaction on the margin of the record in the probate office; a holder paid in full who does not do so within 30 days after a written demand is liable to anyone injured, for at least $200 (Ala. Code § 35-11-231).',
                'Undecided: whether the probate office accepts this recorded satisfaction in place of an entry on the margin of the record; until counsel says otherwise, staff ask the probate office before recording it.',
                'Undecided: whether the probate office accepts the shared acknowledgment in place of the Ala. Code § 35-4-29 form (an acknowledgment taken in another state in that state\'s form is accepted, § 35-4-26(b)); until counsel says otherwise, staff use the shared acknowledgment.',
            ],
        ],
        'prelim_notice' => [
            'title' => 'Notice to Owner Before Furnishing Materials',
            'statute' => 'Ala. Code § 35-11-210',
            'body' => 'documents.lien.letters.bodies.al-materialman-notice',
            'template_version' => 1,
            'sections' => [
                'amount' => 'estimate',
            ],
            'service' => [
                'recipients' => ['owner'],
                'days_after' => null,
                'method' => 'certified_mail',
            ],
            'notes' => [
                'For a materialman selling to a contractor or subcontractor, sent before the material is furnished: it gives a lien for the full price stated, even beyond what the owner still owes the contractor, unless the owner or the owner\'s agent objects in writing before the material is used (Ala. Code § 35-11-210).',
                'Without it, a materialman\'s lien reaches only the unpaid balance the owner owes the contractor (Ala. Code § 35-11-210).',
                'The section has the notice specify the material and its prices: put the material in the description of work and the price in Document details (estimated price) before sending.',
            ],
        ],
        'noi' => [
            'title' => 'Notice of Claim and Intent to File Lien',
            'statute' => 'Ala. Code § 35-11-218',
            'sections' => ['demand_days' => 10],
            'clauses' => [
                // House wording that tracks § 35-11-218 (the claimant "claims a lien on such
                // building or improvement, setting forth the amount thereof, for what, and from
                // whom it is owing"); the statute prescribes no form. The generic body's first
                // paragraph names the claimant, the person it contracted with and the amount.
                // Keep the 10 days in step with demand_days.
                'demand' => 'Claimant claims a lien on the building or improvement on the property described above for the amount stated above. That amount is owing to Claimant for the labor, services or materials described above, from the person Claimant contracted with. After this notice, any unpaid balance in the hands of the owner or proprietor is held subject to the lien (Ala. Code § 35-11-218). Unless the amount is paid in full within 10 days after the date of this notice, Claimant will file its Verified Statement of Lien in the office of the Judge of Probate of the county where the property is situated.',
            ],
            'service' => [
                'recipients' => ['owner', 'gc'],
                'days_after' => null,
                'method' => 'certified_mail',
                'proof' => 'declaration',
            ],
            'notes' => [
                'Not needed for material the owner was notified of in advance with the Notice to Owner Before Furnishing Materials (Ala. Code § 35-11-218).',
                'The statute names the owner or proprietor, or the owner\'s agent; the general contractor gets a copy too, as on the 2026 Baldwin County notice.',
                'The statute sets no form, method or waiting period; the 10-day demand is a courtesy, so send the notice early enough to file within the § 35-11-215 deadline.',
            ],
        ],
    ],
];
