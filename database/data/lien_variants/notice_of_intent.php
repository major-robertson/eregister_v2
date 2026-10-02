<?php

/*
 * Notice of intent to lien, per state, for "/liens/notice-of-intent-to-lien/{state}"
 * (EREG-13, SEO Phase D item D7). Twenty states: the ten whose rule row has a
 * notice-of-intent lead time and the ten busiest.
 *
 * Every statement comes from the Phase A statute research (public_notes and
 * deadline_row_checks), the state's lien_documents data file (the noi entry
 * and its notes) or the Phase D depth file. Where they do not settle a point,
 * the text says to confirm with counsel; never fill one in with a guess.
 *
 * KY, MD, NJ and TN state the statute's rule in words: their
 * lien_deadline_rules noi rows count back from filing, which the statutes do
 * not (EREG-91). Do not quote those rows here.
 *
 * Entry contract:
 *   'status'   => 'required' (every claimant) | 'required_some' (some claimants or projects)
 *                 | 'optional' (helps but is never required) | 'not_required'
 *   'rule'     => the statute's rule in plain words, without the cite
 *   'cite'     => the section the rule comes from
 *   'timing'   => short value for the "When" fact
 *   'meta'     => fragment for the meta description: "{State} notice of intent to lien: {meta}."
 *   'who', 'to', 'how' => who sends it, who receives it, how it is delivered; null on
 *                 not-required states, where the page describes eRegister's courtesy letter
 *                 from the lien_documents noi entry instead
 *   'contents' => what the statute says the notice must state, or null (the page lists
 *                 the elements of eRegister's letter); 'contents_cite' its source
 *   'notes'    => further plain-English facts, each with its cite
 */

return [
    'AR' => [
        'status' => 'required',
        'rule' => 'Arkansas requires every claimant to give the owner ten days\' notice before filing a lien.',
        'cite' => 'Ark. Code Ann. § 18-44-114',
        'timing' => 'At least 10 days before the lien is filed',
        'meta' => 'every claimant gives the owner 10 days\' notice before filing',
        'who' => 'Every claimant, including the general contractor.',
        'to' => 'The property owner.',
        'how' => 'Our research does not confirm a required delivery method. Certified mail with a return receipt gives you proof of the date. Confirm the method with counsel.',
        'contents' => null,
        'contents_cite' => null,
        'notes' => [
            'The lien itself must include an affidavit with copies of the notices you sent and proof of service (Ark. Code Ann. § 18-44-117).',
            'On commercial work, subcontractors and suppliers also send the owner and the contractor a notice of nonpayment before 75 days have passed from furnishing (Ark. Code Ann. § 18-44-115(b)). That is a separate notice. Whether the 75 days run from first or last furnishing is not settled, so confirm with counsel.',
        ],
    ],

    'CO' => [
        'status' => 'required',
        'rule' => 'Colorado requires a notice of intent before every lien statement. Serve it at least ten days before the lien statement is filed.',
        'cite' => 'C.R.S. § 38-22-109(3)',
        'timing' => 'At least 10 days before the lien statement is filed',
        'meta' => 'required at least 10 days before the lien statement is filed',
        'who' => 'Every claimant.',
        'to' => 'The owner or reputed owner, or the owner\'s agent, and the principal contractor or its agent.',
        'how' => 'Personal service, or registered or certified mail with return receipt requested, to each person\'s last-known address. The ten days count from the day of service or mailing.',
        'contents' => null,
        'contents_cite' => null,
        'notes' => [
            'Record an affidavit of service with the lien statement. It is the proof that the notice went out (C.R.S. § 38-22-109(3)).',
            'The notice itself does not need a notary. The affidavit of service is sworn before one.',
        ],
    ],

    'KY' => [
        'status' => 'required_some',
        'rule' => 'Kentucky requires a notice of intention from subcontractors and suppliers who did not contract with the owner. It counts forward from your last day of work or materials, not back from the lien. Send it within 120 days after last furnishing on claims over $1,000, and within 75 days on smaller claims.',
        'cite' => 'KRS 376.010(4), (5)',
        'timing' => 'Within 120 days after last furnishing (75 days on smaller claims)',
        'meta' => 'subcontractors and suppliers send it within 120 days after last furnishing',
        'who' => 'Subcontractors and suppliers who did not contract directly with the owner.',
        'to' => 'The property owner.',
        'how' => 'Our research does not confirm a required delivery method. Certified mail with a return receipt gives you proof of the date. Confirm the method with counsel.',
        'contents' => [
            'That you intend to hold the property liable',
            'The amount you will claim',
        ],
        'contents_cite' => 'KRS 376.010(4)',
        'notes' => [
            'On an owner-occupied single or double family home, the notice is due within 75 days after last furnishing, whatever the amount. The lien does not cover amounts the owner paid before receiving it (KRS 376.010(5)).',
            'The lien statement itself is due within 6 months after last furnishing. Mail the owner a copy within 7 days after filing, or the lien is dissolved (KRS 376.080).',
        ],
    ],

    'LA' => [
        'status' => 'optional',
        'rule' => 'Louisiana does not require a notice of intent before every claim. On residential work with no notice of contract filed, a notice of nonpayment sent at least 10 days before filing extends the filing deadline to 70 days.',
        'cite' => 'La. R.S. 9:4822(D)',
        'timing' => 'At least 10 days before filing, where you use it',
        'meta' => 'optional, but on residential work it can extend the filing deadline to 70 days',
        'who' => 'Subcontractors, sub-subcontractors and suppliers to a contractor or subcontractor. Whether a supplier selling directly to the owner can use it is not settled, so confirm with counsel.',
        'to' => 'Our research does not confirm who must receive it. Send it to the owner and the contractor, and confirm with counsel.',
        'how' => 'Our research does not confirm a required delivery method. Certified mail with a return receipt gives you proof of the date.',
        'contents' => null,
        'contents_cite' => null,
        'notes' => [
            'Statements of claim or privilege are filed with the parish recorder of mortgages (La. R.S. 9:4831).',
            'When a notice of contract was filed, the deadlines change: subcontractors and suppliers have 30 days after a notice of termination or 6 months after substantial completion (La. R.S. 9:4822(B), (C)).',
            'These rules were rewritten effective 2020, so confirm with counsel.',
        ],
    ],

    'MD' => [
        'status' => 'required_some',
        'rule' => 'Maryland requires a notice of intention to claim a lien from subcontractors and suppliers who did not contract with the owner. It counts forward: give it within 120 days after doing the work or furnishing the materials.',
        'cite' => 'Md. Code, Real Prop. § 9-104',
        'timing' => 'Within 120 days after the work or materials',
        'meta' => 'subcontractors and suppliers give it within 120 days after the work',
        'who' => 'Subcontractors and suppliers who did not contract directly with the owner.',
        'to' => 'The property owner.',
        'how' => 'Registered or certified mail, return receipt requested, or personal delivery. If the owner cannot be reached, it may be posted on the building before a witness (Md. Code, Real Prop. § 9-104(c), (e)).',
        'contents' => null,
        'contents_cite' => null,
        'notes' => [
            'On a single-family home built on the owner\'s land for the owner\'s own residence, a subcontractor has no lien if the owner paid the contractor in full before getting the notice.',
            'A Maryland lien is established by a petition in the circuit court, filed within 180 days after the work or materials (Md. Code, Real Prop. § 9-105). It is a court case, so confirm with counsel.',
        ],
    ],

    'MO' => [
        'status' => 'required_some',
        'rule' => 'Missouri requires every claimant except the original contractor to give the owner at least 10 days\' notice before filing a lien.',
        'cite' => 'RSMo § 429.100',
        'timing' => 'At least 10 days before the lien is filed',
        'meta' => 'required of all but the original contractor, 10 days before filing',
        'who' => 'Every claimant except the original contractor who dealt directly with the owner.',
        'to' => 'The owner or the owner\'s agent.',
        'how' => 'An officer who serves civil process, or any adult who would be a competent witness, serves it. Proof is the officer\'s return or the server\'s affidavit. Whether certified mail alone is enough is not settled, so confirm with counsel.',
        'contents' => [
            'The amount of the claim',
            'Who owes it',
        ],
        'contents_cite' => 'RSMo § 429.100',
        'notes' => [
            'Do not file until ten full days after service.',
            'If the owner lives outside Missouri with no agent in the county, or cannot be found, the notice may be recorded with the county recorder of deeds instead (RSMo § 429.110).',
        ],
    ],

    'NV' => [
        'status' => 'required_some',
        'rule' => 'Nevada requires a 15-day notice of intent to lien on single-family and multifamily residential work. Every claimant except laborers serves it before recording the notice of lien. It does not apply to nonresidential projects.',
        'cite' => 'NRS 108.226(6), (7)',
        'timing' => 'At least 15 days before recording (residential work)',
        'meta' => 'required on residential work at least 15 days before recording',
        'who' => 'Every claimant except laborers, on single-family or multifamily residential work.',
        'to' => 'The owner and the reputed prime contractor.',
        'how' => 'In person or by certified mail.',
        'contents' => [
            'Substantially the same information as the notice of lien',
        ],
        'contents_cite' => 'NRS 108.226(6)',
        'notes' => [
            'Serving it adds 15 days to the time to record the notice of lien.',
        ],
    ],

    'NJ' => [
        'status' => 'not_required',
        'rule' => 'New Jersey has no notice of intent before a lien. On residential work the statute requires a different step: lodge a Notice of Unpaid Balance and Right to File Lien with the county clerk within 60 days after last furnishing, then serve a demand for arbitration within 10 days. Commercial work needs no notice before the lien claim.',
        'cite' => 'N.J.S.A. 2A:44A-21',
        'timing' => 'None. Residential work needs a Notice of Unpaid Balance within 60 days after last furnishing',
        'meta' => 'none, but residential work needs a Notice of Unpaid Balance within 60 days',
        'who' => null,
        'to' => null,
        'how' => null,
        'contents' => null,
        'contents_cite' => null,
        'notes' => [
            'The arbitrator rules on whether a lien may be filed and for how much. The lien claim is then due within 10 days after the decision, and never later than 120 days after last furnishing (N.J.S.A. 2A:44A-6, 2A:44A-21).',
            'The free form on this page is a payment demand, not the Notice of Unpaid Balance.',
        ],
    ],

    'ND' => [
        'status' => 'required',
        'rule' => 'North Dakota requires written notice to the owner that a lien will be claimed, sent by certified mail at least 10 days before the lien is recorded.',
        'cite' => 'N.D.C.C. § 35-27-02(4)',
        'timing' => 'At least 10 days before the lien is recorded',
        'meta' => 'notify the owner by certified mail at least 10 days before recording',
        'who' => 'Claimants hired through a contractor or subcontractor. The statute does not clearly cover a claimant in direct contract with the owner. Send it anyway and confirm with counsel.',
        'to' => 'The property owner.',
        'how' => 'Certified mail.',
        'contents' => null,
        'contents_cite' => null,
        'notes' => [],
    ],

    'TN' => [
        'status' => 'not_required',
        'rule' => 'Tennessee has no notice of intent before a lien. Its statutory notice is a notice of nonpayment. A remote contractor serves it within 90 days after the last day of each month in which work or materials went unpaid.',
        'cite' => 'Tenn. Code Ann. § 66-11-145',
        'timing' => 'None. Remote contractors serve a notice of nonpayment within 90 days after each unpaid month',
        'meta' => 'none, but remote contractors serve a notice of nonpayment for each unpaid month',
        'who' => null,
        'to' => null,
        'how' => null,
        'contents' => null,
        'contents_cite' => null,
        'notes' => [
            'The notice of nonpayment goes to the owner and to the prime contractor the remote contractor worked under. It is not required on one- to four-family residential units. Missing it bars the lien except for retainage (Tenn. Code Ann. § 66-11-145).',
            'The free form on this page is a payment demand, not the notice of nonpayment.',
        ],
    ],

    'TX' => [
        'status' => 'not_required',
        'rule' => 'Texas does not require a notice of intent before a lien. The statutory steps are the monthly notice of claim from subcontractors and suppliers and the lien affidavit itself.',
        'cite' => 'Tex. Prop. Code §§ 53.054, 53.056',
        'timing' => 'None required',
        'meta' => 'not required before the lien affidavit',
        'who' => null,
        'to' => null,
        'how' => null,
        'contents' => null,
        'contents_cite' => null,
        'notes' => [
            'Subcontractors and suppliers still owe the Notice of Claim for Unpaid Labor or Materials by the 15th day of the 3rd month after each unpaid month, or the 2nd month on residential work (Tex. Prop. Code § 53.056(a-1)).',
        ],
    ],

    'CA' => [
        'status' => 'not_required',
        'rule' => 'California does not require a notice of intent before a lien. The statutory steps are the preliminary notice and the claim of mechanics lien.',
        'cite' => 'Cal. Civ. Code §§ 8200-8216, 8416',
        'timing' => 'None required',
        'meta' => 'not required before the claim of mechanics lien',
        'who' => null,
        'to' => null,
        'how' => null,
        'contents' => null,
        'contents_cite' => null,
        'notes' => [],
    ],

    'FL' => [
        'status' => 'not_required',
        'rule' => 'Florida does not require a notice of intent before a claim of lien. The statutory steps are the notice to owner and the claim of lien.',
        'cite' => 'Fla. Stat. §§ 713.06, 713.08',
        'timing' => 'None required',
        'meta' => 'not required before the claim of lien',
        'who' => null,
        'to' => null,
        'how' => null,
        'contents' => null,
        'contents_cite' => null,
        'notes' => [],
    ],

    'NY' => [
        'status' => 'not_required',
        'rule' => 'New York does not require a notice of intent before a notice of lien. Sending one does not extend the filing deadline.',
        'cite' => 'N.Y. Lien Law § 10',
        'timing' => 'None required',
        'meta' => 'not required, and sending one does not extend the filing deadline',
        'who' => null,
        'to' => null,
        'how' => null,
        'contents' => null,
        'contents_cite' => null,
        'notes' => [
            'The notice of lien is due within eight months after last furnishing, or four months for a single family dwelling (N.Y. Lien Law § 10).',
        ],
    ],

    'GA' => [
        'status' => 'not_required',
        'rule' => 'Georgia does not require a notice of intent before a claim of lien.',
        'cite' => 'O.C.G.A. § 44-14-361.1',
        'timing' => 'None required',
        'meta' => 'not required before the claim of lien',
        'who' => null,
        'to' => null,
        'how' => null,
        'contents' => null,
        'contents_cite' => null,
        'notes' => [],
    ],

    'NC' => [
        'status' => 'not_required',
        'rule' => 'North Carolina does not require a notice of intent before a claim of lien.',
        'cite' => 'N.C. Gen. Stat. § 44A-12',
        'timing' => 'None required',
        'meta' => 'not required before the claim of lien',
        'who' => null,
        'to' => null,
        'how' => null,
        'contents' => null,
        'contents_cite' => null,
        'notes' => [],
    ],

    'AZ' => [
        'status' => 'not_required',
        'rule' => 'Arizona does not require a notice of intent before a lien. The statutory steps are the preliminary twenty day notice and the notice and claim of lien.',
        'cite' => 'A.R.S. §§ 33-992.01, 33-993',
        'timing' => 'None required',
        'meta' => 'not required before the notice and claim of lien',
        'who' => null,
        'to' => null,
        'how' => null,
        'contents' => null,
        'contents_cite' => null,
        'notes' => [],
    ],

    'WA' => [
        'status' => 'not_required',
        'rule' => 'Washington does not require a notice of intent before a claim of lien. The statutory steps are the notice to owner, where it applies, and the claim of lien.',
        'cite' => 'RCW 60.04.031, 60.04.091',
        'timing' => 'None required',
        'meta' => 'not required before the claim of lien',
        'who' => null,
        'to' => null,
        'how' => null,
        'contents' => null,
        'contents_cite' => null,
        'notes' => [
            'Quote the exact amount owed. A frivolous or clearly excessive claim can cost you the other side\'s attorney fees (RCW 60.04.081).',
        ],
    ],

    'PA' => [
        'status' => 'required_some',
        'rule' => 'Pennsylvania requires every subcontractor to give the owner formal written notice of its intention to file a lien claim at least 30 days before filing. Without it the claim is invalid.',
        'cite' => '49 P.S. § 1501(b.1)',
        'timing' => 'At least 30 days before the claim is filed (subcontractors)',
        'meta' => 'subcontractors give formal notice at least 30 days before filing',
        'who' => 'Every subcontractor, on new construction and on alterations and repairs. A contractor who dealt directly with the owner does not have to send one.',
        'to' => 'The owner or the owner\'s agent.',
        'how' => 'First-class, registered or certified mail. Or have an adult serve it the way a writ of summons is served. If neither works, post it on a conspicuous public part of the property (49 P.S. § 1501(d)).',
        'contents' => [
            'The claimant\'s name',
            'The person it contracted with',
            'The amount due',
            'The general nature of the labor or materials',
            'The date the work was completed',
            'A description of the property',
        ],
        'contents_cite' => '49 P.S. § 1501(c)',
        'notes' => [
            'The claim is due within six months after completion, so send this notice within five months.',
            'It is not needed when the claim is filed in answer to a rule to file (49 P.S. § 1506).',
        ],
    ],

    'IL' => [
        'status' => 'not_required',
        'rule' => 'Illinois does not require a notice of intent before a claim for lien. The statutory steps are the subcontractor\'s 90-day notice and the claim for lien.',
        'cite' => '770 ILCS 60/7, 60/24',
        'timing' => 'None required',
        'meta' => 'not required before the claim for lien',
        'who' => null,
        'to' => null,
        'how' => null,
        'contents' => null,
        'contents_cite' => null,
        'notes' => [
            'Send it early enough that the payment window ends well inside the 4 months to record the claim for lien against lenders and buyers (770 ILCS 60/7(a)).',
        ],
    ],
];
