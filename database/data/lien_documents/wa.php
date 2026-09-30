<?php

/*
 * Washington: RCW chapter 60.04 (Mechanics' and Materialmen's Liens).
 *
 * Claim of lien: RCW 60.04.091. Record it in the county where the property is
 * located not later than 90 days after the claimant ceased to furnish labor,
 * professional services, materials or equipment. It states in substance the
 * claimant's name, phone number and address, the first and last dates of
 * furnishing, the person indebted to the claimant, the property, the owner or
 * reputed owner ("if not known, that fact shall be stated") and the principal
 * amount ((1)(a)-(f)). It is signed by the claimant or an authorized person who
 * states under penalty of perjury that they have read it and believe it true
 * and correct, "and shall be acknowledged pursuant to chapter 64.08 RCW" (2).
 * "A claim of lien substantially in the following form shall be sufficient":
 * rendered in instruments/bodies/wa-claim-of-lien, whose sworn statement ends
 * with a jurat. A copy goes to the owner or reputed owner by certified or
 * registered mail or personal service within 14 days of filing for recording;
 * otherwise the claimant forfeits attorneys' fees and costs against the owner
 * under RCW 60.04.181. Who has a lien: RCW 60.04.021 (definitions in
 * RCW 60.04.011). Frivolous or clearly excessive claims: RCW 60.04.081.
 * Separate residential units: RCW 60.04.101. Foreclosure: RCW 60.04.171, within
 * eight calendar months of recording (RCW 60.04.141). Rank and attorneys' fees:
 * RCW 60.04.181. Release on payment and demand: RCW 60.04.071.
 *
 * Notice to owner: RCW 60.04.031. Given in writing by those furnishing
 * professional services, materials or equipment (exceptions in (2) and (3));
 * it protects only what was supplied after the date 60 days (new single-family
 * residence: 10 days) before it was mailed or delivered, (1); the form in (4)
 * "shall substantially be in the following form", rendered in
 * letters/bodies/wa-notice-to-owner. Notice to real property lender:
 * RCW 60.04.221 (not generated).
 *
 * Recording: RCW 65.04.045 (page 1: 3-inch top margin and 1-inch bottom and
 * sides, return address top left, the title right below the margin, grantor
 * and grantee names, an abbreviated legal description, the assessor's tax
 * parcel number set apart; other pages 1-inch margins; 8-point type or larger;
 * no larger than 8.5 x 14 inches) and RCW 65.04.047 (a cover sheet when page 1
 * lacks that information). Notarial certificates: RCW 42.45.130; the short
 * forms of RCW 42.45.140 are "sufficient", not mandatory, so the shared
 * certificates stay.
 *
 * Verified against app.leg.wa.gov (Revised Code of Washington) on 2026-09-30.
 */

return [
    'state' => 'WA',
    'state_name' => 'Washington',
    'recording' => [
        'filing_office' => ['label' => 'County Auditor', 'method' => 'erecord'],
        'parcel_label' => 'Parcel Number',
        'notes' => [
            'File with the county auditor, or in a charter county the official in charge of recording, of the county where the property is located (RCW 60.04.091, RCW 65.04.015(1)).',
            'The default layout meets RCW 65.04.045: a 3-inch top margin and 1-inch margins elsewhere on page 1, 1-inch margins on the other pages, 10-point type (the minimum is 8), letter-size paper (the maximum is 8.5 x 14 inches).',
            'Page 1 must carry the return address at the top left, the title right below the 3-inch margin, the grantor and grantee names, an abbreviated legal description (lot, block, plat, or section, township, range) and the assessor\'s parcel number set apart (RCW 65.04.045(1)). The preparer block, the title, the index line (claimant, owner, parcel number) and the legal description carry them; the claim of lien repeats the legal description above the form because its item 4 starts on page 2. A recorder can require a cover sheet when page 1 lacks any of them (RCW 65.04.047).',
        ],
    ],
    'execution' => [
        'verification' => 'sworn',
        'notary' => true,
        'notary_form' => 'jurat',
    ],
    'service' => [
        'recipients' => ['owner'],
        'days_after' => 14,
        'method' => 'certified_mail',
    ],
    'kinds' => [
        'mechanics_lien' => [
            'title' => 'Claim of Lien',
            'statute' => 'RCW 60.04.091',
            'body' => 'documents.lien.instruments.bodies.wa-claim-of-lien',
            'template_version' => 1,
            'sections' => [
                // The form states the principal amount itself; no amounts table.
                'amount' => 'single',
                'gc' => false,
                'prior_notice' => false,
            ],
            // The statutory form's sworn statement ("… being sworn, says: I am the
            // claimant …") is part of the body, so the execution block prints only
            // the signature and the jurat.
            'execution' => ['statement' => false],
            'notes' => [
                'Record within 90 days after the claimant last furnished labor, professional services, materials or equipment (RCW 60.04.091). When the job builds two or more separate residential structures, the 90 days runs separately for each one (RCW 60.04.101).',
                'Give a copy to the owner or reputed owner by certified or registered mail or personal service within 14 days after filing for recording; without it the claimant forfeits any right to attorneys\' fees and costs against the owner (RCW 60.04.091, RCW 60.04.181).',
                'The lien lapses eight calendar months after recording unless the claimant sues in the superior court of the county within that time and serves the owner within 90 days after filing suit (RCW 60.04.141).',
                'Claim only the principal owed (RCW 60.04.091(1)(f)): no interest, late fees or collection costs. A court releases a frivolous claim or cuts a clearly excessive one and makes the claimant pay the other side\'s costs and attorneys\' fees (RCW 60.04.081).',
                'Item 5 prints "unknown" when the project has no owner party, as the form directs when the owner is not known; add the owner party first if the owner is known. Item 8 stays blank unless the claimant took the claim by assignment.',
                'A prime contractor on a job of $1,000 or more on residential property of four or fewer units, or on a commercial building for $1,000 to under $60,000, cannot maintain the lien unless it gave its customer the disclosure statement of RCW 18.27.114 before starting work (RCW 18.27.114(4)).',
                'Do not reuse the 2026 archive draft: it left out the first furnishing date and half of the sworn statement, and headed its jurat "Notary Acknowledgment".',
                'Undecided: RCW 60.04.091(2) says the claim "shall be acknowledged pursuant to chapter 64.08 RCW", but the statute\'s own form ends with a jurat; until counsel says otherwise, the claim carries the form\'s jurat only.',
            ],
        ],
        'lien_release' => [
            'title' => 'Release of Claim of Lien',
            'statute' => 'RCW 60.04.071',
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
            'notes' => [
                'Once paid, the claimant must immediately prepare, sign and deliver a release of the lien rights paid for when the owner or the person who paid asks for it; an unjustified delay makes the claimant pay the costs, attorneys\' fees and damages of a suit to compel it (RCW 60.04.071).',
                'Deliver the signed release to the person who paid, and record it with the county auditor to clear the claim of lien of record. Page 1 must carry the recording number of the claim being released (RCW 65.04.045(1)(d)); set it in Document details if the claim was not recorded through eRegister.',
            ],
        ],
        'prelim_notice' => [
            'title' => 'Notice to Owner',
            'statute' => 'RCW 60.04.031',
            'body' => 'documents.lien.letters.bodies.wa-notice-to-owner',
            'template_version' => 1,
            'sections' => [
                'amount' => 'estimate',
                'first_furnish' => true,
                'last_furnish' => false,
            ],
            'service' => [
                'recipients' => ['owner', 'gc'],
                'days_after' => null,
                'method' => 'certified_mail',
            ],
            'notes' => [
                'Required of everyone furnishing professional services, materials or equipment, except those who contract directly with the owner or the owner\'s common law agent (so prime contractors), laborers claiming only for labor, and subcontractors who contract directly with the prime contractor (RCW 60.04.031(1)-(2)).',
                'On a repair, alteration or remodel of an existing owner-occupied single-family residence (or its garage), everyone furnishing professional services, materials or equipment who did not contract directly with the owner-occupier must give it, subcontractors included; their lien reaches only what the owner had not yet paid the prime contractor when the notice was received (RCW 60.04.031(3)).',
                'It may be given at any time, but it protects only what was supplied after the date 60 days before it was mailed or delivered (10 days on new construction of a single-family residence). Send it within 60 days of first delivery (10 days on a new single-family residence); a later notice leaves out whatever was supplied before that window, and without a notice the lien cannot be enforced (RCW 60.04.031(1), (6)).',
                'Mail it to the owner or reputed owner by certified or registered mail, or deliver it personally and keep a receipt signed by the owner or an affidavit of service (RCW 60.04.031(1)).',
                'Send the prime contractor a copy too, unless the claimant contracted directly with it. The statute requires that copy when the prime contractor has complied with RCW 19.27.095, 60.04.230 and 60.04.261, which staff cannot check (RCW 60.04.031(1)).',
                'The statute asks only for notice in writing: no oath, declaration or notary. The 2026 archive notices added a perjury declaration and did not carry the statutory wording; do not reuse them.',
                'The residential contractor\'s "Notice to Customer" disclosure (RCW 18.27.114) is a different document: the prime contractor gives it to its customer before starting work.',
                'A professional services provider whose work is not yet visible on the site may record a Notice of Furnishing Professional Services (RCW 60.04.031(5)); eRegister does not generate it.',
            ],
        ],
        'noi' => [
            'title' => 'Notice of Intent to Record a Claim of Lien',
            'statute' => 'Chapter 60.04 RCW',
            'sections' => ['demand_days' => 10],
            'service' => [
                'recipients' => ['owner', 'gc'],
                'days_after' => null,
                'method' => 'certified_mail',
            ],
            'notes' => [
                'Washington has no notice-of-intent step; the notice to owner and the claim of lien are the statutory steps. This is a courtesy demand.',
                'Quote the exact principal owed: a frivolous or clearly excessive claim can cost the claimant the other side\'s attorneys\' fees (RCW 60.04.081).',
            ],
        ],
    ],
];
