<?php

/*
 * Arizona: A.R.S. Title 33, ch. 7, art. 6 (Mechanics' and Materialmen's Liens).
 *
 * Notice and claim of lien: § 33-993(A) (record within 120 days after
 * completion, or 60 days after a recorded notice of completion; made under
 * oath; contents (1)-(6): legal description, owner and the person who employed
 * the claimant, the oral contract's terms or a copy of the written contract,
 * the demand after just credits and offsets, the completion date, and the
 * date the preliminary twenty day notice was given "A copy of such preliminary
 * twenty day notice and the proof of mailing required by section 33-992.02
 * shall be attached."). Preliminary twenty day notice: § 33-992.01 (serve the
 * owner, the original contractor, the construction lender and the person with
 * whom the claimant contracted within 20 days of first furnishing; contents in
 * (C); the form in (D) with the "Notice to Property Owner" in bold and the two
 * ten-day paragraphs "in type at least as large as the largest type otherwise
 * on the document"; a late notice reaches back only 20 days, (E)). Proof of
 * mailing / acknowledgment of receipt: § 33-992.02. Statutory text verified
 * against azleg.gov on 2026-09-29.
 */

return [
    'state' => 'AZ',
    'state_name' => 'Arizona',
    'recording' => [
        'filing_office' => ['label' => 'County Recorder', 'method' => 'erecord'],
        'parcel_label' => 'APN',
        'fee_note' => '$30 per instrument (La Paz County, 2026).',
        'notes' => [
            'Record within 120 days after completion, or within 60 days after a recorded notice of completion, and serve the remaining copy on the owner within a reasonable time (A.R.S. § 33-993(A)).',
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
            'title' => 'Notice and Claim of Mechanic\'s and Materialman\'s Lien',
            'statute' => 'A.R.S. § 33-993',
            'sections' => [
                'amount' => 'breakdown',
                'license' => true,
                'contract_type' => true,
                'contract_date' => true,
                'completion_date' => true,
                'prior_notice' => true,
            ],
            'clauses' => [
                'affirmations' => [
                    'The labor, professional services, materials, machinery, fixtures or tools were furnished at the request of the owner or reputed owner, or at the request of a person the claimant reasonably believed to be the lawful agent of the owner or reputed owner.',
                    'If this lien is claimed against the dwelling of a person who became an owner-occupant before the construction, alteration, repair or improvement, the claimant executed a written contract directly with the owner-occupant (A.R.S. § 33-1002).',
                ],
            ],
            'attachments' => [
                'A copy of the written contract, or the statement of the oral contract\'s terms, time given and conditions printed on the claim (A.R.S. § 33-993(A)(3)).',
                'A copy of the preliminary twenty day notice and its proof of mailing (A.R.S. § 33-993(A)(6), § 33-992.02).',
            ],
            'notes' => [
                'The claim must state the completion date and the date the preliminary twenty day notice was given; both come from Document details.',
            ],
        ],
        'lien_release' => [
            'title' => 'Release of Lien',
            'statute' => 'A.R.S. § 33-1006',
            'execution' => [
                'verification' => 'acknowledged',
                'notary' => true,
                'notary_form' => 'acknowledgment',
            ],
        ],
        'prelim_notice' => [
            'title' => 'Arizona Preliminary Twenty Day Lien Notice',
            'statute' => 'A.R.S. § 33-992.01',
            'body' => 'documents.lien.letters.bodies.az-preliminary-20-day-notice',
            'template_version' => 1,
            'sections' => [
                'amount' => 'estimate',
                'lender' => true,
                'gc' => true,
                'first_furnish' => true,
            ],
            'service' => [
                'recipients' => ['owner', 'gc', 'lender', 'customer'],
                'days_after' => 20,
                'method' => 'certified_mail',
                'proof' => 'declaration',
            ],
            'clauses' => [
                // § 33-992.01(D), verbatim.
                'notice_box' => 'documents.lien.letters.clauses.az-notice-to-property-owner',
            ],
            'notes' => [
                'Serve within 20 days after first furnishing on the owner, the original contractor, the construction lender (if any) and the person with whom the claimant contracted (A.R.S. § 33-992.01(B)-(C)); a later notice covers only the 20 days before service (§ 33-992.01(E)).',
                'Keep the proof of mailing; the claim of lien must attach it (A.R.S. § 33-992.02, § 33-993(A)(6)).',
            ],
        ],
        'noi' => [
            'title' => 'Notice of Intent to Record a Lien',
            'statute' => 'A.R.S. § 33-981 et seq.',
            'sections' => ['demand_days' => 10],
            'notes' => [
                'Arizona has no notice-of-intent step; the preliminary twenty day notice and the claim of lien are the statutory steps. This is a demand courtesy.',
            ],
        ],
    ],
];
