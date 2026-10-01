<?php

/*
 * New Jersey: Construction Lien Law, N.J.S.A. 2A:44A-1 to -38.
 *
 * Lien claim: § 2A:44A-8 ("The lien claim shall be filed in substantially the
 * following form": items 1-6, the Claimant's Representation and Verification
 * "under oath" with seven statements, a suggested notarial for an individual
 * and for a corporate or limited liability claimant, and the Notice to Owner
 * of Real Property; bodies/nj-construction-lien-claim and two clause views).
 * § 2A:44A-6(a)(1): the form "shall be signed, acknowledged and verified by
 * oath of the claimant"; (a)(2): lodged for record with the county clerk
 * within 90 days after the last work, services, material or equipment, or on
 * a residential construction contract within 10 days after the arbitrator's
 * determination and within 120 days. § 2A:44A-7: within 10 days after
 * lodging, serve a copy marked received for filing on the owner and the
 * contractor or subcontractor the claim is asserted against, by personal
 * service or by simultaneous registered or certified mail (or courier) and
 * ordinary mail. § 2A:44A-2: "Contract" is a signed writing (a supplier's
 * signed delivery or order slip counts); "lodged for record" and "county
 * clerk". §§ 2A:44A-3, -9: a lien for the value under the contract, capped
 * by the unpaid contract price and the lien fund. § 2A:44A-14: sue within one
 * year, or within 30 days after a written demand. § 2A:44A-15: a claim
 * without basis, willfully overstated or not in substantially the statutory
 * form is forfeited, with costs, attorneys' fees and damages.
 *
 * Residential construction: § 2A:44A-20(b) (the Notice of Unpaid Balance and
 * Right to File Lien "shall be filed in substantially the following form";
 * letters/bodies/nj-notice-of-unpaid-balance) and § 2A:44A-21(b) (lodge it
 * within 60 days, serve it under § 7, serve a demand for arbitration within
 * 10 days). Discharge: § 2A:44A-30(a) (a certificate "duly acknowledged or
 * proved" within 30 days after payment, satisfaction or settlement or 7 days
 * after a demand, stating the filing date, book and page, owner, location and
 * the person for whom the work was provided; the section sets out no form for
 * it, only the owner's affidavit in (d)), § 2A:44A-35. Recording: N.J.S.A.
 * 46:26A-3 (acknowledged or proved; names printed under signatures),
 * 46:26A-5 (document summary sheet, else $20 more); fees 22A:2-29 (per
 * 2A:44A-13(e)). Acknowledgments 46:14-2.1; notarial certificates 52:7-19,
 * 52:7-10.12. §§ 2A:44A-16, -19 and -24 were repealed by P.L.2010, c.119.
 *
 * Verified against the New Jersey Legislature's statutes database
 * (lis.njleg.state.nj.us, "updated through P.L.2026, c.30") on 2026-09-30;
 * law.justia.com refused automated reading.
 */

return [
    'state' => 'NJ',
    'state_name' => 'New Jersey',
    'recording' => [
        'filing_office' => ['label' => 'County Clerk', 'method' => 'either'],
        'parcel_label' => 'Parcel ID',
        'fee_note' => 'N.J.S.A. 22A:2-29: construction lien $15; notice of unpaid balance, discharge $15; notation $5 (2026).',
        'notes' => [
            'Lodge the claim for record with the county clerk of the county where the property is located; some clerks e-record through vendors, and practice varies by county (N.J.S.A. 2A:44A-2, 2A:44A-6).',
            'Other than residential construction, lodge the claim within 90 days after the last work, services, material or equipment for which payment is claimed (N.J.S.A. 2A:44A-6(a)(2)).',
            'Residential construction: lodge a Notice of Unpaid Balance and Right to File Lien within 60 days and demand arbitration within 10 days after lodging it, then lodge the claim within 10 days after the arbitrator\'s determination and within 120 days after the last work (N.J.S.A. 2A:44A-21(b), 2A:44A-6(a)(2)).',
            'Within 10 days after lodging, serve a copy marked received for filing on the owner and on the contractor or subcontractor the claim is asserted against, by personal service, or by certified or registered mail (or a commercial courier) and ordinary mail sent at the same time (N.J.S.A. 2A:44A-7).',
            'The lien needs a written contract signed by the party it is asserted against (for a supplier, a signed delivery or order slip); an oral contract gives no lien (N.J.S.A. 2A:44A-2, 2A:44A-3).',
            'The amount may not exceed the unpaid contract price or the lien fund (N.J.S.A. 2A:44A-9), and a claim without basis or willfully overstated is forfeited with costs, fees and damages (N.J.S.A. 2A:44A-15), so the amount must be exact.',
            'Sue in the Superior Court within one year after the last work, or within 30 days after a written demand to sue, or the lien is forfeited and must be discharged (N.J.S.A. 2A:44A-14).',
            'Public works awarded by a public entity carry no construction lien (N.J.S.A. 2A:44A-5(b)).',
            'Send the county\'s document summary (cover) sheet with each document; without one the recording office charges $20 more for indexing (N.J.S.A. 46:26A-5(b), (c)).',
        ],
    ],
    'execution' => [
        'verification' => 'sworn',
        'notary' => true,
        'notary_form' => 'jurat',
    ],
    'service' => [
        // The owner, and the contractor or subcontractor the claim is asserted against (§ 2A:44A-7(a)).
        'recipients' => ['owner', 'customer'],
        'days_after' => 10,
        'method' => 'certified_mail',
        'proof' => 'declaration',
    ],
    'kinds' => [
        'mechanics_lien' => [
            'title' => 'Construction Lien Claim',
            'statute' => 'N.J.S.A. 2A:44A-8',
            'body' => 'documents.lien.instruments.bodies.nj-construction-lien-claim',
            'template_version' => 1,
            'sections' => [
                // The body prints the form's own A-G amount lines and never the generic table;
                // 'breakdown' turns on the package's contract-minus-payments check, which is the
                // form's E - [F + G].
                'amount' => 'breakdown',
                'gc' => false,
                'block_lot' => true,
                'prior_notice' => false,
            ],
            // § 2A:44A-6(a)(1): "signed, acknowledged and verified by oath". The body carries the
            // form's verification, so the execution block prints the signature and the jurat, and
            // the form's suggested notarial (an acknowledgment) and its Notice to Owner follow.
            'execution' => ['statement' => false],
            'clauses' => [
                'after_execution' => [
                    'documents.lien.instruments.clauses.nj-lien-claim-notarial',
                    'documents.lien.instruments.clauses.nj-notice-to-owner',
                ],
            ],
            'notes' => [
                'The claim must be signed, acknowledged and verified by oath (N.J.S.A. 2A:44A-6(a)(1)), so the notary completes both the jurat under the signature and the form\'s notarial (the acknowledgment) after it.',
                'Enter Block and Lot in Document details from the tax bill or the county\'s records; the form requires them.',
                'The municipality prints from the jobsite city; a mailing town can differ from the taxing municipality, so check it against the tax bill.',
                'The residential answer follows the project\'s property type, but residential construction means work on a one- to three-family dwelling or on a unit designed to be sold as a residence, not on rental units (N.J.S.A. 2A:44A-2).',
                'Item 1 is dated the day the claim is generated; regenerate it on the day it is signed.',
                'On residential work, fill items 5 and 6 by hand from the lodged Notice of Unpaid Balance and the arbitrator\'s award before signing; the form warns that an incomplete claim may be invalid.',
                'A sub-subcontractor or a supplier to a subcontractor also serves the contractor above its hiring party when the claim is asserted against it (N.J.S.A. 2A:44A-7(a)).',
                'The archive template ended with an acknowledgment only and added interest, costs and attorney\'s fees to the claim; the statutory form has neither.',
                'Undecided: whether the form\'s notarial alone, without the separate jurat, satisfies "verified by oath"; until counsel says otherwise, the notary completes both.',
            ],
        ],
        'lien_release' => [
            'title' => 'Certificate of Discharge of Construction Lien Claim',
            'statute' => 'N.J.S.A. 2A:44A-30',
            // § 2A:44A-30(a), § 2A:44A-35: "duly acknowledged or proved".
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
            'clauses' => [
                'affirmations' => [
                    // § 2A:44A-30(a): a certificate "directing the county clerk to discharge the lien claim of record".
                    'The undersigned claimant directs the County Clerk to discharge the lien claim of record.',
                ],
                // The five items § 2A:44A-30(a) says the certificate "shall contain".
                'after_property' => ['documents.lien.instruments.clauses.nj-discharge-particulars'],
            ],
            'notes' => [
                'File the certificate within 30 days after payment, satisfaction or settlement, or within 7 days after any interested party demands it; a claimant who fails to discharge owes costs, attorneys\' fees and damages (N.J.S.A. 2A:44A-30(a), (e)).',
                'Set the lien claim\'s filing date, book and page in Document details: the certificate must state them with the owner, the property location and the person for whom the work was provided (N.J.S.A. 2A:44A-30(a)).',
                'Discharging the claim also discharges the Notice of Unpaid Balance filed with it (N.J.S.A. 2A:44A-33(e)).',
                'If an action to enforce the lien was filed, the party who filed it must also discharge the notice of lis pendens (N.J.S.A. 2A:44A-30(f)).',
                'No statute requires serving the certificate; send the owner a copy.',
                'The certificate recites full satisfaction of the claim; when a claim was settled for less or forfeited (N.J.S.A. 2A:44A-14), have the wording checked before signing.',
            ],
        ],
        'prelim_notice' => [
            'title' => 'Notice of Unpaid Balance and Right to File Lien',
            'statute' => 'N.J.S.A. 2A:44A-20',
            'body' => 'documents.lien.letters.bodies.nj-notice-of-unpaid-balance',
            'template_version' => 1,
            'sections' => [
                // The body prints the form's own A-G amount lines; 'breakdown' only turns on the
                // package's contract-minus-payments check.
                'amount' => 'breakdown',
                'gc' => false,
                'block_lot' => true,
                'prior_notice' => false,
            ],
            // Lodged for record, so signed and acknowledged (N.J.S.A. 46:26A-3(a)(3)); its own
            // verification is not under oath.
            'execution' => [
                'verification' => 'acknowledged',
                'notary' => true,
                'notary_form' => 'acknowledgment',
                'statement' => false,
            ],
            'service' => [
                // On residential construction it is served like a lien claim (§ 2A:44A-21(b)(2), § 2A:44A-7).
                'recipients' => ['owner', 'customer'],
                'days_after' => null,
                'method' => 'certified_mail',
                'proof' => 'declaration',
            ],
            'notes' => [
                'Residential construction: lodge this notice for record with the county clerk within 60 days after the last work, serve it the way N.J.S.A. 2A:44A-7 serves a lien claim, and serve a demand for arbitration within 10 days after lodging it (N.J.S.A. 2A:44A-21(b)(1)-(3)); eRegister does not prepare the demand.',
                'The arbitrator\'s determination decides whether and for how much the lien claim may be lodged (N.J.S.A. 2A:44A-21(b)(4), (8)).',
                'On other work the notice is optional: it gives the later lien claim priority over conveyances, leases and mortgages recorded after it, and it need not be served (N.J.S.A. 2A:44A-20(b), (c)).',
                'It prints in the letter layout without a recorder space; the county clerk lodges it as filed.',
                'Undecided: whether county clerks want a first-page recording format for the notice; until counsel says otherwise, lodge it as generated.',
                'Block and Lot print as blanks to fill by hand: the Document details card offers them only on lien filings.',
                'Item 1 is dated the day the notice is generated; regenerate it on the day it is signed.',
                'A notice without basis, significantly overstated or not in substantially the statutory form makes the claimant liable for damages, costs and attorneys\' fees (N.J.S.A. 2A:44A-21(b)(12)).',
            ],
        ],
        'noi' => [
            'title' => 'Notice of Intent to File a Construction Lien Claim',
            'statute' => 'N.J.S.A. 2A:44A-1 et seq.',
            'sections' => ['demand_days' => 10],
            'service' => [
                'recipients' => ['owner', 'customer'],
                'days_after' => null,
                'method' => 'certified_mail',
            ],
            'clauses' => [
                'demand' => 'Unless payment in full of the amount stated above is received within 10 days after the date of this notice, Claimant intends to file a Construction Lien Claim against the property under the New Jersey Construction Lien Law, N.J.S.A. 2A:44A-1 et seq. If the work was residential construction, Claimant will first file a Notice of Unpaid Balance and Right to File Lien and demand arbitration, as N.J.S.A. 2A:44A-21 requires. This notice is given without waiver of any right or remedy.',
            ],
            'notes' => [
                'New Jersey has no notice of intent: on residential construction the Notice of Unpaid Balance and arbitration are the statutory steps, and on other work the claim may be lodged without prior notice. This is a demand courtesy.',
            ],
        ],
    ],
];
