<?php

/*
 * Florida: Construction Lien Law, Fla. Stat. ch. 713, part I.
 *
 * Claim of lien: § 713.08 (contents in (1), sworn in (2), the "substantially
 * the following form" claim with its WARNING in (3), service on the owner
 * before recording or within 15 days after in (4)(c), record within 90 days
 * after final furnishing in (5)). Notice to owner: § 713.06(2) (serve before
 * commencing or within 45 days after first furnishing; the form in (2)(c)
 * "must include the information and the warning contained in the following
 * form"). Release: § 713.21. Service methods: § 713.18. Notary certificates
 * say "by means of physical presence or online notarization" (§ 117.05(13)).
 * Statutory text verified against leg.state.fl.us (2026 Florida Statutes)
 * on 2026-09-29. Florida clerks reserve a 3" x 3" space at the top right of
 * page 1 (§ 695.26(1)(e)).
 */

return [
    'state' => 'FL',
    'state_name' => 'Florida',
    'recording' => [
        'filing_office' => ['label' => 'Clerk of the Circuit Court, Official Records', 'method' => 'either'],
        'parcel_label' => 'Parcel ID',
        'fee_note' => '$10 for the first page and $8.50 per additional page (2026).',
        'notes' => [
            'Record within 90 days after the lienor\'s final furnishing (Fla. Stat. § 713.08(5)).',
            'Serve the claim of lien on the owner before recording or within 15 days after recording (Fla. Stat. § 713.08(4)(c), § 713.18).',
        ],
    ],
    'execution' => [
        'verification' => 'sworn',
        'notary' => true,
        'notary_form' => 'jurat',
        'notary_variant' => 'fl',
    ],
    'service' => [
        'recipients' => ['owner'],
        'days_after' => 15,
        'method' => 'certified_mail',
    ],
    'kinds' => [
        'mechanics_lien' => [
            'title' => 'Claim of Lien',
            'statute' => 'Fla. Stat. § 713.08',
            'body' => 'documents.lien.instruments.bodies.fl-claim-of-lien',
            'template_version' => 1,
            'sections' => [
                'amount' => 'breakdown',
                'amount_in_words' => true,
                'prior_notice' => true,
                'gc' => false,
            ],
            // The statutory form opens "Before me, the undersigned notary public,
            // personally appeared …, who was duly sworn and says", so the body is
            // the sworn statement and the execution block prints only the jurat.
            'execution' => ['statement' => false],
            'clauses' => [
                // § 713.08(3): the claim "includes the following warning".
                'notice_box' => 'WARNING! THIS LEGAL DOCUMENT REFLECTS THAT A CONSTRUCTION LIEN HAS BEEN PLACED ON THE REAL PROPERTY LISTED HEREIN. UNLESS THE OWNER OF SUCH PROPERTY TAKES ACTION TO SHORTEN THE TIME PERIOD, THIS LIEN MAY REMAIN VALID FOR ONE YEAR FROM THE DATE OF RECORDING, AND SHALL EXPIRE AND BECOME NULL AND VOID THEREAFTER UNLESS LEGAL PROCEEDINGS HAVE BEEN COMMENCED TO FORECLOSE OR TO DISCHARGE THIS LIEN.',
            ],
            'notes' => [
                'The privity sentence ("the lienor served her or his notice to owner on …, by …") prints only when the claimant did not contract with the owner (Fla. Stat. § 713.08(1)(h)); fill the notice date and method in Document details.',
            ],
        ],
        'lien_release' => [
            'title' => 'Release of Lien',
            'statute' => 'Fla. Stat. § 713.21',
            'execution' => [
                'verification' => 'acknowledged',
                'notary' => true,
                'notary_form' => 'acknowledgment',
                'notary_variant' => 'fl',
            ],
            'clauses' => [
                'affirmations' => [
                    'The undersigned lienor hereby releases its claim of lien and directs the Clerk of the Circuit Court to cancel and discharge the Claim of Lien of record in accordance with § 713.21, Florida Statutes.',
                ],
            ],
        ],
        'prelim_notice' => [
            'title' => 'Notice to Owner',
            'statute' => 'Fla. Stat. § 713.06',
            'body' => 'documents.lien.letters.bodies.fl-notice-to-owner',
            'template_version' => 1,
            'sections' => [
                'amount' => 'estimate',
                'lender' => true,
                'first_furnish' => true,
            ],
            'service' => [
                'recipients' => ['owner', 'gc', 'lender'],
                'days_after' => 45,
                'method' => 'certified_mail',
            ],
            'clauses' => [
                // § 713.06(2)(c): "must include the information and the warning contained in the following form".
                'notice_box' => 'WARNING! FLORIDA\'S CONSTRUCTION LIEN LAW ALLOWS SOME UNPAID CONTRACTORS, SUBCONTRACTORS, AND MATERIAL SUPPLIERS TO FILE LIENS AGAINST YOUR PROPERTY EVEN IF YOU HAVE MADE PAYMENT IN FULL. UNDER FLORIDA LAW, YOUR FAILURE TO MAKE SURE THAT WE ARE PAID MAY RESULT IN A LIEN AGAINST YOUR PROPERTY AND YOUR PAYING TWICE. TO AVOID A LIEN AND PAYING TWICE, YOU MUST OBTAIN A WRITTEN RELEASE FROM US EVERY TIME YOU PAY YOUR CONTRACTOR.',
                'after_property' => [
                    'Florida law prescribes the serving of this notice and restricts your right to make payments under your contract in accordance with Section 713.06, Florida Statutes.',
                ],
                'before_signature' => [
                    'IMPORTANT INFORMATION FOR YOUR PROTECTION',
                    'Under Florida\'s laws, those who work on your property or provide materials and are not paid have a right to enforce their claim for payment against your property. This claim is known as a construction lien.',
                    'If your contractor fails to pay subcontractors or material suppliers or neglects to make other legally required payments, the people who are owed money may look to your property for payment, EVEN IF YOU HAVE PAID YOUR CONTRACTOR IN FULL.',
                    'PROTECT YOURSELF:',
                    '—RECOGNIZE that this Notice to Owner may result in a lien against your property unless all those supplying a Notice to Owner have been paid.',
                    '—LEARN more about the Construction Lien Law, Chapter 713, Part I, Florida Statutes, and the meaning of this notice by contacting an attorney or the Florida Department of Business and Professional Regulation.',
                ],
            ],
            'notes' => [
                'Serve before commencing or not later than 45 days after commencing to furnish (Fla. Stat. § 713.06(2)(a)). A sub-subcontractor or a materialman to a subcontractor must also serve a copy on the contractor; a materialman to a sub-subcontractor serves the subcontractor too.',
                'Not needed when the claimant contracted directly with the owner (privity).',
            ],
        ],
        'noi' => [
            'title' => 'Notice of Intent to Record a Claim of Lien',
            'statute' => 'Fla. Stat. ch. 713',
            'sections' => ['demand_days' => 10],
            'notes' => [
                'Florida does not require a notice of intent; this is a demand courtesy sent before the claim of lien is recorded.',
            ],
        ],
    ],
];
