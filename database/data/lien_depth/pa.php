<?php

/*
 * PA: mechanics lien depth for the /liens/{state} page: how to file, what
 * the claim must contain, the recording offices of the ten most populous
 * counties, recent law changes and six more FAQs. Researched
 * 2026-10-02 (EREG-13) from the statute and the offices' own sites.
 * A null fee, address or e-recording value means the office does not publish
 * it: never fill one in with a guess. County 'notes' record where a fact came
 * from and are never rendered. Generated once from the research JSON; edit
 * this file directly from now on.
 */

return [
    'state' => 'PA',
    'researched_on' => '2026-10-02',
    'filing_verb' => 'file',
    'county_intro' => 'Pennsylvania lien claims are filed with the prothonotary of the county court of common pleas, not the recorder of deeds. Some counties now call that office the office of judicial records. Fees vary widely by county, from $25.25 to $259 in the counties below, and all ten accept e-filing.',
    'how_to_file' => [
        [
            'step' => 1,
            'title' => 'On searchable projects, file a Notice of Furnishing in the State Construction Notices Directory',
            'text' => 'If the improvement costs $1.5 million or more and the owner has filed and posted a Notice of Commencement in the Department of General Services\' State Construction Notices Directory, every subcontractor must file a Notice of Furnishing in the directory within 45 days after first performing work or first delivering materials to the job site. A subcontractor that fails to substantially comply forfeits its lien right, unless the owner or contractor pressured it not to file.',
            'cite' => 'Mechanics\' Lien Law of 1963 (49 P.S. § 1101 et seq.) §§ 201(18), 501.3(b)-(c), 501.6',
            'residential_note' => null,
        ],
        [
            'step' => 2,
            'title' => 'Subcontractors: serve a formal 30-day notice of intention to file',
            'text' => 'A subcontractor\'s claim is valid only if, at least 30 days before filing, it gave the owner a formal written notice of intention to file a claim. The notice states the claimant, who hired it, the amount due, the general nature of the labor or materials, the completion date, and a description of the property. Serve it by first-class, registered or certified mail on the owner or agent, by an adult as with a writ of summons, or, failing that, by posting on the property.',
            'cite' => '49 P.S. § 1501(b.1)-(d) (Act § 501)',
            'residential_note' => 'A subcontractor has no lien on an owner-occupied (or tenant-occupied) one- or two-unit residence or single townhouse if the owner or tenant paid the full contract price to the contractor (§ 301(b)).',
        ],
        [
            'step' => 3,
            'title' => 'Prepare the mechanics\' lien claim',
            'text' => 'Prepare the claim with the contents required by section 503: claimant and whether contractor or subcontractor, owner name and address, completion date, the hiring party and notice dates (for subcontractors), a description of the labor or materials (detailed with prices unless a fixed-sum contract), the amount claimed (more than $500), and a description of the property. The act does not itself require notarization; county practice and the form used may call for a verification.',
            'cite' => '49 P.S. §§ 1301(a), 1503 (Act §§ 301, 503)',
            'residential_note' => null,
        ],
        [
            'step' => 4,
            'title' => 'File with the prothonotary within 6 months after completing your work',
            'text' => 'File the claim with the prothonotary (or the county\'s equivalent court records office) of the court of common pleas in the county where the property lies, within six months after completion of your own work. Completion means the last labor or materials your contract required. The prothonotary enters it on the judgment index and mechanics\' lien docket. Most large counties accept civil e-filing.',
            'cite' => '49 P.S. §§ 1201(13), 1502(a)(1), (b), 1507 (Act §§ 201, 502, 507)',
            'residential_note' => null,
        ],
        [
            'step' => 5,
            'title' => 'Serve notice of the filing on the owner within one month',
            'text' => 'Within one month after filing, serve the owner with written notice of the filing, giving the court, term, number and filing date. Service is by an adult in the same manner as a writ of summons in assumpsit, or by posting on the property if that fails. Then file an affidavit of service (or the owner\'s acceptance of service) within 20 days after service. Missing either deadline is grounds to strike the claim.',
            'cite' => '49 P.S. § 1502(a)(2), (c) (Act § 502)',
            'residential_note' => null,
        ],
        [
            'step' => 6,
            'title' => 'Start an action on the claim within 2 years',
            'text' => 'Commence an action to obtain judgment on the claim within two years after filing, unless the owner extends the time in writing. A verdict or judgment must be obtained within five years after filing, or the claim is lost. Procedure follows the Rules of Civil Procedure.',
            'cite' => '49 P.S. § 1701(b), (d) (Act § 701)',
            'residential_note' => null,
        ],
    ],
    'claim_contents' => [
        'document_name' => 'Mechanics\' lien claim',
        'required' => [
            'Name of the claimant and whether it files as contractor or subcontractor',
            'Name and address of the owner or reputed owner',
            'Date of completion of the claimant\'s work',
            'For a subcontractor: the name of the person it contracted with and the dates the formal notice of intention (and any preliminary notice) were given',
            'For a contractor under a fixed-sum contract: identification of the contract and a general statement of the labor or materials',
            'In all other cases: a detailed statement of the labor or materials furnished and the prices charged for each',
            'The amount claimed to be due (must exceed $500)',
            'A description of the improvement and the property sufficient to identify them',
        ],
        'cite' => '49 P.S. §§ 1301(a), 1503 (Act §§ 301, 503)',
        'official_form_url' => null,
        'notes' => 'The act lists contents but prescribes no wording and no state form. Section 503 does not require notarization; whether a verification is expected is a county-practice and form question (unconfirmed). The Notice of Furnishing form is prescribed in § 501.3(b)(3).',
    ],
    'counties' => [
        [
            'county' => 'Philadelphia',
            'office' => 'First Judicial District, Office of Judicial Records - Civil (formerly the Prothonotary), Court of Common Pleas',
            'url' => 'https://www.courts.phila.gov/pdf/prothyfees.pdf',
            'erecording' => true,
            'erecording_vendors' => [
                'First Judicial District Civil Electronic Filing System',
            ],
            'fee' => [
                'first_page_cents' => null,
                'additional_page_cents' => null,
                'flat_cents' => 11669,
                'summary' => 'Filing of a lien as a first filing: $116.69 (includes $25.00 electronic filing fee); fees effective November 12, 2025.',
                'url' => 'https://www.courts.phila.gov/pdf/prothyfees.pdf',
            ],
            'mailing_address' => 'Office of Judicial Records - Civil, Room 284 City Hall, Philadelphia, PA 19107',
            'notes' => 'The fee schedule lists a generic \'lien as a first filing\' line rather than a mechanics\' lien line. The address came from a court directory via search summary.',
        ],
        [
            'county' => 'Allegheny',
            'office' => 'Allegheny County Department of Court Records, Civil/Family Division',
            'url' => 'https://www.alleghenycounty.us/Government/Court-Related/Civil-and-Family-Court-Records/Civil-and-Family-Court-Record-Fees/New-Case-Fees',
            'erecording' => true,
            'erecording_vendors' => [
                'Department of Court Records e-filing (dcr.alleghenycounty.us)',
            ],
            'fee' => [
                'first_page_cents' => null,
                'additional_page_cents' => null,
                'flat_cents' => 10275,
                'summary' => 'Mechanics\' Lien Claim: $102.75 (prices include a $10 satisfaction fee when applicable).',
                'url' => 'https://www.alleghenycounty.us/Government/Court-Related/Civil-and-Family-Court-Records/Civil-and-Family-Court-Record-Fees/New-Case-Fees',
            ],
            'mailing_address' => 'Department of Court Records, Civil/Family Division, City-County Building, 414 Grant Street, First Floor, Pittsburgh, PA 15219-2469',
            'notes' => 'E-filing has been mandatory for attorneys filing civil documents since November 13, 2023 (per the court FAQ via search summary).',
        ],
        [
            'county' => 'Montgomery',
            'office' => 'Montgomery County Prothonotary',
            'url' => 'https://www.montgomerycountypa.gov/departments/prothonotary',
            'erecording' => true,
            'erecording_vendors' => [],
            'fee' => [
                'first_page_cents' => null,
                'additional_page_cents' => null,
                'flat_cents' => 3700,
                'summary' => 'Mechanics Lien Claim: $37 in person or by e-filing (fee schedule effective January 1, 2026). A Complaint in Mechanics Lien is $304.50.',
                'url' => 'https://assets.montgomerycountypa.gov/files/2026-06/Prothonotary%20-%20Jan%201%202026%20fee%20schedule%20with%20custody%20changes%20updated%20ADA.pdf',
            ],
            'mailing_address' => 'Montgomery County Prothonotary, P.O. Box 311, Norristown, PA 19404-0311',
            'notes' => 'The fee schedule has an e-filing column, so e-filing is available; system name not read.',
        ],
        [
            'county' => 'Bucks',
            'office' => 'Bucks County Prothonotary',
            'url' => 'https://www.buckscounty.gov/361/Prothonotary',
            'erecording' => true,
            'erecording_vendors' => [
                'Bucks County Prothonotary E-Filing and Web Viewer',
            ],
            'fee' => [
                'first_page_cents' => null,
                'additional_page_cents' => null,
                'flat_cents' => 3300,
                'summary' => 'Liens - Mechanics, Federal, Commonwealth or Certified Copy Incoming: $33.00 (paper or e-file), effective January 1, 2026.',
                'url' => 'https://www.buckscounty.gov/DocumentCenter/View/1283/2023-Prothonotary-Civil-Fee-Bill-PDF',
            ],
            'mailing_address' => 'Bucks County Prothonotary, Bucks County Justice Center, 100 North Main Street, Doylestown, PA 18901',
            'notes' => null,
        ],
        [
            'county' => 'Delaware',
            'office' => 'Delaware County Office of Judicial Support (Civil Division)',
            'url' => 'https://delcopa.gov/ojs/civil-division',
            'erecording' => true,
            'erecording_vendors' => [
                'Delaware County E-Filing (delcopa.gov/ojs/efile)',
            ],
            'fee' => [
                'first_page_cents' => null,
                'additional_page_cents' => null,
                'flat_cents' => 2525,
                'summary' => 'Mechanics Lien: $25.25 (civil filing fee schedule effective January 1, 2026).',
                'url' => 'https://www.delcopa.gov/sites/default/files/2026-02/civilfees.pdf',
            ],
            'mailing_address' => 'Office of Judicial Support, Government Center, Room 127, 201 W. Front St., Media, PA 19063',
            'notes' => 'Address from a search summary of the county site.',
        ],
        [
            'county' => 'Lancaster',
            'office' => 'Lancaster County Prothonotary',
            'url' => 'https://co.lancaster.pa.us/155/Prothonotary',
            'erecording' => true,
            'erecording_vendors' => [
                'File & ServeXpress',
            ],
            'fee' => [
                'first_page_cents' => null,
                'additional_page_cents' => null,
                'flat_cents' => 18150,
                'summary' => 'Mechanics Lien Claim: $181.50 ($120.50 initial filing fee, $0.50 tax, $41.25 JCS/ATJ, $14.25 final costs, $5.00 PAF); fee bill effective December 1, 2025.',
                'url' => 'https://co.lancaster.pa.us/DocumentCenter/View/18264/2026-Prothonotary-Fee-Bill?bidId=',
            ],
            'mailing_address' => 'Lancaster County Prothonotary, Lancaster County Courthouse, 50 N. Duke St., P.O. Box 83480, Lancaster, PA 17608',
            'notes' => 'E-filing provider (File & ServeXpress) and address came from a search summary of the county FAQ and directory.',
        ],
        [
            'county' => 'Chester',
            'office' => 'Chester County Prothonotary',
            'url' => 'https://www.chesco.org/173/Prothonotary',
            'erecording' => true,
            'erecording_vendors' => [
                'Chester County E-Filing (chesco.org/3512/E-Filing)',
            ],
            'fee' => [
                'first_page_cents' => null,
                'additional_page_cents' => null,
                'flat_cents' => 3750,
                'summary' => 'Mechanic\'s Lien: $37.50 (fee schedule effective January 1, 2026).',
                'url' => 'https://chesco.org/DocumentCenter/View/73957/Prothonotary-Fees?bidId=',
            ],
            'mailing_address' => 'Chester County Prothonotary, Chester County Justice Center, Suite 1425, 201 West Market Street, P.O. Box 2746, West Chester, PA 19380-0989',
            'notes' => 'E-filing accepted for civil/non-family filings.',
        ],
        [
            'county' => 'York',
            'office' => 'York County Prothonotary',
            'url' => 'https://yorkcountypa.gov/329/Prothonotary',
            'erecording' => true,
            'erecording_vendors' => [
                'York County civil e-filing portal',
            ],
            'fee' => [
                'first_page_cents' => null,
                'additional_page_cents' => null,
                'flat_cents' => 25900,
                'summary' => 'Mechanics claim (charged as commencement of a new action): $259.00; fees effective January 1, 2026.',
                'url' => 'https://yorkcountypa.gov/DocumentCenter/View/7978/FEE-SCHEDULE-JANUARY-1-2026',
            ],
            'mailing_address' => 'York County Prothonotary, York County Judicial Center, 45 N. George Street, York, PA 17401',
            'notes' => 'Civil e-filing has been mandatory for most non-family civil matters since October 1, 2020 (per search summary of the county page).',
        ],
        [
            'county' => 'Berks',
            'office' => 'Berks County Prothonotary',
            'url' => 'https://www.berkspa.gov/departments/prothonotary',
            'erecording' => true,
            'erecording_vendors' => [],
            'fee' => [
                'first_page_cents' => null,
                'additional_page_cents' => null,
                'flat_cents' => 6000,
                'summary' => 'Mechanics Liens or Municipal Liens: $60.00 (fee bill effective September 28, 2026; satisfaction fee not included). Fees include $15.00 e-filing fees.',
                'url' => 'https://www.berkspa.gov/getmedia/d70498fe-b117-45a5-b8f5-a0f613af0338/Fee-Bill-effective-9-28-2026-Paymentus-Third-party-credit-debit-card-processor.pdf',
            ],
            'mailing_address' => 'Prothonotary\'s Office, Berks County Courthouse, 2nd Floor, 633 Court Street, Reading, PA 19601',
            'notes' => 'The fee bill references e-filing fees, so e-filing exists; system name not read.',
        ],
        [
            'county' => 'Lehigh',
            'office' => 'Lehigh County Clerk of Judicial Records, Civil Division',
            'url' => 'https://www.lehighcounty.org/departments/clerk-of-judicial-records/civil-division',
            'erecording' => true,
            'erecording_vendors' => [
                'Odyssey File & Serve (Tyler)',
            ],
            'fee' => [
                'first_page_cents' => null,
                'additional_page_cents' => null,
                'flat_cents' => null,
                'summary' => null,
                'url' => 'https://www.lehighcounty.org/Portals/0/Civil%20Division%20--%20Fee%20Schedule%2012-2025.pdf?ver=oNi6I3_zpM-L4Lls4LJTPg%3D%3D',
            ],
            'mailing_address' => 'Clerk of Judicial Records, Civil Division, Lehigh County Courthouse, Room 122, 455 W. Hamilton Street, Allentown, PA 18101-1614',
            'notes' => 'The fee schedule\'s two-column layout did not extract cleanly, so the mechanics\' lien claim fee could not be read with confidence; left null.',
        ],
    ],
    'recent_changes' => [
        [
            'title' => 'HB 1319 (2025-26): rented equipment as lienable \'materials\' (pending, not enacted)',
            'effective' => null,
            'summary' => 'Would amend the definition of materials to include rented equipment reasonably necessary for the improvement. Passed the House 116-87 on July 7, 2025; referred to the Senate Judiciary Committee July 24, 2025, its last action. Not law.',
            'cite' => 'HB 1319, 2025-2026 Regular Session',
        ],
        [
            'title' => 'Rental equipment lien rights before the Supreme Court (Cleveland Brothers)',
            'effective' => null,
            'summary' => 'In November 2024 the Superior Court held that unpaid rental charges for equipment not incorporated into the improvement are not lienable \'materials.\' The Supreme Court granted allowance of appeal in April 2025; no decision was found. Case law, not a statute change.',
            'cite' => 'Cleveland Brothers Equipment Co. (Pa. Super. 2024), allocatur granted April 23, 2025',
        ],
    ],
    'faqs' => [
        [
            'q' => 'Can a supplier to a supplier file a mechanics\' lien in Pennsylvania?',
            'a' => 'No. Only contractors and subcontractors have lien rights. A subcontractor includes a supplier to the contractor and a second-tier party that contracts with a subcontractor who is in privity with the contractor. Anyone further down, including a supplier to a materialman, cannot claim a lien, even if its materials went into the job.',
            'cite' => '49 P.S. §§ 1201(5), 1303(a) (Act §§ 201, 303)',
        ],
        [
            'q' => 'Can I file a mechanics\' lien on a public project in Pennsylvania?',
            'a' => 'No. The act allows no lien for labor or materials furnished for a purely public purpose. On public work, unpaid subcontractors and suppliers usually look to the prime contractor\'s payment bond under the Public Works Contractors\' Bond Law of 1967 instead, which has its own notice and suit deadlines.',
            'cite' => '49 P.S. § 1303(b) (Act § 303); 8 P.S. § 191 et seq.',
        ],
        [
            'q' => 'What if the work was ordered by a tenant?',
            'a' => 'The lien reaches the tenant\'s leasehold interest. It does not reach the owner\'s fee interest just because the owner consented to the tenant\'s work, unless the owner signed a writing stating the work was for the owner\'s immediate use and benefit.',
            'cite' => '49 P.S. § 1303(d) (Act § 303)',
        ],
        [
            'q' => 'Is there a minimum amount for a Pennsylvania mechanics\' lien?',
            'a' => 'Yes. Every improvement is subject to a lien only if the amount of the claim is more than $500, not counting amounts set by apportionment among several buildings under section 306(b). A claim of $500 or less cannot be filed as a mechanics\' lien, so smaller debts must be collected through a contract suit or other means.',
            'cite' => '49 P.S. § 1301(a) (Act § 301)',
        ],
        [
            'q' => 'Can the owner contest a lien claim in Pennsylvania?',
            'a' => 'Yes. Any party may file preliminary objections showing the property is exempt from lien or the claim does not conform to the act, and the court decides them, taking evidence if facts are disputed. The owner can also have the claim discharged by depositing its amount with the court, or approved security, while the dispute continues.',
            'cite' => '49 P.S. §§ 1505, 1510 (Act §§ 505, 510)',
        ],
        [
            'q' => 'How is a paid Pennsylvania lien released?',
            'a' => 'After payment or other discharge, the claimant must enter satisfaction on the record, upon payment of the costs. If the claimant fails to do so within 30 days after a written request, the court may order the claim satisfied and impose a penalty on the claimant of up to the amount of the claim.',
            'cite' => '49 P.S. § 1704 (Act § 704)',
        ],
    ],
    'sources' => [
        [
            'title' => 'Mechanics\' Lien Law of 1963, Act 497 (49 P.S. § 1101 et seq.), Pennsylvania General Assembly',
            'url' => 'https://www.palegis.us/statutes/unconsolidated/law-information/view-statute?txtType=HTM&SessYr=1963&ActNum=0497.&SessInd=0',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'HB 1319 (2025-2026) bill information',
            'url' => 'https://www.palegis.us/legislation/bills/2025/hb1319',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Clark Hill, Proposed legislation and appeal on rental equipment under the PA Lien Law',
            'url' => 'https://www.clarkhill.com/news-events/news/proposed-legislation-and-appeal-on-potential-collision-course-to-provide-clarity-to-pa-lien-laws-inclusion-of-rental-equipment/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'First Judicial District, Office of Judicial Records Fee Schedule',
            'url' => 'https://www.courts.phila.gov/pdf/prothyfees.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Allegheny County Department of Court Records, New Case Fees',
            'url' => 'https://www.alleghenycounty.us/Government/Court-Related/Civil-and-Family-Court-Records/Civil-and-Family-Court-Record-Fees/New-Case-Fees',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Montgomery County Prothonotary, Fee Schedule',
            'url' => 'https://www.montgomerycountypa.gov/departments/prothonotary/fee-schedule',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Bucks County Prothonotary Civil Fee Bill (effective January 1, 2026)',
            'url' => 'https://www.buckscounty.gov/DocumentCenter/View/1283/2023-Prothonotary-Civil-Fee-Bill-PDF',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Delaware County Office of Judicial Support, Civil Filing Fee Schedule',
            'url' => 'https://www.delcopa.gov/sites/default/files/2026-02/civilfees.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Lancaster County Prothonotary Fee Bill',
            'url' => 'https://co.lancaster.pa.us/DocumentCenter/View/18264/2026-Prothonotary-Fee-Bill?bidId=',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Chester County Prothonotary Fee Schedule',
            'url' => 'https://chesco.org/DocumentCenter/View/73957/Prothonotary-Fees?bidId=',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'York County Prothonotary Fee Schedule (January 1, 2026)',
            'url' => 'https://yorkcountypa.gov/DocumentCenter/View/7978/FEE-SCHEDULE-JANUARY-1-2026',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Berks County Prothonotary Fee Bill (effective September 28, 2026)',
            'url' => 'https://www.berkspa.gov/getmedia/d70498fe-b117-45a5-b8f5-a0f613af0338/Fee-Bill-effective-9-28-2026-Paymentus-Third-party-credit-debit-card-processor.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Lehigh County Clerk of Judicial Records, Civil Division',
            'url' => 'https://www.lehighcounty.org/departments/clerk-of-judicial-records/civil-division',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Levelset, Pennsylvania Mechanics Lien Guide & FAQs (secondary cross-check)',
            'url' => 'https://www.levelset.com/mechanics-lien/pennsylvania-lien-law-faqs/',
            'accessed' => '2026-10-02',
        ],
    ],
];
