<?php

/*
 * Utah: Utah Code Title 38, ch. 1a (Preconstruction and Construction Liens)
 * and ch. 11 (Residence Lien Restriction and Lien Recovery Fund Act); Utah
 * Admin. Code R156-38a.
 *
 * Notice of construction lien: § 38-1a-502 ((1)(a) submitted for recording
 * with each applicable county recorder no later than 180 days after final
 * completion of the original contract when no notice of completion is filed,
 * or 90 days after a notice of completion is filed under § 38-1a-507 but not
 * later than 180 days after final completion; (1)(b) a subcontractor whose
 * substantial work follows the certificate of occupancy or final inspection
 * has 180 days after finishing that work; (2) contents (a)-(i): the reputed
 * owner (or the record owner), the person who employed the claimant or to
 * whom it provided construction work, when it first and last provided
 * construction work, a description of the project property sufficient for
 * identification, the claimant's name, current address and current phone
 * number, the amount claimed, the signature of the claimant or its authorized
 * agent, "an acknowledgment or certificate as required under Title 57,
 * Chapter 3, Recording of Documents", and, on an owner-occupied residence, "a
 * statement describing what steps an owner ... may take to require a lien
 * claimant to remove the lien in accordance with Section 38-11-107"; (4)(a)
 * within 30 days after filing, deliver or mail by certified mail a copy to
 * the reputed or record owner; (4)(c) without that, no costs or attorney fees
 * against the owner; (5) the division makes rules on the form of the (2)(i)
 * statement). Signed and acknowledged, not sworn: § 57-3-101 (an
 * acknowledgment, jurat or other notarial certificate entitles a document to
 * be recorded); § 57-2a-7 and § 46-1-6.5(3) give acknowledgment forms that
 * are sufficient, not required (§ 57-2a-7 "does not preclude the use of other
 * forms"), so the generic certificate (blank venue, signer and capacity named)
 * serves a signer who notarizes outside Utah.
 * The (2)(i) statement is the notice Utah Admin. Code R156-38a-108 prescribes
 * "in substantially the following form" for "every notice of intent to hold
 * and claim lien filed under Section 38-1a-502 against a homeowner or against
 * an owner-occupied residence" (§ 38-11-108 itself sets out no wording); it
 * prints from instruments/clauses/ut-owner-occupied-residence-statement.
 * Recording: § 57-3-105(2), (4) (a legal description by metes and bounds,
 * government survey or recorded plat), § 17-71-402(4) (a recorder may require
 * a 2-1/2" by 4-1/2" space in the upper right corner of page 1; Utah County
 * does), § 17-71-407 ($40 per instrument; formerly § 17-21-18.5).
 * Preliminary notice: § 38-1a-501 (filed with the State Construction Registry
 * no later than 20 days after first providing construction work; contents in
 * (1)(h); no preliminary notice, no lien, (1)(e)). Enforcement: § 38-1a-701
 * (action and lis pendens within 180 days after filing the notice).
 * Cancellation once paid: § 38-1a-803 (within 10 days after a request).
 * Excessive lien notice: § 38-1a-308. Verified against le.utah.gov (Utah Code)
 * and adminrules.utah.gov (R156-38a as amended effective 2026-06-25) on
 * 2026-09-30.
 */

return [
    'state' => 'UT',
    'state_name' => 'Utah',
    'recording' => [
        'filing_office' => ['label' => 'County Recorder', 'method' => 'erecord'],
        'parcel_label' => 'Parcel Number',
        // § 17-71-402(4)(a)(iii): recorders may require the upper right 4-1/2" of the
        // top 2-1/2" of page 1 to stay clear. The in-space preparer block is 3.4"
        // wide from the left margin and would reach into it, so it prints below the rule.
        'preparer_in_space' => false,
        'fee_note' => '$40 per instrument, whatever the page count, plus $2 for each legal description over ten; a county of the second to sixth class may add $5 (Utah Code § 17-71-407).',
        'notes' => [
            'Record with the county recorder of each county where the property is located within 180 days after final completion of the original contract, or within 90 days after a notice of completion is filed in the State Construction Registry but never later than 180 days after final completion; final completion is usually the certificate of occupancy or final inspection date (Utah Code § 38-1a-502(1), § 38-1a-102(17)).',
            'Deliver or mail a copy of the recorded notice by certified mail to the reputed or record owner within 30 days after recording; without it the claimant cannot recover costs and attorney fees from the owner (Utah Code § 38-1a-502(4)).',
            'There is no lien without a preliminary notice filed in the State Construction Registry (Utah Code § 38-1a-501(1)(e)); keep the SCR entry number with the filing. An owner can petition to nullify a lien filed without one (Utah Code § 38-1a-805).',
            'File the action to enforce the lien and record a notice of pendency of the action within 180 days after recording the notice, or the lien is void (Utah Code § 38-1a-701(2)-(4)).',
            'Utah recorders need a legal description by metes and bounds, government survey, or lot, block or parcel in a recorded plat (Utah Code § 57-3-105(4)); the parcel number and street address do not count as one (Utah Code § 17-71-402(5)).',
        ],
    ],
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
    'kinds' => [
        'mechanics_lien' => [
            'title' => 'Notice of Construction Lien',
            'statute' => 'Utah Code § 38-1a-502',
            'template_version' => 1,
            'sections' => [
                'amount' => 'breakdown',
                'amount_in_words' => true,
                'gc' => true,
                'hiring_party' => true,
                // Utah's preliminary notice is a registry filing, not a served notice.
                'prior_notice' => false,
            ],
            'clauses' => [
                'affirmations' => [
                    'Claimant\'s current address and current telephone number are stated above (Utah Code § 38-1a-502(2)(e)).',
                    'This notice is submitted for recording within the time required by Utah Code § 38-1a-502(1).',
                ],
                // § 38-1a-502(2)(i) with the R156-38a-108 notice; printed on every notice
                // because staff rarely know whether the owner occupies the property.
                'before_signature' => ['documents.lien.instruments.clauses.ut-owner-occupied-residence-statement'],
            ],
            // § 38-1a-502(4)(a): "Within 30 days after filing ... by certified mail".
            'service' => ['days_after' => 30],
            'notes' => [
                'The claimant\'s current phone number must print with its name and address (Utah Code § 38-1a-502(2)(e)); add it to the claimant party or the business if it is missing.',
                'The notice is signed and acknowledged before a notary, not sworn (Utah Code § 38-1a-502(2)(h), § 57-3-101); the certificate must name the person who signs, and one recorded Utah acknowledgment named the notary instead.',
                'State only the amount due, without "plus interest, attorney fees, and costs" or "could change" language: a notice that intentionally claims more than is due is a class B misdemeanor and costs twice the excess or actual damages (Utah Code § 38-1a-308).',
                'On a residence, the complaint to enforce the lien must be served with the division\'s owner instructions and form, or the lien cannot be enforced (Utah Code § 38-1a-701(6)).',
                'Undecided: whether the owner-occupied residence notice should keep R156-38a-108\'s paragraph (3) (apply for a Certificate of Compliance), which follows the rule\'s closing quotation mark and names the division by its pre-2022 name; until counsel says otherwise, it prints as the rule publishes it.',
                'Undecided: whether the notice should also say that the owner mails the certificate of compliance to the claimant by certified mail and the claimant then has 15 days to remove the lien (Utah Code § 38-11-107(3)); until counsel says otherwise, it prints the rule\'s text only.',
            ],
        ],
        'lien_release' => [
            'title' => 'Release of Construction Lien',
            'statute' => 'Utah Code § 38-1a-803',
            'execution' => [
                'verification' => 'acknowledged',
                'notary' => true,
                'notary_form' => 'acknowledgment',
            ],
            'clauses' => [
                'affirmations' => [
                    'Claimant submits this release for recording as the cancellation of the construction lien described above (Utah Code § 38-1a-803).',
                ],
            ],
            'notes' => [
                'Once the full amount owing under the lien, including costs and cancellation fees, is paid, a person interested in the property may ask for a cancellation; the claimant must submit it to each applicable county recorder within 10 days after the request or owe $100 a day or actual damages, whichever is greater (Utah Code § 38-1a-803).',
                'Identify the lien being released by its recording entry number in Document details.',
            ],
        ],
        'prelim_notice' => [
            'title' => 'Preliminary Notice (State Construction Registry)',
            'statute' => 'Utah Code § 38-1a-501',
            'template_version' => 1,
            'sections' => [
                'amount' => 'estimate',
                'gc' => true,
                'first_furnish' => true,
                'last_furnish' => false,
            ],
            'service' => [
                'recipients' => ['owner'],
                'days_after' => null,
                'method' => 'certified_mail',
            ],
            'clauses' => [
                'before_signature' => [
                    'Utah law requires this preliminary notice to be filed with the State Construction Registry (Utah Code § 38-1a-501). This copy is sent to the owner for information.',
                ],
            ],
            'notes' => [
                'The legal step is filing the preliminary notice with the State Construction Registry no later than 20 days after first providing construction work (Utah Code § 38-1a-501(1)(a)); mailing this letter does not replace it.',
                'A late registry filing covers only work provided from five days after it is filed, and has no effect if filed more than 10 days after a notice of completion (Utah Code § 38-1a-501(1)(c)-(d)).',
                'This letter is the record of what was filed and a courtesy copy for the owner; put the SCR entry number in the filing notes.',
                'The registry filing needs the claimant\'s name, address, phone and email; the hiring party\'s name and address; the owner; the original contractor; the project address and county; and each tax parcel number, or the entry number of a construction loan notice, an earlier preliminary notice or the building permit (Utah Code § 38-1a-501(1)(h)).',
            ],
        ],
        'noi' => [
            'title' => 'Notice of Intent to Record a Construction Lien',
            'statute' => 'Utah Code § 38-1a-101 et seq.',
            'sections' => ['demand_days' => 10],
            'service' => [
                'recipients' => ['owner'],
                'days_after' => null,
                'method' => 'certified_mail',
            ],
            'notes' => [
                'Utah has no notice-of-intent step; the registry preliminary notice and the notice of construction lien are the statutory steps. This is a demand courtesy.',
            ],
        ],
    ],
];
