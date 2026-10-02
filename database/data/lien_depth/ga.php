<?php

/*
 * GA: mechanics lien depth for the /liens/{state} page: how to file, what
 * the claim must contain, the recording offices of the ten most populous
 * counties, recent law changes and six more FAQs. Researched
 * 2026-10-02 (EREG-13) from the statute and the offices' own sites.
 * A null fee, address or e-recording value means the office does not publish
 * it: never fill one in with a guess. County 'notes' record where a fact came
 * from and are never rendered. Generated once from the research JSON; edit
 * this file directly from now on.
 */

return [
    'state' => 'GA',
    'researched_on' => '2026-10-02',
    'filing_verb' => 'file',
    'county_intro' => 'Georgia claims of lien are filed with the clerk of superior court in the county where the property sits. Since January 1, 2025, anyone filing their own lien must e-file it through the Clerks\' Authority portal at efile.gsccca.org after a photo identity check. Only listed filers, such as Georgia attorneys, banks and title insurers, may still file on paper. The statewide fee to record a lien is $25 (O.C.G.A. § 15-6-77).',
    'how_to_file' => [
        [
            'step' => 1,
            'title' => 'Look for a notice of commencement',
            'text' => 'The owner, the owner\'s agent or the contractor must file a notice of commencement with the clerk of superior court, and post it on the site, within 15 days after the contractor starts work. It names the contractor, owner, property, surety and lender. If you ask the contractor in writing for a copy and do not receive it within 10 calendar days, the notice-to-contractor requirement does not apply to you.',
            'cite' => 'O.C.G.A. § 44-14-361.5(b)',
            'residential_note' => null,
        ],
        [
            'step' => 2,
            'title' => 'Send a notice to contractor if you have no contract with the prime',
            'text' => 'If a notice of commencement was filed and you have no contract with the prime contractor (for example, a sub-subcontractor or a supplier to a subcontractor), give written notice to the owner and the contractor within 30 days after the notice of commencement was filed or 30 days after your first delivery, whichever is later. If no notice of commencement was filed, this step does not apply.',
            'cite' => 'O.C.G.A. § 44-14-361.5(a), (c)',
            'residential_note' => null,
        ],
        [
            'step' => 3,
            'title' => 'Prepare the claim of lien',
            'text' => 'Draft the claim in substantially the statutory form: who you are, the amount claimed, the property and its owner, the date the claim became due (your last day of furnishing), and what you furnished. It must also include, in at least 12-point bold type, the statement that the claim expires and is void 395 days after filing if no notice of commencement of lien action is filed, and a notice telling the owner of the right to contest the lien.',
            'cite' => 'O.C.G.A. §§ 44-14-361.1(a)(2), 44-14-367',
            'residential_note' => null,
        ],
        [
            'step' => 4,
            'title' => 'File with the clerk of superior court within 90 days',
            'text' => 'File the claim within 90 days after you completed the work or furnished the materials, in the clerk of superior court\'s office for the county where the property is located. Since January 1, 2025 self-filers must e-file real estate documents, including liens, through the Clerks\' Authority portal (efile.gsccca.org) after verifying their identity. Only listed groups, such as Georgia attorneys, banks and title insurers, may still file on paper.',
            'cite' => 'O.C.G.A. §§ 44-14-361.1(a)(2), 44-2-2, 44-2-39 (HB 1292)',
            'residential_note' => null,
        ],
        [
            'step' => 5,
            'title' => 'Send a copy to the owner within 2 business days',
            'text' => 'No later than two business days after the claim is filed, send a true copy by registered or certified mail or statutory overnight delivery to the owner. If the owner\'s address cannot be found, send it to the contractor as the owner\'s agent.',
            'cite' => 'O.C.G.A. § 44-14-361.1(a)(2)',
            'residential_note' => null,
        ],
        [
            'step' => 6,
            'title' => 'Start a lien action within 365 days',
            'text' => 'Commence a lien action for the amount of your claim within 365 days after the claim was filed. If the owner files a notice of contest of lien, your time shrinks to 60 days from receipt of that notice.',
            'cite' => 'O.C.G.A. §§ 44-14-361.1(a)(3), 44-14-368',
            'residential_note' => null,
        ],
        [
            'step' => 7,
            'title' => 'File a notice of commencement of lien action within 30 days',
            'text' => 'Within 30 days after starting the lien action, file a notice with the clerk of superior court in the county where the lien was filed, identifying the court, the case and the recorded lien. It is executed under oath by the claimant or its attorney. Without it the claim may be disregarded 395 days after filing.',
            'cite' => 'O.C.G.A. §§ 44-14-361.1(a)(3), 44-14-367',
            'residential_note' => null,
        ],
    ],
    'claim_contents' => [
        'document_name' => 'Claim of lien (materialman\'s or mechanic\'s lien)',
        'required' => [
            'Identity and capacity of the claimant (mechanic, contractor, subcontractor, materialman, architect, engineer, surveyor, etc.)',
            'The amount claimed',
            'The property (house, building or premises and the real estate on which it stands) and the name of its owner, with a description of the property',
            'The date the claim became due, which is the last date labor, services or materials were supplied to the premises',
            'What the claim is for (building, repairing, improving, furnishing material, or other work)',
            'In at least 12-point bold type: \'This claim of lien expires and is void 395 days from the date of filing of the claim of lien if no notice of commencement of lien action is filed in that time period.\'',
            'A notice to the owner that the owner has the right to contest the lien',
        ],
        'cite' => 'O.C.G.A. §§ 44-14-361.1(a)(2), 44-14-367',
        'official_form_url' => null,
        'notes' => 'The statute gives the claim\'s wording \'in substance\' (beginning \'A.B., a mechanic, contractor, subcontractor, materialman ... claims a lien in the amount of ...\'); no separate official form was found. A claim that omits the 395-day expiration statement is invalid (§ 44-14-367). The statute read does not expressly require the claim itself to be notarized; the later notice of commencement of lien action must be under oath.',
    ],
    'counties' => [
        [
            'county' => 'Fulton',
            'office' => 'Fulton County Clerk of Superior and Magistrate Courts, Real Estate Division',
            'url' => 'https://www.fultonclerk.org/400/Fee-Schedule',
            'erecording' => true,
            'erecording_vendors' => [
                'GSCCCA eFile portal (efile.gsccca.org)',
            ],
            'fee' => [
                'first_page_cents' => null,
                'additional_page_cents' => null,
                'flat_cents' => 2500,
                'summary' => '$25 to record a materialman\'s lien (same fee for lien recordings, notices, bonds and lis pendens).',
                'url' => 'https://www.fultonclerk.org/400/Fee-Schedule',
            ],
            'mailing_address' => null,
            'notes' => 'The fee page lists the Real Estate Division at the Lewis R. Slaton Courthouse, 136 Pryor Street, Suite C155 J2-101, Atlanta, GA 30303 but no recording-by-mail address. Under HB 1292 self-filers must e-file; paper is limited to exempt filers. The Real Estate Division does not take credit cards for in-person filings.',
        ],
        [
            'county' => 'Gwinnett',
            'office' => 'Gwinnett County Clerk of Courts, Deeds and Land Records',
            'url' => 'https://www.gwinnettcourts.com/deeds-and-land-records/faqs',
            'erecording' => true,
            'erecording_vendors' => [
                'GSCCCA eFile portal (efile.gsccca.org)',
            ],
            'fee' => [
                'first_page_cents' => null,
                'additional_page_cents' => null,
                'flat_cents' => null,
                'summary' => null,
                'url' => 'https://www.gwinnettcourts.com/deeds-and-land-records/fees',
            ],
            'mailing_address' => 'Gwinnett County Clerk of Court, P.O. Box 2050, Lawrenceville, GA 30046 (street: 75 Langley Drive, Lawrenceville, GA 30046)',
            'notes' => 'The FAQ says all real estate documents must be e-filed as of January 1, 2025 (HB 1292). The fee page was not read; the statewide statutory lien recording fee is $25 (O.C.G.A. § 15-6-77).',
        ],
        [
            'county' => 'Cobb',
            'office' => 'Cobb County Superior Court Clerk, Real Estate Division',
            'url' => 'https://www.cobbsuperiorcourtclerk.com/real-estate-information/real-estate-notices/',
            'erecording' => true,
            'erecording_vendors' => [
                'GSCCCA eFile portal (efile.gsccca.org)',
            ],
            'fee' => [
                'first_page_cents' => null,
                'additional_page_cents' => null,
                'flat_cents' => 2500,
                'summary' => '$25 for FiFas, liens, materialman liens, lis pendens and notices of commencement, and $25 for each cancellation, release or assignment.',
                'url' => 'https://www.cobbsuperiorcourtclerk.com/real-estate-information/real-estate-notices/',
            ],
            'mailing_address' => 'Cobb County Superior Court Clerk, Real Estate Division, 10 East Park Square, Building C, Marietta, GA 30090',
            'notes' => 'The page did not describe e-filing; e-filing through GSCCCA is mandatory statewide for self-filers under HB 1292.',
        ],
        [
            'county' => 'DeKalb',
            'office' => 'DeKalb County Clerk of Superior Court, Real Estate',
            'url' => 'https://www.dksuperiorclerk.com/real-estate/',
            'erecording' => true,
            'erecording_vendors' => [
                'GSCCCA',
                'Simplifile',
                'eRecording.com',
                'CSC Global',
                'GO ePN',
            ],
            'fee' => [
                'first_page_cents' => null,
                'additional_page_cents' => null,
                'flat_cents' => null,
                'summary' => null,
                'url' => 'https://www.dksuperiorclerk.com/fees-charts/',
            ],
            'mailing_address' => 'DeKalb County Clerk of Superior Court, Attn: Real Estate, 556 N. McDonough St., Ground Floor, Decatur, GA 30030',
            'notes' => 'The clerk says mail-in recordings are no longer accepted and all real estate filings must be e-filed through approved sites (HB 1292). The fee chart was not read; statewide lien fee is $25 (O.C.G.A. § 15-6-77).',
        ],
        [
            'county' => 'Chatham',
            'office' => 'Chatham County Clerk of Superior Court, Real Estate Division',
            'url' => 'https://superiorcourtclerk.chathamcountyga.gov/Superior/RealEstate',
            'erecording' => true,
            'erecording_vendors' => [
                'GSCCCA (gsccca.org/file)',
            ],
            'fee' => [
                'first_page_cents' => null,
                'additional_page_cents' => null,
                'flat_cents' => null,
                'summary' => null,
                'url' => 'https://superiorcourtclerk.chathamcountyga.gov/Superior/RealEstateFees',
            ],
            'mailing_address' => 'ATTN: Real Estate Division, Clerk of Superior Court, P.O. Box 10227, Savannah, GA 31412 (FedEx/UPS: 133 Montgomery Street, Room 304, Savannah, GA 31401)',
            'notes' => 'Search excerpts also name Simplifile, eRecording Partners Network and CSC as e-recording options, and show an outdated $5/$2 per-page lien fee that predates the 2020 flat fee; neither was confirmed on the clerk\'s page. Statewide lien fee is $25 (O.C.G.A. § 15-6-77).',
        ],
        [
            'county' => 'Clayton',
            'office' => 'Clayton County Clerk of Superior Court, Real Estate Division',
            'url' => 'https://www.claytoncountyga.gov/government/court-services/clerk-of-superior-court/real-estate-division/',
            'erecording' => true,
            'erecording_vendors' => [
                'GSCCCA eFile portal (efile.gsccca.org)',
            ],
            'fee' => [
                'first_page_cents' => null,
                'additional_page_cents' => null,
                'flat_cents' => null,
                'summary' => null,
                'url' => null,
            ],
            'mailing_address' => null,
            'notes' => 'The county site redirected the fetch; search excerpts give the Real Estate Division at Harold R. Banke Justice Center, 9151 Tara Boulevard, Room 1CL25, Jonesboro, GA 30236-4912 and say liens can be e-filed through GSCCCA. Not verified directly.',
        ],
        [
            'county' => 'Cherokee',
            'office' => 'Cherokee County Clerk of Superior Court, Deeds and Records',
            'url' => 'https://www.cherokeecourtclerk.com/liens',
            'erecording' => true,
            'erecording_vendors' => [],
            'fee' => [
                'first_page_cents' => null,
                'additional_page_cents' => null,
                'flat_cents' => null,
                'summary' => null,
                'url' => null,
            ],
            'mailing_address' => 'Deeds and Records, 90 North Street, Suite G-150, Canton, GA 30114',
            'notes' => 'The clerk\'s lien page says self-filers must e-file from January 1, 2025 via the clerk\'s e-filing page (cherokeecourtclerk.com/efiling) and warns that recording a lien does not mean it is valid. No fee is listed.',
        ],
        [
            'county' => 'Forsyth',
            'office' => 'Forsyth County Clerk of Superior Court, Real Estate',
            'url' => 'https://www.forsythclerk.com/RealEstate/FAQ.aspx',
            'erecording' => true,
            'erecording_vendors' => [
                'GSCCCA eFile portal (efile.gsccca.org)',
            ],
            'fee' => [
                'first_page_cents' => null,
                'additional_page_cents' => null,
                'flat_cents' => 2500,
                'summary' => '$25 per instrument for liens and for lien cancellations, releases and assignments.',
                'url' => 'https://www.forsythclerk.com/files/CourtCostsAndRecordingFees.pdf',
            ],
            'mailing_address' => 'Clerk of Court-Real Estate, 101 East Courthouse Square, Suite 1007, Cumming, GA 30040',
            'notes' => 'Fee from a search excerpt of the clerk\'s Court Costs and Recording Fees PDF; it matches O.C.G.A. § 15-6-77.',
        ],
        [
            'county' => 'Henry',
            'office' => 'Henry County Clerk of Superior Court, Real Estate Division',
            'url' => 'https://www.henrycountyga.gov/279/Real-Estate-Division',
            'erecording' => true,
            'erecording_vendors' => [
                'GSCCCA eFile portal (efile.gsccca.org)',
            ],
            'fee' => [
                'first_page_cents' => null,
                'additional_page_cents' => null,
                'flat_cents' => null,
                'summary' => null,
                'url' => null,
            ],
            'mailing_address' => null,
            'notes' => 'The division page did not show fees or an address. Search excerpts give One Courthouse Square, McDonough, GA 30253; not verified directly.',
        ],
        [
            'county' => 'Hall',
            'office' => 'Hall County Clerk of Court, Real Estate Division',
            'url' => 'https://www.hallclerk.com/RealEstate/RealEstateFAQ.aspx',
            'erecording' => true,
            'erecording_vendors' => [
                'GSCCCA eFile portal (gsccca.org)',
            ],
            'fee' => [
                'first_page_cents' => null,
                'additional_page_cents' => null,
                'flat_cents' => 2500,
                'summary' => '$25 per document to record most documents, including cancellations and assignments ($25 per reference), set by state law since January 2020.',
                'url' => 'https://www.hallclerk.com/RealEstate/RealEstateFAQ.aspx',
            ],
            'mailing_address' => 'Hall County Clerk of Court, P.O. Box 1275, Gainesville, GA 30503-1336',
            'notes' => 'E-filing requires a debit or credit card and a map/parcel number.',
        ],
    ],
    'recent_changes' => [
        [
            'title' => 'HB 1292: mandatory e-filing and ID verification for real estate filings',
            'effective' => '2025-01-01',
            'summary' => 'Self-filers must e-file all real estate documents, including liens, through the Clerks\' Authority portal at efile.gsccca.org, and every e-filer must complete photo identity verification. Only listed groups (federally insured banks and credit unions, Georgia attorneys, mortgage lenders and servicers, licensed surveyors, title insurers, real estate brokers and public officials) may still file on paper, with proof of status. A contractor filing its own lien must e-file.',
            'cite' => 'O.C.G.A. §§ 44-2-2, 44-2-39 (HB 1292, 2024 session)',
        ],
        [
            'title' => 'GSCCCA Georgia real estate e-recording rules, version 2025-04-09',
            'effective' => '2025-05-01',
            'summary' => 'The Clerks\' Authority issued updated statewide e-recording rules for real estate filings, effective May 1, 2025, to implement the e-filing mandate.',
            'cite' => 'GSCCCA Georgia Real Estate eRecording Rules (https://www.gsccca.org/docs/efiling-documents/gsccca_georgia_real_estate_erecording_rules.pdf)',
        ],
    ],
    'faqs' => [
        [
            'q' => 'Can I file a lien on a Georgia public project?',
            'a' => 'Public property is protected by the contractor\'s payment bond rather than liens. If the contractor filed a notice of commencement, give notice within 30 days after it was filed or after your first delivery, whichever is later. If you had no direct contract with the contractor, you must also give written notice of your claim within 90 days after your last work or delivery, by certified mail or sheriff\'s service.',
            'cite' => 'O.C.G.A. § 13-10-63',
        ],
        [
            'q' => 'Can a supplier to a supplier file a Georgia lien?',
            'a' => 'Generally no. The lien statute covers contractors, subcontractors, materialmen furnishing materials to subcontractors, and laborers for subcontractors and materialmen. A company that only sells to another supplier is not in that list, so it does not get a lien on the owner\'s property.',
            'cite' => 'O.C.G.A. § 44-14-361(a)',
        ],
        [
            'q' => 'What happens after I file a lien in Georgia?',
            'a' => 'Within two business days you must mail or overnight a copy to the owner. You then have 365 days from filing to start a lien action, and 30 days after starting it to file a notice of commencement of lien action with the clerk. If no such notice is filed within 395 days of the lien, the lien can be disregarded.',
            'cite' => 'O.C.G.A. §§ 44-14-361.1(a)(2)-(3), 44-14-367',
        ],
        [
            'q' => 'Can the owner challenge or remove my lien?',
            'a' => 'Yes. The owner or contractor can file a notice of contest of lien, which gives you only 60 days from receipt to start your lien action; if no notice of commencement of lien action follows, the lien is extinguished 90 days after the contest was filed. The owner or contractor can also discharge the lien by posting a bond for double the claim, or for the claim amount on the owner\'s primary residence.',
            'cite' => 'O.C.G.A. §§ 44-14-364(a), 44-14-368',
        ],
        [
            'q' => 'How do lien waivers and releases work in Georgia?',
            'a' => 'Georgia prescribes interim and final waiver forms, in at least 12-point type. A waiver signed before you are paid becomes binding unless you file an affidavit of nonpayment in the property\'s county within 90 days after signing it and send a copy to the owner within seven days. A waiver covers only lien and bond rights, not your right to sue for payment.',
            'cite' => 'O.C.G.A. § 44-14-366',
        ],
        [
            'q' => 'What does it cost to file a lien in Georgia?',
            'a' => 'Every clerk of superior court charges the same statutory fee: $25 to file a lien on real estate, regardless of page count, and $25 to file a cancellation of the lien. Filing through the GSCCCA e-file portal, which is now required for most self-filers, needs a card payment and may carry a convenience fee.',
            'cite' => 'O.C.G.A. § 15-6-77',
        ],
    ],
    'sources' => [
        [
            'title' => 'O.C.G.A. § 44-14-361.1 (FindLaw)',
            'url' => 'https://codes.findlaw.com/ga/title-44-property/ga-code-sect-44-14-361-1/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'O.C.G.A. § 44-14-361 (FindLaw)',
            'url' => 'https://codes.findlaw.com/ga/title-44-property/ga-code-sect-44-14-361/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'O.C.G.A. § 44-14-361.5 (FindLaw)',
            'url' => 'https://codes.findlaw.com/ga/title-44-property/ga-code-sect-44-14-361-5/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'O.C.G.A. § 44-14-364 (FindLaw)',
            'url' => 'https://codes.findlaw.com/ga/title-44-property/ga-code-sect-44-14-364/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'O.C.G.A. § 44-14-366 (FindLaw)',
            'url' => 'https://codes.findlaw.com/ga/title-44-property/ga-code-sect-44-14-366/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'O.C.G.A. § 44-14-367 (FindLaw)',
            'url' => 'https://codes.findlaw.com/ga/title-44-property/ga-code-sect-44-14-367/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'O.C.G.A. § 44-14-368 (FindLaw)',
            'url' => 'https://codes.findlaw.com/ga/title-44-property/ga-code-sect-44-14-368/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'O.C.G.A. § 13-10-63 (FindLaw)',
            'url' => 'https://codes.findlaw.com/ga/title-13-contracts/ga-code-sect-13-10-63/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'O.C.G.A. § 15-6-77 (FindLaw)',
            'url' => 'https://codes.findlaw.com/ga/title-15-courts/ga-code-sect-15-6-77/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Camden County Clerk of Superior Court, HB 1292 real estate filing requirements',
            'url' => 'https://www.camdencountyga.gov/DocumentCenter/View/17481/House-Bill-1292-Real-Estate-Filing-Requirements-Effective-Jan-1-2025',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'GSCCCA Georgia Real Estate eRecording Rules (eff. May 1, 2025)',
            'url' => 'https://www.gsccca.org/docs/efiling-documents/gsccca_georgia_real_estate_erecording_rules.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Levelset, Georgia Lien Law FAQs',
            'url' => 'https://www.levelset.com/mechanics-lien/georgia-lien-law-faqs/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Fulton County Clerk, Fee Schedule',
            'url' => 'https://www.fultonclerk.org/400/Fee-Schedule',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Gwinnett County Courts, Deeds & Land Records FAQs',
            'url' => 'https://www.gwinnettcourts.com/deeds-and-land-records/faqs',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Cobb County Superior Court Clerk, Real Estate Notices and fees',
            'url' => 'https://www.cobbsuperiorcourtclerk.com/real-estate-information/real-estate-notices/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DeKalb County Clerk of Superior Court, Real Estate',
            'url' => 'https://www.dksuperiorclerk.com/real-estate/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Chatham County Clerk of Superior Court, Real Estate Division',
            'url' => 'https://superiorcourtclerk.chathamcountyga.gov/Superior/RealEstate',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Clayton County Clerk of Superior Court, Real Estate Division',
            'url' => 'https://www.claytoncountyga.gov/government/court-services/clerk-of-superior-court/real-estate-division/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Cherokee County Clerk of Courts, Liens',
            'url' => 'https://www.cherokeecourtclerk.com/liens',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Forsyth County Clerk, Real Estate FAQ',
            'url' => 'https://www.forsythclerk.com/RealEstate/FAQ.aspx',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Henry County, Real Estate Division',
            'url' => 'https://www.henrycountyga.gov/279/Real-Estate-Division',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Hall County Clerk, Real Estate FAQ',
            'url' => 'https://www.hallclerk.com/RealEstate/RealEstateFAQ.aspx',
            'accessed' => '2026-10-02',
        ],
    ],
];
