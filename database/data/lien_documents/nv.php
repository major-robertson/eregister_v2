<?php

/*
 * Nevada: NRS 108.221 to 108.246 (mechanics' and materialmen's liens).
 *
 * Notice of lien: NRS 108.226 (record with the county recorder of the county
 * where the property is located within 90 days after the latest of the
 * completion of the work of improvement, the claimant's last delivery of
 * material or equipment and its last performance of work, or within 40 days
 * after a notice of completion is recorded and served under NRS 108.228,
 * (1); contents, (2); "must be verified by the oath of the lien claimant or
 * some other person" and "need not be acknowledged to be recorded", (3); a
 * knowingly false statement is a gross misdemeanor with a $5,000 to $10,000
 * fine, (4); "must be substantially in the following form", (5), rendered
 * verbatim, verification included, by bodies/nv-notice-of-lien; on
 * single-family or multifamily residential work every claimant except
 * laborers first serves a 15-day notice of intent to lien on the owner and
 * the reputed prime contractor, which extends the recording time by 15 days,
 * (6), never on nonresidential work, (7)). NRS 108.227 is service only: a
 * copy to the owner within 30 days after recording, in person or by
 * certified mail, return receipt requested, (1), and a subcontractor's copy
 * to the prime contractor, (3); it prescribes no verification. Frivolous or
 * excessive notice of lien: NRS 108.2275. Duration: 6 months unless suit is
 * filed (NRS 108.233). Discharge: NRS 108.2437 (record within 10 days after
 * satisfaction, "in substantially the following form", rendered verbatim by
 * bodies/nv-discharge-of-notice-of-lien; $100 or actual damages plus fees),
 * acknowledged where the lien was recorded by a photographic process (NRS
 * 108.2433(2)). Notice of right to lien: NRS 108.245 (the form in (1),
 * rendered verbatim by letters/bodies/nv-notice-of-right-to-lien; a copy to
 * the prime contractor for information only; no lien except for labor
 * without it, (3); need not be verified, (4); not required of a prime
 * contractor or anyone who contracts directly with, or sells materials
 * directly to, the owner, (5); it reaches back 31 days, (6)).
 *
 * Recording: NRS 247.110(3) (white letter-size paper, 1-inch side and bottom
 * margins, a 3-by-3-inch space at the upper right of page 1, a 1-inch top
 * margin after page 1, 10-point text, black ink, at most nine lines an
 * inch: the default 3-inch first-page space and 1-inch margins meet it, and
 * page numbers are off so nothing prints in the bottom margin); NRS
 * 111.312(1) (no notice of lien is recorded without a mailing address and
 * the assessor's parcel number at the top left corner of page 1); NRS
 * 247.190(2) (names printed under signatures); NRS 247.305 (a flat fee per
 * document). NRS 239B.030(6) only lets a recorder require an affirmation
 * that a document holds no personal information; Clark and Washoe list
 * none, so no affirmation prints. Notarial certificates: NRS 240.1655,
 * 240.166 to 240.169; another state's notarial act has the same effect (NRS
 * 240.164).
 *
 * Verified against leg.state.nv.us (NRS chapters 108, 111, 239B and 240
 * revised 2025, chapter 247 revised 2026) and the Clark and Washoe County
 * Recorder requirement pages on 2026-09-30.
 */

return [
    'state' => 'NV',
    'state_name' => 'Nevada',
    'recording' => [
        'filing_office' => ['label' => 'County Recorder', 'method' => 'erecord'],
        // NRS 247.110(3)(b) wants a 1-inch margin at the bottom of each page, and
        // Clark County's sample says "blank": the shell's page number would print
        // inside it.
        'page_numbers' => false,
        'parcel_label' => 'Assessor\'s Parcel Number',
        'fee_note' => '$32 to $43 per document, whatever its length: $25, a $7 add-on and up to $11 in local add-ons (NRS 247.305).',
        'notes' => [
            'Record with the County Recorder of the county where the property is located (NRS 108.226(1), NRS 247.200).',
            'Recording format (NRS 247.110(3)): white letter-size paper printed on one side, 1-inch margins at the sides and bottom, a blank 3-by-3-inch space at the upper right of page 1, and 10-point black type or larger. The generated pages meet it; page numbers are off because they would print in the bottom margin.',
            'NRS 111.312(1): the recorder will not record a notice of lien without a mailing address and the assessor\'s parcel number at the top left corner of page 1. On the notice of lien and the discharge the parcel number prints as the first line of text under the recorder\'s space, above the title, and again in the page-1 index line.',
            'Undecided: whether that first line under the 3-inch space meets "the top left corner of the first page" (NRS 111.312(1)(b)); until counsel says otherwise, record as generated, and if a recorder rejects it for placement, add the county\'s own cover page (Clark County\'s carries the APN).',
            'No personal-information affirmation prints: NRS 239B.030(6) lets a recorder require one, and neither Clark nor Washoe lists one in its recording requirements (checked 2026-09-30).',
        ],
    ],
    'execution' => [
        'verification' => 'sworn',
        'notary' => true,
        'notary_form' => 'jurat',
    ],
    'service' => [
        'recipients' => ['owner'],
        'days_after' => 30,
        'method' => 'certified_mail',
        'proof' => 'declaration',
    ],
    'kinds' => [
        'mechanics_lien' => [
            'title' => 'Notice of Lien',
            'statute' => 'NRS 108.226',
            'body' => 'documents.lien.instruments.bodies.nv-notice-of-lien',
            'template_version' => 1,
            'sections' => [
                // The body prints the form's own items 1 to 4, never the shared
                // amounts table; 'breakdown' only turns on the package's check
                // that the lien amount reconciles with the project's figures.
                'amount' => 'breakdown',
                'gc' => true,
                // The NRS 108.226(5) form does not recite the notice of right to lien.
                'prior_notice' => false,
            ],
            // The body ends with the form's own verification ("being first duly
            // sworn on oath according to law, deposes and says"), so the
            // execution block prints only the signature and the jurat.
            'execution' => [
                'verification' => 'sworn',
                'notary' => true,
                'notary_form' => 'jurat',
                'statement' => false,
            ],
            'service' => [
                'recipients' => ['owner'],
                'days_after' => 30,
                'method' => 'certified_mail',
                'proof' => 'declaration',
            ],
            'clauses' => [
                // The form's first line, "Assessor's Parcel Numbers"; notice_box is
                // the only slot above the title (NRS 108.226(5), NRS 111.312(1)(b)).
                'notice_box' => 'documents.lien.instruments.clauses.nv-assessors-parcel-numbers',
            ],
            'notes' => [
                'Record within 90 days after the latest of the completion of the work of improvement, the claimant\'s last delivery of material or equipment, or its last performance of work; or within 40 days after the owner records a notice of completion and serves it as NRS 108.228 requires (NRS 108.226(1)).',
                'Residential work (single-family or multifamily residences): serve the 15-day notice of intent to lien on the owner and the reputed prime contractor before recording. It extends the recording time by 15 days, and without it there is no lien except for labor (NRS 108.226(6)).',
                'A claimant who had to give the notice of right to lien and did not has no lien except for labor, and a late notice reaches back only 31 days (NRS 108.245(3), (6)).',
                'Only work, materials or equipment worth $500 or more gives a lien (NRS 108.2214), and a contractor who needed a Nevada license and had none has no lien (NRS 108.222(2)).',
                'Against two or more separate buildings on separate parcels of the same owner, the notice must state the amount due on each building (NRS 108.231); the generated notice states one amount, so record one notice per parcel.',
                'Serve a copy of the recorded notice of lien on the owner within 30 days after recording, in person or by certified mail, return receipt requested (NRS 108.227(1)). A subcontractor also delivers a copy to the prime contractor (NRS 108.227(3)).',
                'State the lien amount exactly: a court can release a frivolous notice of lien or reduce an excessive one and award attorney\'s fees against the claimant (NRS 108.2275), and a knowingly false statement is a gross misdemeanor with a $5,000 to $10,000 fine (NRS 108.226(4)).',
                'The lien expires 6 months after recording unless a foreclosure action is started or a written extension is recorded (NRS 108.233).',
                'Item 7 (terms of payment) prints a ruled blank because there is no payment-terms field yet; the claimant writes the terms in before signing, for example "Net 30 days from invoice".',
                'The claimant signs once, under the verification, and swears to it before a notary (NRS 108.226(3)); the notary fills in the state and county where the oath is taken.',
            ],
        ],
        'lien_release' => [
            'title' => 'Discharge or Release of Notice of Lien',
            'statute' => 'NRS 108.2437',
            'body' => 'documents.lien.instruments.bodies.nv-discharge-of-notice-of-lien',
            'template_version' => 1,
            'execution' => [
                'verification' => 'acknowledged',
                'notary' => true,
                'notary_form' => 'acknowledgment',
            ],
            'service' => [
                'recipients' => ['owner'],
                'days_after' => null,
                'method' => 'certified_mail',
                'proof' => 'declaration',
            ],
            'clauses' => [
                // The NRS 108.2437(1) form also opens with "Assessor's Parcel Numbers".
                'notice_box' => 'documents.lien.instruments.clauses.nv-assessors-parcel-numbers',
            ],
            'notes' => [
                'Record the discharge as soon as practicable and no later than 10 days after the lien is fully satisfied or discharged; otherwise the claimant owes the owner actual damages or $100, whichever is greater, plus attorney\'s fees and costs (NRS 108.2437).',
                'Acknowledged before a notary: NRS 108.2433(2) requires an acknowledged discharge when the notice of lien was recorded by microfilm or another photographic process.',
                'Set the recorded notice of lien\'s date, book and document number in Document details; "in Book" drops out when only a document number is on file.',
                'No service is required; mailing the owner a copy of the recorded discharge is a courtesy.',
            ],
        ],
        'prelim_notice' => [
            'title' => 'Notice of Right to Lien',
            'statute' => 'NRS 108.245',
            'body' => 'documents.lien.letters.bodies.nv-notice-of-right-to-lien',
            'template_version' => 1,
            'sections' => [
                'amount' => 'none',
                'gc' => true,
            ],
            'service' => [
                'recipients' => ['owner', 'gc'],
                'days_after' => null,
                'method' => 'certified_mail',
                'proof' => 'declaration',
            ],
            'notes' => [
                'Deliver it to the owner in person or by certified mail any time after first furnishing. It covers work and materials in the 31 days before it is given and everything after, so send it within 31 days of first furnishing to cover the whole job (NRS 108.245(1), (6)).',
                'Send the prime contractor a copy for information only, in person or by certified mail. Skipping it is a ground for discipline but does not invalidate the notice to the owner (NRS 108.245(1)).',
                'Not required of a laborer, a prime contractor, or anyone who contracted directly with the owner or sold materials directly to the owner (NRS 108.245(1), (5)). Anyone else has no lien except for labor without it (NRS 108.245(3)).',
            ],
        ],
        'noi' => [
            'title' => 'Notice of Intent to Lien',
            'statute' => 'NRS 108.226(6)',
            'sections' => ['demand_days' => 15],
            'clauses' => [
                // NRS 108.226(6): the notice incorporates "substantially the same
                // information required in a notice of lien"; the generic body lacks
                // the owner and the terms of payment (NRS 108.226(2)(b), (d)).
                'after_property' => ['documents.lien.letters.clauses.nv-intent-to-lien-owner-and-terms'],
                'demand' => 'This is a 15-day notice of intent to lien under NRS 108.226(6). Unless the amount stated above is paid in full, Claimant intends to record a notice of lien against the property described above with the county recorder of the county where the property is located no sooner than 15 days after this notice is served.',
            ],
            'service' => [
                'recipients' => ['owner', 'gc'],
                'days_after' => null,
                'method' => 'certified_mail',
                'proof' => 'declaration',
            ],
            'notes' => [
                'Required on work of improvement for single-family or multifamily residences: every lien claimant except laborers serves it on the owner and the reputed prime contractor, in person or by certified mail, before recording the notice of lien (NRS 108.226(6)).',
                'It extends the time to record the notice of lien by 15 days; record no sooner than 15 days after it is served.',
                'Not required on nonresidential construction (NRS 108.226(7)); there it is a courtesy.',
                'It must carry substantially the same information as the notice of lien, so the claimant fills in the terms of payment before it goes out.',
            ],
        ],
    ],
];
