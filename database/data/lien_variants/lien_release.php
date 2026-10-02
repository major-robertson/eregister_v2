<?php

/*
 * Lien release, per state, for "/liens/lien-release/{state}" (EREG-13, SEO
 * Phase D item D7). The same twenty states as the notice-of-intent pages.
 *
 * Every statement comes from the Phase A statute research (public_notes,
 * wrongful_lien and corrections), the state's lien_documents data file (the
 * lien_release entry and its notes) or the Phase D depth file. A null
 * 'deadline' or 'penalty' means none of them confirms one: the page then
 * says to confirm with counsel. Never fill one in with a guess.
 *
 * Entry contract:
 *   'name'     => what the statute calls the release, in sentence case, or null for
 *                 the document title in the state's lien_documents file ("Release of Lien")
 *   'cite'     => the section that governs releasing the lien, or null
 *   'deadline' => when it must be given or recorded after payment, or null (unconfirmed)
 *   'deadline_detail' => one more sentence on the deadline, or null
 *   'penalty'  => what the claimant owes for not releasing, with its cite, or null (unconfirmed)
 *   'office'   => where it is recorded when that differs from the lien rule's filing office,
 *                 false when no source confirms it, or null to use the lien rule's office
 *   'notes'    => further plain-English facts, each with its cite
 */

return [
    'AR' => [
        'name' => null,
        'cite' => null,
        'deadline' => null,
        'deadline_detail' => null,
        'penalty' => null,
        'office' => null,
        'notes' => [],
    ],

    'CO' => [
        'name' => 'Release of lien',
        'cite' => 'C.R.S. § 38-22-118',
        'deadline' => 'Within 10 days after a written request, once the lien amount and the recording costs are paid',
        'deadline_detail' => 'Anyone with an interest in the property can make the request.',
        'penalty' => '$10 for each day of delay (C.R.S. § 38-22-118).',
        'office' => null,
        'notes' => [],
    ],

    'KY' => [
        'name' => null,
        'cite' => 'KRS 382.365',
        'deadline' => 'Within 30 days after the lien is satisfied',
        'deadline_detail' => null,
        'penalty' => 'After written notice, the owner can recover damages for each day of delay and attorney fees (KRS 382.365).',
        'office' => null,
        'notes' => [],
    ],

    'LA' => [
        'name' => 'Request to cancel the statement of claim',
        'cite' => 'La. R.S. 9:4833',
        'deadline' => null,
        'deadline_detail' => null,
        'penalty' => 'A claimant who, without reasonable cause, does not deliver a request to cancel an improperly filed or extinguished claim within 10 days of a written request owes the owner\'s damages and reasonable attorney fees (La. R.S. 9:4833(B)).',
        'office' => 'Parish recorder of mortgages',
        'notes' => [
            'Statements of claim are filed with the parish recorder of mortgages (La. R.S. 9:4831).',
            'These rules were rewritten effective 2020, so confirm with counsel.',
        ],
    ],

    'MD' => [
        'name' => null,
        'cite' => null,
        'deadline' => null,
        'deadline_detail' => null,
        'penalty' => null,
        'office' => false,
        'notes' => [
            'A Maryland lien is established by a petition in the circuit court for the county where the land is (Md. Code, Real Prop. § 9-105). How to release one depends on that case, so confirm with counsel.',
        ],
    ],

    'MO' => [
        'name' => 'Acknowledgment of satisfaction',
        'cite' => 'RSMo § 429.120',
        'deadline' => 'When asked, once the debt is paid',
        'deadline_detail' => 'File it with the clerk of the circuit court.',
        'penalty' => 'Refusing for ten days after payment and a request makes you liable for the injury it causes (RSMo § 429.130).',
        'office' => null,
        'notes' => [],
    ],

    'NV' => [
        'name' => 'Discharge or release of notice of lien',
        'cite' => 'NRS 108.2437',
        'deadline' => 'As soon as practicable, and no later than 10 days after the lien is satisfied',
        'deadline_detail' => null,
        'penalty' => 'The owner\'s actual damages or $100, whichever is greater, plus attorney\'s fees and costs (NRS 108.2437).',
        'office' => null,
        'notes' => [
            'It must be acknowledged before a notary when the notice of lien was recorded by microfilm or another photographic process (NRS 108.2433(2)).',
        ],
    ],

    'NJ' => [
        'name' => 'Certificate of discharge',
        'cite' => 'N.J.S.A. 2A:44A-30',
        'deadline' => 'Within 30 days after payment, satisfaction or settlement',
        'deadline_detail' => 'If any interested party demands it, within 7 days after the demand.',
        'penalty' => 'Costs, attorney\'s fees and damages (N.J.S.A. 2A:44A-30(e)).',
        'office' => null,
        'notes' => [
            'The certificate states the lien claim\'s filing date, book and page, the owner, the property and the person the work was for (N.J.S.A. 2A:44A-30(a)).',
            'Discharging the claim also discharges the Notice of Unpaid Balance filed with it (N.J.S.A. 2A:44A-33(e)).',
        ],
    ],

    'ND' => [
        'name' => null,
        'cite' => null,
        'deadline' => null,
        'deadline_detail' => null,
        'penalty' => null,
        'office' => null,
        'notes' => [],
    ],

    'TN' => [
        'name' => 'Release of lien',
        'cite' => 'Tenn. Code Ann. § 66-11-135',
        'deadline' => 'Within 30 days after the owner\'s written demand, once the lien is paid, expired or forfeited',
        'deadline_detail' => null,
        'penalty' => 'The owner\'s damages, costs and attorney\'s fees (Tenn. Code Ann. § 66-11-135(a)).',
        'office' => null,
        'notes' => [
            'Record it with the register of deeds where the notice of lien was recorded. The lien counts as released on the day the release is recorded (Tenn. Code Ann. § 66-11-135(b), (c)).',
        ],
    ],

    'TX' => [
        'name' => 'Release of lien',
        'cite' => 'Tex. Prop. Code § 53.152',
        'deadline' => 'Within 10 days after a written request, once the debt is paid',
        'deadline_detail' => 'The owner, the original contractor or whoever paid can make the request.',
        'penalty' => null,
        'office' => null,
        'notes' => [
            'The release must be in a form that can be recorded, and recording it discharges the lien (Tex. Prop. Code §§ 53.152, 53.157(1)).',
            'A release filed with the county clerk needs a notary, even though Texas lien waivers no longer do.',
        ],
    ],

    'CA' => [
        'name' => 'Release of mechanics lien',
        'cite' => null,
        'deadline' => null,
        'deadline_detail' => null,
        'penalty' => null,
        'office' => null,
        'notes' => [
            'If you have not sued within 90 days after recording, the owner can demand a release and, after at least 10 days\' notice, petition the court for a release order. The winner of that petition recovers reasonable attorney fees (Cal. Civ. Code §§ 8480, 8488).',
        ],
    ],

    'FL' => [
        'name' => 'Satisfaction or release of lien',
        'cite' => 'Fla. Stat. § 713.21(1)',
        'deadline' => null,
        'deadline_detail' => null,
        'penalty' => null,
        'office' => null,
        'notes' => [
            'Since October 1, 2023, it must carry your notarized signature and the official records reference number and recording date stamped on the lien being released (Fla. Stat. § 713.21(1)).',
            'Recording it in the clerk\'s office discharges the lien.',
        ],
    ],

    'NY' => [
        'name' => 'Certificate of discharge',
        'cite' => 'N.Y. Lien Law § 19(1)',
        'deadline' => null,
        'deadline_detail' => null,
        'penalty' => null,
        'office' => null,
        'notes' => [
            'It is a certificate of the lienor, acknowledged or proved, stating that the lien is satisfied or released in whole or in part (N.Y. Lien Law § 19(1)).',
            'File it in the county clerk\'s office where the notice of lien was filed. Most counties charge nothing, though some, such as Nassau, charge a fee.',
        ],
    ],

    'GA' => [
        'name' => 'Release and cancellation of claim of lien',
        'cite' => 'O.C.G.A. § 44-14-362',
        'deadline' => null,
        'deadline_detail' => null,
        'penalty' => null,
        'office' => null,
        'notes' => [
            'Every clerk of superior court charges $25 to file a cancellation of a lien (O.C.G.A. § 15-6-77).',
        ],
    ],

    'NC' => [
        'name' => 'Release of claim of lien',
        'cite' => 'N.C. Gen. Stat. § 44A-16(a)',
        'deadline' => null,
        'deadline_detail' => null,
        'penalty' => null,
        'office' => null,
        'notes' => [
            'You can acknowledge satisfaction before the clerk of superior court, or the owner can show the clerk a signed and acknowledged instrument of satisfaction (N.C. Gen. Stat. § 44A-16(a)).',
            'A lien is also discharged if it is not enforced in time, or if the owner deposits cash equal to the claim or a surety bond for 1.25 times the claim with the clerk.',
        ],
    ],

    'AZ' => [
        'name' => 'Release of lien',
        'cite' => 'A.R.S. § 33-1006',
        'deadline' => 'Within 20 days after the lien is satisfied',
        'deadline_detail' => 'On a protected owner-occupant\'s dwelling, within 20 days of the owner\'s written request.',
        'penalty' => '$1,000 plus actual damages. Refusing to release an invalid lien adds the greater of $1,000 or treble damages (A.R.S. §§ 33-1006, 33-420(C)).',
        'office' => null,
        'notes' => [
            'The release must meet the county recorder\'s format rules.',
        ],
    ],

    'WA' => [
        'name' => 'Release of claim of lien',
        'cite' => 'RCW 60.04.071',
        'deadline' => 'Immediately, once paid, when the owner or the person who paid asks',
        'deadline_detail' => null,
        'penalty' => 'If you delay without good reason, a court can order the release and award costs, reasonable attorney fees and damages (RCW 60.04.071).',
        'office' => null,
        'notes' => [
            'Page 1 of the recorded release must carry the recording number of the claim being released (RCW 65.04.045(1)(d)).',
        ],
    ],

    'PA' => [
        'name' => 'Satisfaction of the lien claim',
        'cite' => '49 P.S. § 1704',
        'deadline' => 'Within 30 days after a written request, once the claim is paid',
        'deadline_detail' => 'Satisfaction is entered on the record when the costs are paid.',
        'penalty' => 'The court may order the claim satisfied and impose a penalty of up to the amount of the claim (49 P.S. § 1704).',
        'office' => null,
        'notes' => [
            'Give the prothonotary the claim\'s docket number when you file it.',
        ],
    ],

    'IL' => [
        'name' => 'Release and satisfaction',
        'cite' => '770 ILCS 60/35',
        'deadline' => 'Within 10 days after a written demand, once the claim and the recording costs are paid',
        'deadline_detail' => 'The owner, a lienor or anyone with an interest in the property can make the demand.',
        'penalty' => '$2,500 to the owner, plus the costs and reasonable attorney fees of collecting it (770 ILCS 60/35(a)).',
        'office' => null,
        'notes' => [
            'The release must say, in bold letters at least 1/4 inch high, that it should be filed with the recorder where the claim was filed (770 ILCS 60/35(c)).',
            'It must show the recorder\'s document number of the claim (55 ILCS 5/3-5020.5(2)).',
        ],
    ],
];
