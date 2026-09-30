<?php

/*
 * New York: Lien Law art. 2 (Mechanics' Liens), private improvements.
 *
 * Notice of mechanic's lien: § 3 (a contractor, subcontractor, laborer or
 * materialman who improves real property with the consent or at the request
 * of the owner, or of the owner's agent, contractor or subcontractor, has a
 * lien from the time the notice of lien is filed; § 2 defines lienor, owner,
 * improvement, contractor, subcontractor and materialman). § 9 lists what the
 * notice "shall state": (1) the lienor's name and residence, or a firm's
 * business address, partners and principal place of business (in New York,
 * for a foreign corporation); (1-a) the lienor's attorney, if any; (2) the
 * owner and "the interest of the owner as far as known to the lienor"; (3)
 * the person who employed the lienor or with whom the contract was made; (4)
 * the labor or materials and the agreed price or value; (5) the amount
 * unpaid; (6) when the first and last items were furnished; (7) the property,
 * with its street address in a city or village, and "whether the property
 * subject to the lien is real property improved or to be improved with a
 * single family dwelling or not" (clauses/ny-single-family-dwelling). The
 * notice "must be verified by the lienor or his agent" in the words quoted in
 * clauses/ny-verification.
 *
 * Filing: § 10 (with the clerk of the county where the property is situated,
 * within eight months after the final furnishing, four months for a single
 * family dwelling, 90 days after retainage was due; a county clerk who keeps
 * a block index files only a notice that gives the block number). Service:
 * § 11 (a copy on the owner within five days before or thirty days after
 * filing, by personal delivery, registered or certified mail or the other
 * listed methods) and § 11-b (a copy by certified mail on the person the
 * lienor contracted with, and on the contractor when the lienor contracted
 * with a subcontractor or sub-subcontractor). Under both, proof of service
 * not filed with the county clerk within 35 days after filing "shall
 * terminate the notice as a lien". Amendment: § 12-a. Duration: § 17 (one
 * year; extension). Wilful exaggeration: §§ 39, 39-a. Public improvements
 * use a different notice (§ 12).
 *
 * Certificate of discharge: § 19(1) (the lienor's certificate, "duly
 * acknowledged or proved and filed in the office where the notice of lien is
 * filed", stating that the lien is satisfied or released). Real Property Law
 * § 309-a (acknowledgments taken in New York, "must conform substantially")
 * and § 309-b (taken elsewhere, "may conform substantially") reach that
 * certificate, an acknowledged instrument; they do not reach the notice of
 * lien, which is verified and sworn before a notary (jurat). Executive Law
 * § 137: what a New York notary prints under the signature. No statute
 * prescribes a preliminary notice or a notice of intent for private
 * improvements.
 *
 * Verified against the official text on nysenate.gov on 2026-09-30 (the site
 * blocks scripted downloads, so the quoted passages were read there directly
 * and the full sections saved from newyork.public.law, which reproduces the
 * Senate text).
 */

return [
    'state' => 'NY',
    'state_name' => 'New York',
    'recording' => [
        'filing_office' => ['label' => 'County Clerk', 'method' => 'mail'],
        'cover_sheet' => true,
        'notes' => [
            'File with the county clerk of the county where the property is located (the clerk of each county when it lies in more than one) within eight months after the last item of work or materials, four months for a single family dwelling, or 90 days after retainage was due to be released (N.Y. Lien Law § 10(1)).',
            'Where the county clerk keeps a block index, as the New York City clerks do, the notice is filed only if it gives the block number (N.Y. Lien Law § 10(2)); set Block and Lot in Document details.',
            'Serve a copy on the owner within five days before or 30 days after filing, by personal delivery or by registered or certified mail to the owner\'s last known residence, or a corporation\'s last known place of business (N.Y. Lien Law § 11). In the same window, serve a copy by certified mail on the person the claimant contracted with, and also on the general contractor when the claimant contracted with a subcontractor or sub-subcontractor; add the general contractor as a recipient in that case (§ 11-b).',
            'File the affidavits of service with the county clerk within 35 days after the notice is filed, or the notice terminates as a lien (N.Y. Lien Law §§ 11, 11-b). Serving up to five days before filing lets the affidavit of service go in with the notice, as in the 2026 Queens filing.',
            'The lien lasts one year from filing unless a foreclosure action is started and a notice of pendency filed, or an extension is filed with the county clerk within that year; a single family dwelling lien can be extended only by court order (N.Y. Lien Law § 17).',
            'A wilfully exaggerated lien is void and cannot be refiled for the same claim, and the claimant is liable for the owner\'s or contractor\'s damages, including attorney\'s fees and the amount overclaimed (N.Y. Lien Law §§ 39, 39-a). State the exact amount unpaid.',
            'Without a court order, a lien can be amended only to reduce the amount: within 60 days after filing, before any action to enforce or cancel it, on 20 days\' notice to the owner, mortgagees and other lienors; wilful exaggeration survives the amendment (N.Y. Lien Law § 12-a). Serve the amendment by certified mail as § 11-b requires.',
            'A lien on a public improvement is a different notice, filed with the agency in charge and its financial officer within 30 days after completion and acceptance (N.Y. Lien Law § 12); these documents are for private improvements.',
            'Check whether the county clerk takes filings through the NYS Courts Electronic Document Delivery System (EDDS), as Queens does; otherwise mail the notice with the filing cover sheet.',
        ],
    ],
    'execution' => [
        'verification' => 'sworn',
        'notary' => true,
        'notary_form' => 'jurat',
    ],
    'service' => [
        // § 11 (owner) and § 11-b (the person the claimant contracted with; 'customer'
        // resolves to the hiring party). The contractor is added only for claimants
        // below a subcontractor, per the note above.
        'recipients' => ['owner', 'customer'],
        'days_after' => 30,
        'method' => 'certified_mail',
        'proof' => 'affidavit',
    ],
    'kinds' => [
        'mechanics_lien' => [
            'title' => 'Notice of Mechanic\'s Lien',
            'statute' => 'N.Y. Lien Law §§ 9 and 10',
            'sections' => [
                'amount' => 'breakdown',
                'gc' => true,
                'hiring_party' => true,
                'block_lot' => true,
                'owner_interest' => true,
                'prior_notice' => false,
                'first_furnish' => true,
                'last_furnish' => true,
            ],
            // § 9 prescribes what the verification must say, so the clause view
            // prints it in place of the generic sworn statement; the jurat
            // follows the signature.
            'execution' => ['statement' => false],
            'clauses' => [
                // § 9(7): single family dwelling or not.
                'after_property' => ['documents.lien.instruments.clauses.ny-single-family-dwelling'],
                // § 9, last paragraph: the verification.
                'before_signature' => ['documents.lien.instruments.clauses.ny-verification'],
            ],
            'notes' => [
                'Mark whether the property is improved or to be improved with a single family dwelling before the notice is signed: the notice must say so (N.Y. Lien Law § 9(7)), and the answer sets the filing deadline (§ 10) and how the lien can be extended (§ 17). "Is not" prints on its own only when the project is classed as commercial.',
                'Set the owner\'s interest (for example "fee simple"), block and lot in Document details (N.Y. Lien Law § 9(2), § 10(2)).',
                'The notice has no line for three § 9 items that apply only sometimes: the partners\' names when the claimant is a partnership, its principal place of business in New York when it is a corporation formed elsewhere (N.Y. Lien Law § 9(1)), and the name and address of the claimant\'s attorney, if any (§ 9(1-a)). Add them before signing when they apply.',
                'A New York notary prints, types or stamps under the signature, in black ink, the notary\'s name, "Notary Public State of New York", the county where the notary originally qualified and the commission expiration date, and where required the county where the certificate of official character is filed; a notary who qualified or filed in a New York City county adds the official number (N.Y. Executive Law § 137). A notary in another state follows that state\'s rules.',
                'Undecided: whether a verification sworn outside New York needs the certificate CPLR 2309(c) calls for (the one a deed acknowledged outside New York needs to be recorded); until counsel says otherwise, file it without one, as with the Kentucky-sworn affidavit of service Queens accepted in 2026.',
            ],
        ],
        'lien_release' => [
            'title' => 'Certificate of Discharge of Mechanic\'s Lien',
            'statute' => 'N.Y. Lien Law § 19(1)',
            'execution' => [
                'verification' => 'acknowledged',
                'notary' => true,
                'notary_form' => 'acknowledgment',
            ],
            // Nothing requires serving the certificate; the owner gets a copy.
            'service' => [
                'recipients' => ['owner'],
                'days_after' => null,
                'method' => 'certified_mail',
                'proof' => 'declaration',
            ],
            'clauses' => [
                // § 19(1): the certificate states "that the lien is satisfied or released as to
                // the whole or a portion of the real property affected thereby and may be
                // discharged in whole or in part"; this certificate discharges the whole lien.
                'affirmations' => [
                    'The undersigned lienor certifies that the lien is satisfied and released as to the whole of the real property affected thereby and may be discharged in whole (N.Y. Lien Law § 19(1)).',
                ],
            ],
            'notes' => [
                'File the certificate with the county clerk where the notice of lien was filed; the clerk notes it in the lien docket (N.Y. Lien Law § 19(1)). This certificate discharges the whole lien; a partial discharge must name the part of the property released.',
                'No statute requires serving the certificate; mail the owner a copy once it is filed.',
                'Undecided: whether the printed acknowledgment is close enough to the Real Property Law § 309-a form, which an acknowledgment taken in New York must substantially follow (§ 309-b gives the form for one taken elsewhere); until counsel says otherwise, file it as printed, and if a county clerk rejects it, have the notary complete the § 309-a or § 309-b form instead.',
                'A New York notary prints, types or stamps under the signature, in black ink, the notary\'s name, "Notary Public State of New York", the county where the notary originally qualified and the commission expiration date, and where required the county where the certificate of official character is filed; a notary who qualified or filed in a New York City county adds the official number (N.Y. Executive Law § 137). A notary in another state follows that state\'s rules.',
            ],
        ],
        'prelim_notice' => [
            'title' => 'Notice of Furnishing (courtesy)',
            'service' => [
                'recipients' => ['owner'],
                'days_after' => null,
                'method' => 'certified_mail',
            ],
            'notes' => [
                'New York has no preliminary notice for private improvements, and no statute requires this one; it does not preserve or extend lien rights. It is a courtesy to the owner.',
            ],
        ],
        'noi' => [
            'title' => 'Notice of Intent to File a Mechanic\'s Lien',
            'statute' => 'N.Y. Lien Law art. 2',
            'sections' => ['demand_days' => 10],
            'service' => [
                'recipients' => ['owner', 'customer'],
                'days_after' => null,
                'method' => 'certified_mail',
            ],
            'notes' => [
                'Not required in New York; a demand courtesy sent before the notice of lien is filed. It does not extend the filing deadline in N.Y. Lien Law § 10 (eight months, or four for a single family dwelling).',
            ],
        ],
    ],
];
