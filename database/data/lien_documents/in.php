<?php

/*
 * Indiana: IC 32-28-3 (Mechanic's Liens) and the recorder rules of IC 36-2-11.
 *
 * Mechanic's lien: IC 32-28-3-3 (a person who wishes to acquire a lien "must
 * file in duplicate a sworn statement and notice of the person's intention to
 * hold a lien upon the property for the amount of the claim" in the recorder's
 * office of the county, not later than 90 days after performing labor or
 * furnishing materials or machinery, (a), or 60 days for a Class 2 structure
 * (IC 22-12-1-5: a townhouse or a building with one or two dwelling units, and
 * its outbuildings) or an improvement on the same real estate auxiliary to
 * one, (b); the statement "must specifically set forth" the amount claimed,
 * the claimant's name and address, the owner's name and "latest address as
 * shown on the property tax records of the county", and the legal description
 * and street and number, (c); the owner's name and legal description suffice
 * if "substantially as set forth in the latest entry in the transfer books" of
 * the county auditor, (c); the recorder mails one duplicate to the owner, first
 * class, within three business days after recording and collects $2, (d)).
 * Recording creates the lien (IC 32-28-3-5(b)). Enforcement: a complaint within
 * one year after recording, or the lien is void (IC 32-28-3-6). Owner-occupied
 * dwellings: IC 32-28-3-1(h) (alteration or repair: a written notice "of the
 * delivery or work and of the existence of lien rights" to the occupying owner
 * within 30 days after the first delivery or labor performed) and (i)
 * (original construction for the owner's intended occupancy: the same notice
 * to the owner named in the county auditor's transfer books, and a copy filed
 * in the recorder's office, within 60 days); either is a condition precedent
 * to the lien. Release: IC 32-28-1-1 (release, discharge and satisfy of record
 * once paid; a partial release says so on its face) and IC 32-28-6-1 (within
 * 15 days after the owner's demand). Recording: IC 36-2-11-15 (the preparer's
 * name and the Social Security number affirmation at the conclusion of the
 * instrument, verbatim in clauses/in-recording-affirmation), IC 36-2-11-16
 * (printed names beneath signatures), IC 36-2-11-16.5 (clean margins of at
 * least 2 inches top and bottom on the first and last pages; see the notes),
 * IC 36-2-7-10 (fees). Notarial certificates: IC 33-42-9-12 (no statutory
 * short form; the certificate shows the county and state of the act, the
 * officer's title, the commission expiration date and the Indiana county of
 * the commission). Verified against the 2026 Indiana Code as published by the
 * Indiana General Assembly (iga.in.gov/ic/2026/Title_32.html, Title_36.html,
 * Title_33.html and Title_22.html) on 2026-09-30.
 */

return [
    'state' => 'IN',
    'state_name' => 'Indiana',
    'recording' => [
        'filing_office' => ['label' => 'County Recorder (county where the real estate is located)', 'method' => 'erecord'],
        'parcel_label' => 'Parcel Number',
        // IC 36-2-11-16.5(b)(2)(A) wants the first page's top two inches clean,
        // so the preparer / return-to block prints below the recorder's rule.
        'preparer_in_space' => false,
        'fee_note' => '$25 per instrument plus any fee the county adds by ordinance (IC 36-2-7-10).',
        'notes' => [
            'File the lien in the recorder\'s office of the county not later than 60 days after performing labor or furnishing materials or machinery for a Class 2 structure (a townhouse, or a building with one or two dwelling units, and its outbuildings: IC 22-12-1-5) or an improvement on the same real estate auxiliary to one, and not later than 90 days after in any other case (IC 32-28-3-3(a), (b)).',
            'A complaint to foreclose must be filed not later than one year after the lien is recorded, or the lien is void (IC 32-28-3-6).',
            'IC 36-2-11-16.5: the recorder may receive an instrument if it is on white paper of at least 20-pound weight, no larger than 8 1/2 by 14 inches, typed in black ink of at least 10 point, with clean margins of at least 2 inches at the top and bottom and 1/2 inch on each side on the first and last pages (1/2 inch all around on other pages). The generated instrument keeps the top 3 inches of page 1 clean, but its bottom margins are 1 inch with the page number in them and its last page has a 1-inch top; confirm the recorder accepts it.',
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
            'title' => 'Sworn Statement and Notice of Intention to Hold Mechanic\'s Lien',
            'statute' => 'IC 32-28-3-3',
            'template_version' => 1,
            'sections' => [
                'amount' => 'breakdown',
                'license' => true,
                'gc' => true,
                'hiring_party' => true,
                'prior_notice' => true,
            ],
            'clauses' => [
                // House wording that tracks IC 32-28-3-3(a)-(c); not statutory text.
                'affirmations' => [
                    'Claimant gives notice of its intention to hold a lien upon the real estate and improvements described above for the amount of its claim stated above.',
                    'The owner\'s name and address stated above are the owner\'s name and latest address as shown on the property tax records of the county.',
                    'Claimant files this statement and notice in the recorder\'s office of the county within the time IC 32-28-3-3 allows. That is not later than sixty (60) days after performing labor or furnishing materials or machinery for a Class 2 structure (as defined in IC 22-12-1-5) or an improvement on the same real estate auxiliary to a Class 2 structure, and not later than ninety (90) days after in any other case.',
                ],
                // IC 36-2-11-15(c), (d), verbatim, at the conclusion of the instrument.
                'after_execution' => ['documents.lien.instruments.clauses.in-recording-affirmation'],
            ],
            'notes' => [
                'The owner\'s address on the lien must be the owner\'s latest address as shown on the county property tax records (IC 32-28-3-3(c)): edit the Owner party\'s address to match before generating. The owner\'s name and the legal description are sufficient if they are substantially as set forth in the latest entry in the county auditor\'s transfer books.',
                'File the statement in duplicate. The recorder mails one duplicate to the owner, first class, within three business days after recording, to the address in the statement, and collects $2 from the claimant for it (IC 32-28-3-3(d)); staff still mail the owner a copy with the notice-of-recording letter.',
                'The Social Security number affirmation and the "This instrument was prepared by" statement print after the notary block (IC 36-2-11-15). Set config(\'lien.documents.preparer.attention\') to the staff member who prepares Indiana instruments; until then a blank prints where the name goes.',
                'An Indiana notary\'s certificate must also show the Indiana county of the notary\'s commission (IC 33-42-9-12(a)(5)(B)); the generic certificate has no line for it, so the notary writes or stamps it.',
                'Open question for Major: the prior-notice item prints for every claimant who did not contract with the owner, but Indiana requires that notice only on owner-occupied single or double family dwellings (IC 32-28-3-1(h), (i)); should it print on other jobs?',
                'Open question for Major: IC 32-28-3-3 names an attorney in good standing as the one who may verify and file the statement on a client\'s behalf; confirm eRegister may prepare and e-record it for a claimant who signs and swears to it.',
            ],
        ],
        'lien_release' => [
            'title' => 'Release of Mechanic\'s Lien',
            'statute' => 'IC 32-28-1-1',
            'execution' => [
                'verification' => 'acknowledged',
                'notary' => true,
                'notary_form' => 'acknowledgment',
            ],
            'clauses' => [
                // IC 36-2-11-15 applies to an instrument that disposes of a lien on property too.
                'after_execution' => ['documents.lien.instruments.clauses.in-recording-affirmation'],
            ],
            'notes' => [
                'Once the debt is paid, the lienholder must release, discharge and satisfy the lien of record (IC 32-28-1-1(b)) within 15 days after the owner demands it, or owe the owner the greater of actual damages or $10 a day (IC 32-28-6-1). If it has not released the lien 15 days after receiving a written demand sent by registered or certified mail, it also forfeits up to $500 plus costs and reasonable attorney\'s fees (IC 32-28-1-2).',
                'This template is a full release. A partial release must say on its face that it is a partial release and describe what part of the lien it releases (IC 32-28-1-1(c)).',
                'The Social Security number affirmation and the "This instrument was prepared by" statement print after the notary block (IC 36-2-11-15). Set config(\'lien.documents.preparer.attention\') to the staff member who prepares Indiana instruments; until then a blank prints where the name goes.',
                'An Indiana notary\'s certificate must also show the Indiana county of the notary\'s commission (IC 33-42-9-12(a)(5)(B)); the generic certificate has no line for it, so the notary writes or stamps it.',
            ],
        ],
        'prelim_notice' => [
            'title' => 'Notice of Delivery or Work and of the Existence of Lien Rights',
            'statute' => 'IC 32-28-3-1(h), (i)',
            'template_version' => 1,
            'sections' => [
                'amount' => 'estimate',
                'gc' => true,
                'first_furnish' => true,
            ],
            'service' => [
                'recipients' => ['owner'],
                'days_after' => 30,
                'method' => 'certified_mail',
            ],
            'clauses' => [
                // House wording that tracks IC 32-28-3-1(h), (i): a written notice "of the
                // delivery or work and of the existence of lien rights"; not statutory text.
                'before_signature' => [
                    'This is the written notice of the delivery or work described above, and of the existence of lien rights, that IC 32-28-3-1 requires. If the claimant is not paid, it may hold a lien on the dwelling and on the owner\'s interest in the land, to the extent of the value of the materials, labor or machinery it furnished, by filing a sworn statement and notice of intention to hold a lien in the county recorder\'s office (IC 32-28-3-3).',
                ],
            ],
            'notes' => [
                'Use only for an owner-occupied single or double family dwelling, when the claimant sold or furnished materials, labor or machinery on credit to anyone other than the occupying owner. Alteration or repair: furnish the notice to the occupying owner not later than 30 days after the first delivery or labor performed (IC 32-28-3-1(h)).',
                'Original construction of a single or double family dwelling for the owner\'s intended occupancy: furnish the notice to the owner named in the latest entry in the county auditor\'s transfer books, and file a copy in the recorder\'s office of the county, both not later than 60 days after the first delivery or labor performed (IC 32-28-3-1(i)).',
                'Furnishing the notice (and, for original construction, filing it) is a condition precedent to the lien. The statute names no delivery method; certified mail, return receipt requested, proves receipt.',
                'Open question for Major: will recorders take this letter (1-inch margins, no IC 36-2-11-15 statements) as the IC 32-28-3-1(i) recorder copy, given the 2-inch top and bottom margins IC 36-2-11-16.5 asks for on the first and last pages?',
            ],
        ],
        'noi' => [
            'title' => 'Notice of Intent to File a Mechanic\'s Lien',
            'statute' => 'IC 32-28-3',
            'sections' => ['demand_days' => 10],
            'notes' => [
                'Indiana has no pre-lien notice of intent; the lien itself is the sworn "statement and notice of intention to hold a lien" (IC 32-28-3-3). This is a demand courtesy; do not record it.',
            ],
        ],
    ],
];
