<?php

/*
 * Colorado: C.R.S. Title 38, art. 22 (General Mechanics' Lien).
 *
 * Lien statement: § 38-22-109 ((1) filed for record with the county clerk and
 * recorder of the county where the property, or its principal part, is
 * situated, stating (a) the owner or reputed owner, or that the name is not
 * known; (b) the person claiming the lien, the person who furnished the
 * laborers or materials or performed the labor and, for a subcontractor or
 * its assignee, the contractor, or that the contractor's name is not known;
 * (c) a description of the property sufficient to identify it; (d) the
 * amount due or owing; (2) "signed and sworn to by the party, or by one of
 * the parties, claiming such lien, or by some other person in his or their
 * behalf, to the best knowledge, information, and belief of the affiant";
 * (4) labor alone by the day or piece: within two months after completion of
 * the improvement; (5) every other claimant: within four months after its
 * last labor or materials; (6) new or amended statements within those
 * periods; (10) a recorded notice can extend them). The statute prescribes
 * the contents, not a form, so the generic body carries them.
 *
 * Notice of intent: § 38-22-109(3) (served on the owner or reputed owner or
 * the owner's agent and the principal or prime contractor or its agent at
 * least ten days before the lien statement is filed, by personal service or
 * registered or certified mail, return receipt requested, to the last-known
 * address; "an affidavit of such service or mailing at least ten days before
 * filing of the lien statement with the county clerk and recorder shall be
 * filed for record with said statement and shall constitute proof of such
 * service"). No wording is prescribed for the notice itself.
 *
 * Enforcement: § 38-22-110 (action commenced, and notice of it recorded,
 * within six months after the last work or materials or completion).
 * Release: § 38-22-118 (acknowledgment of satisfaction entered of record;
 * $10 a day after ten days from a written request). Excessive claims:
 * § 38-22-128 (good-faith subsections (2)-(3) added by SB 26-074, effective
 * 2026-08-12) and § 38-35-109(3). No preliminary notice on private work;
 * § 38-22-102(4)-(6) lets a claimant other than the principal contractor
 * notify the owner, who must then withhold; the § 38-22-105.5 residential
 * notice comes from the building permit office. Recording: § 30-10-406(3)(a)
 * (1" top, 1/2" side and bottom margins); § 30-1-103(1) and § 30-10-421(1)
 * ($40 per document plus surcharges from 2025-07-01). Notary: §§ 24-21-515 to
 * 24-21-517 (the § 24-21-516 short forms are sufficient, not mandatory; a
 * rectangular stamp with the notary's ID number and commission expiration);
 * § 38-35-101(2) (acknowledgment form as prima facie evidence, not mandatory).
 *
 * Verified against the 2026 Colorado Revised Statutes published by the Office
 * of Legislative Legal Services (olls.info, current through the 2026 regular
 * session) on 2026-09-30; leg.colorado.gov links the C.R.S. to LexisNexis and
 * law.justia.com refused automated reading.
 *
 * Archive (2026): the Boulder and Denver notices of intent were notarized and
 * carried a certificate of delivery; the Denver affidavit of service was a
 * separate notarized affidavit signed by staff. The Weld County self-serve
 * statement was verified "to the best of their knowledge" under a notary
 * acknowledgment: that knowledge standard is the statute's own, but an
 * acknowledgment is not an oath, so the statement here takes a jurat.
 */

return [
    'state' => 'CO',
    'state_name' => 'Colorado',
    'recording' => [
        'filing_office' => ['label' => 'County Clerk and Recorder', 'method' => 'erecord'],
        'parcel_label' => 'Parcel Number',
        'fee_note' => '$43 per document, any number of pages, from July 1, 2025: the $40 recording fee plus $3 in surcharges (C.R.S. § 30-1-103(1), § 30-10-421(1)). E-recording vendors add their own fees.',
        'notes' => [
            'File with the county clerk and recorder of the county where the property, or its principal part, is located (C.R.S. § 38-22-109(1)).',
            'Record within four months after the claimant last furnished labor, laborers or materials; a claim for labor alone by the day or piece must be recorded within two months after the improvement is completed (C.R.S. § 38-22-109(4), (5)). A notice recorded under § 38-22-109(10) can extend the time.',
            'The lien lapses unless a foreclosure action is started, and a notice of it recorded, within six months after the last work or materials or the completion of the improvement (C.R.S. § 38-22-110).',
            'A lien knowingly filed for more than is due is forfeited, and the claimant owes the person it was filed against the costs and attorney fees (C.R.S. § 38-22-128(1)). From August 12, 2026, an amount the claimant reasonably believes in good faith is due counts as due, even if disputed (§ 38-22-128(2), (3)).',
            'Knowingly recording a groundless lien, or one with a material misstatement or false claim, makes the claimant liable to the owner for $1,000 or the actual damages, whichever is greater, plus attorney fees (C.R.S. § 38-35-109(3)).',
            'Recorders require a 1-inch top margin and 1/2-inch side and bottom margins (C.R.S. § 30-10-406(3)(a)); these documents exceed that.',
            'A Colorado notary\'s stamp must be rectangular and show the notary\'s name, ID number and commission expiration date with the words "State of Colorado" and "Notary Public"; embossed seals are not allowed (C.R.S. § 24-21-517).',
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
            'title' => 'Statement of Lien',
            'statute' => 'C.R.S. § 38-22-109',
            'template_version' => 1,
            'sections' => [
                'amount' => 'breakdown',
                'gc' => true,
                'hiring_party' => true,
                'prior_notice' => false,
            ],
            'clauses' => [
                // House wording for the § 38-22-109 contents and steps; the statute prescribes no text.
                'affirmations' => [
                    'Claimant, named above, is the person who furnished the laborers or materials, or performed the labor, for which this lien is claimed (C.R.S. § 38-22-109(1)(b)).',
                    'If Claimant is a subcontractor, the name of the contractor is stated above (C.R.S. § 38-22-109(1)(b)).',
                    'The amount claimed above is due and owing to Claimant (C.R.S. § 38-22-109(1)(d)).',
                    'Claimant served a notice of intent to file a lien statement at least ten days before filing this statement. The affidavit of that service is recorded with this statement (C.R.S. § 38-22-109(3)).',
                    'This statement is filed within four months after Claimant last furnished labor, laborers or materials, or within two months after the improvement was completed for labor alone by the day or piece (C.R.S. § 38-22-109(4), (5)).',
                ],
            ],
            'attachments' => [
                'Affidavit of service of the notice of intent to file a lien statement, recorded with this statement (C.R.S. § 38-22-109(3)).',
            ],
            'notes' => [
                'Sign and swear to the statement before a notary (jurat); C.R.S. § 38-22-109(2) wants it "signed and sworn to". A 2026 self-serve Weld County statement used a notary acknowledgment instead, which is not an oath.',
                'Attach the notarized affidavit of service of the notice of intent behind the statement so both record together (C.R.S. § 38-22-109(3)).',
                'If the owner\'s name, or the name of the contractor a subcontractor worked under, is not known, the statement must say so (C.R.S. § 38-22-109(1)(a), (b)); find the names before filing.',
                'On an existing single-family home or an owner-occupied residence, the owner has a defense if it has paid the principal contractor in full (C.R.S. § 38-22-102(3.5), § 38-22-113(4)).',
                'Colorado requires no service of the recorded statement; staff mail the owner a copy with the notice-of-recording letter.',
            ],
        ],
        'lien_release' => [
            'title' => 'Release of Lien',
            'statute' => 'C.R.S. § 38-22-118',
            'execution' => [
                'verification' => 'acknowledged',
                'notary' => true,
                'notary_form' => 'acknowledgment',
            ],
            'clauses' => [
                'affirmations' => [
                    'This release is Claimant\'s acknowledgment of satisfaction of the lien, entered of record under C.R.S. § 38-22-118.',
                ],
            ],
            'notes' => [
                'Once the lien amount and the costs of recording the lien and this release (and of any suit) are paid, the claimant must record the release within ten days after a written request from anyone with an interest in the property, or pay $10 a day (C.R.S. § 38-22-118).',
            ],
        ],
        'prelim_notice' => [
            'title' => 'Preliminary Notice',
            'statute' => 'C.R.S. § 38-22-101 et seq.',
            'service' => [
                'recipients' => ['owner', 'gc'],
                'days_after' => null,
                'method' => 'certified_mail',
            ],
            'notes' => [
                'Colorado has no required preliminary notice on private work; this is a courtesy notice of furnishing.',
                'A claimant other than the principal contractor may tell the owner or the construction lender what it furnished, for whom and for how much; whoever hired the principal contractor must then hold back enough from it to cover the claim (C.R.S. § 38-22-102(4)-(6)).',
                'Undecided: whether this notice should be rebuilt to C.R.S. § 38-22-102(4), with the value furnished so far as well as the whole and delivery by hand under § 38-22-102(5), so that it triggers the owner\'s duty to withhold; until counsel says otherwise, it goes by certified mail as a courtesy.',
                'The residential lien-law notice in C.R.S. § 38-22-105.5 is mailed by the building permit office, not the contractor, so there is no contractor disclosure to send.',
            ],
        ],
        'noi' => [
            'title' => 'Notice of Intent to File Lien Statement',
            'statute' => 'C.R.S. § 38-22-109(3)',
            'template_version' => 1,
            'sections' => ['demand_days' => 10],
            'clauses' => [
                // House wording tracking § 38-22-109(3); the statute prescribes no text for the notice.
                'demand' => 'Claimant gives notice that it intends to file a lien statement against the property described above with the county clerk and recorder of the county where the property is located. Unless the amount stated above is paid, Claimant will file it no sooner than ten days after this notice is served.',
            ],
            // Only the affidavit of service is sworn; the notice itself is not notarized.
            'execution' => [
                'verification' => 'none',
                'notary' => false,
                'notary_form' => null,
            ],
            'service' => [
                'recipients' => ['owner', 'gc'],
                'days_after' => null,
                'method' => 'certified_mail',
                'proof' => 'affidavit',
            ],
            'notes' => [
                'Required before every Colorado lien statement: serve it on the owner or reputed owner (or the owner\'s agent) and on the principal contractor (or its agent) at least ten days before the lien statement is filed (C.R.S. § 38-22-109(3)).',
                'Serve by personal service or by registered or certified mail, return receipt requested, to each person\'s last-known address; the ten days count from the day of service or mailing (C.R.S. § 38-22-109(3)).',
                'The notice itself need not be notarized. The affidavit of service must be sworn before a notary and recorded with the lien statement; it is the proof of service (C.R.S. § 38-22-109(3)).',
            ],
        ],
    ],
];
