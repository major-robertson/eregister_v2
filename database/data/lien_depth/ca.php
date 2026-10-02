<?php

/*
 * CA: mechanics lien depth for the /liens/{state} page: how to file, what
 * the claim must contain, the recording offices of the ten most populous
 * counties, recent law changes and six more FAQs. Researched
 * 2026-10-02 (EREG-13) from the statute and the offices' own sites.
 * A null fee, address or e-recording value means the office does not publish
 * it: never fill one in with a guess. County 'notes' record where a fact came
 * from and are never rendered. Generated once from the research JSON; edit
 * this file directly from now on.
 */

return [
    'state' => 'CA',
    'researched_on' => '2026-10-02',
    'filing_verb' => 'record',
    'county_intro' => 'California claims of mechanics lien are recorded with the county recorder. E-recording runs through the state-certified systems SECURE and CERTNA and is open only to authorized submitters. On top of the page fees, most counties add state charges such as the $75 SB 2 fee, a real estate fraud fee and a lien notice fee. Los Angeles County puts a one-page mechanics lien at $106 in total.',
    'how_to_file' => [
        [
            'step' => 1,
            'title' => 'Serve the 20-day preliminary notice',
            'text' => 'Before you can record a lien, give a preliminary notice to the owner, the direct contractor and the construction lender, if any. Serve it no later than 20 days after you first furnish work. A late notice still works, but only for work furnished in the 20 days before it was served and afterward. Use the statutory form wording, including the boldface NOTICE TO PROPERTY OWNER, and serve it by personal delivery or by registered, certified or express mail or overnight delivery. Laborers are exempt, and a contractor that contracted directly with the owner only has to notify the construction lender.',
            'cite' => 'Cal. Civ. Code §§ 8200, 8202, 8204, 8106, 8110',
            'residential_note' => null,
        ],
        [
            'step' => 2,
            'title' => 'Watch for a notice of completion or cessation',
            'text' => 'Your recording deadline runs from completion of the whole work of improvement, but a recorded notice of completion or cessation shortens it: to 60 days after the notice for a direct contractor, and to 30 days after the notice for everyone else. Check the county records before you count on the full 90 days.',
            'cite' => 'Cal. Civ. Code §§ 8412, 8414',
            'residential_note' => null,
        ],
        [
            'step' => 3,
            'title' => 'Prepare the claim of mechanics lien',
            'text' => 'Write a claim of mechanics lien with the statutory contents: your demand after credits and offsets, the owner\'s name if known, a general description of the work, who hired you, a description of the site, your address, a proof of service affidavit, and the statutory NOTICE OF MECHANICS LIEN text addressed to the owner.',
            'cite' => 'Cal. Civ. Code § 8416(a)',
            'residential_note' => null,
        ],
        [
            'step' => 4,
            'title' => 'Sign and verify the claim',
            'text' => 'The claimant signs and verifies the claim (a statement under penalty of perjury that its contents are true). A properly verified claim is accepted for recording without a notary acknowledgment.',
            'cite' => 'Cal. Civ. Code § 8416(a), (b)',
            'residential_note' => null,
        ],
        [
            'step' => 5,
            'title' => 'Serve a copy on the owner',
            'text' => 'Mail a copy of the claim and the statutory notice to the owner or reputed owner by registered, certified or first-class mail, to the owner\'s residence or place of business or the address on the building permit. If the owner cannot be found at those addresses, mail it to the construction lender or the direct contractor. Service is complete when mailed. The person who mails it signs the proof of service affidavit that goes in the claim. Without this service the lien is unenforceable as a matter of law.',
            'cite' => 'Cal. Civ. Code § 8416(a)(7), (c), (d), (e)',
            'residential_note' => null,
        ],
        [
            'step' => 6,
            'title' => 'Record the claim with the county recorder',
            'text' => 'Record the claim in the office of the county recorder of the county where the work of improvement, or part of it, is located. A direct contractor records after completing its contract and before the earlier of 90 days after completion of the work of improvement or 60 days after a recorded notice of completion or cessation. Everyone else records after they stop furnishing work and before the earlier of 90 days after completion or 30 days after such a notice.',
            'cite' => 'Cal. Civ. Code §§ 8060(b), 8412, 8414',
            'residential_note' => null,
        ],
        [
            'step' => 7,
            'title' => 'File suit to enforce within 90 days',
            'text' => 'Commence an action to enforce the lien within 90 days after recording, or the lien expires and is unenforceable. If you and the owner agree to extend credit and record notice of that within the 90 days, the deadline becomes 90 days after the credit expires, but never later than one year after completion.',
            'cite' => 'Cal. Civ. Code § 8460',
            'residential_note' => null,
        ],
        [
            'step' => 8,
            'title' => 'Release the lien or expect a release petition',
            'text' => 'If you do not sue within the 90 days, the owner may demand that you record a release and, after at least 10 days\' notice, petition the court for a release order. The prevailing party on that petition recovers reasonable attorney fees.',
            'cite' => 'Cal. Civ. Code §§ 8480, 8482, 8488(c)',
            'residential_note' => null,
        ],
    ],
    'claim_contents' => [
        'document_name' => 'Claim of mechanics lien',
        'required' => [
            'A statement of the claimant\'s demand after deducting all just credits and offsets',
            'The name of the owner or reputed owner, if known',
            'A general statement of the kind of work furnished by the claimant',
            'The name of the person by whom the claimant was employed or to whom the claimant furnished work',
            'A description of the site sufficient for identification',
            'The claimant\'s address',
            'A proof of service affidavit completed and signed by the person serving a copy of the claim on the owner',
            'The statutory \'NOTICE OF MECHANICS LIEN / ATTENTION!\' text warning the owner that the property may be subject to a foreclosure action filed within 90 days',
            'Signature and verification by the claimant',
        ],
        'cite' => 'Cal. Civ. Code § 8416(a), (b)',
        'official_form_url' => null,
        'notes' => 'The statute prescribes the wording of the owner notice that must be included in the claim, but no official state form for the whole claim was found. A properly verified claim is recorded without acknowledgment (§ 8416(b)). Willfully including work not furnished forfeits the lien (§ 8422).',
    ],
    'counties' => [
        [
            'county' => 'Los Angeles',
            'office' => 'Los Angeles County Registrar-Recorder/County Clerk',
            'url' => 'https://www.lavote.gov/home/recorder/property-document-recording/general-info',
            'erecording' => true,
            'erecording_vendors' => [
                'SECURE (multi-county ERDS; authorized submitters only)',
            ],
            'fee' => [
                'first_page_cents' => 1500,
                'additional_page_cents' => 300,
                'flat_cents' => null,
                'summary' => 'Base fee $15 first page, $3 each additional page. A mechanic\'s lien also pays a $9 involuntary lien notice fee ($2 for each additional notice), a $5 district attorney fraud fee, the $75 SB 2 fee and a $2 AB 1466 fee. The county\'s own example: a one-page mechanic\'s lien costs $106 in total.',
                'url' => 'https://www.lavote.gov/docs/rrcc/documents/fees.pdf?v=3',
            ],
            'mailing_address' => 'Los Angeles County Registrar-Recorder/County Clerk, 12400 Imperial Highway, Norwalk, CA 90650',
            'notes' => 'Fee bulletin is dated Rev 4/1/2022. Mailing address from the office\'s contact page. LA County is a sub-county of the SECURE electronic recording system certified by the Attorney General (Gov. Code § 27390 et seq.); e-recording is open only to authorized submitters.',
        ],
        [
            'county' => 'San Diego',
            'office' => 'San Diego County Assessor/Recorder/County Clerk, Recording',
            'url' => 'https://www.sdarcc.gov/content/arcc/home/divisions/recorder-clerk/recording.html',
            'erecording' => true,
            'erecording_vendors' => [
                'SECURE (multi-county ERDS; authorized submitters only)',
            ],
            'fee' => [
                'first_page_cents' => null,
                'additional_page_cents' => null,
                'flat_cents' => null,
                'summary' => null,
                'url' => 'https://www.sdarcc.gov/content/dam/arcc/recorder-county-clerk/forms/Recorder%20County%20Clerk%20Fee%20Schedule.pdf',
            ],
            'mailing_address' => 'San Diego Recorder/County Clerk, P.O. Box 121750, San Diego, CA 92112-1750 (overnight: 590 3rd Avenue, Suite 204, Chula Vista, CA 91910)',
            'notes' => 'The county\'s fee schedule PDF and Recording page timed out on every fetch; the mailing address and the involuntary lien notice fee ($13 first notice, $6 each additional, per the fee schedule effective July 1, 2025) come from search-result excerpts of those official pages and should be rechecked. San Diego joined SECURE in 2015 per the Attorney General\'s list.',
        ],
        [
            'county' => 'Orange',
            'office' => 'Orange County Clerk-Recorder',
            'url' => 'https://ocrecorder.com/recorder-services/property-documentsdocument-recording-services',
            'erecording' => true,
            'erecording_vendors' => [
                'SECURE (lead county; authorized submitters only)',
            ],
            'fee' => [
                'first_page_cents' => 1200,
                'additional_page_cents' => 300,
                'flat_cents' => null,
                'summary' => '$12 standard first page per title, $3 each additional page, plus a $10 district attorney fraud fee on certain documents and the $75 SB 2 fee per title unless exempt. The county\'s involuntary lien notification fee is marked \'not applicable to Mechanic Liens\'. Fee schedule effective 01/01/2026.',
                'url' => 'https://ocrecorder.com/sites/ocrecorder/files/2026-01/FeeSchedule.pdf',
            ],
            'mailing_address' => 'Orange County Clerk-Recorder Department, County Administration South, 601 N. Ross Street, Santa Ana, CA 92701',
            'notes' => 'Orange County leads SECURE (with Los Angeles, Riverside and San Diego since 2008); about 80% of its documents are e-recorded (county release, Aug. 20, 2025).',
        ],
        [
            'county' => 'Riverside',
            'office' => 'Riverside County Assessor-County Clerk-Recorder',
            'url' => 'https://www.rivcoacr.org/DocumentRecordingServices',
            'erecording' => true,
            'erecording_vendors' => [
                'SECURE (multi-county ERDS; authorized submitters only)',
            ],
            'fee' => [
                'first_page_cents' => 1400,
                'additional_page_cents' => 300,
                'flat_cents' => null,
                'summary' => '$14 first page, $3 each additional page. Mechanic\'s liens are listed for the $10 Real Estate Fraud Prosecution fee; add the $75 SB 2 fee per title, the $2 AB 1466 fee and $13 involuntary lien notification per debtor address (fee schedule ACR 156, Rev. 01/2026).',
                'url' => 'https://evogov.s3.us-west-2.amazonaws.com/151/media/425304.pdf',
            ],
            'mailing_address' => 'Riverside County Recorder, P.O. Box 751, Riverside, CA 92502-0751 (overnight: 2724 Gateway Drive, Riverside, CA 92507)',
            'notes' => null,
        ],
        [
            'county' => 'San Bernardino',
            'office' => 'San Bernardino County Assessor-Recorder-Clerk, Document Recording',
            'url' => 'https://arc.sbcounty.gov/document-recording/',
            'erecording' => true,
            'erecording_vendors' => [
                'SECURE (multi-county ERDS; authorized submitters only)',
            ],
            'fee' => [
                'first_page_cents' => 1400,
                'additional_page_cents' => 300,
                'flat_cents' => null,
                'summary' => '$14 first page of title ($24 with the real estate fraud fee), $3 each additional page; 3.5% surcharge on card payments.',
                'url' => 'https://arc.sbcounty.gov/document-recording/',
            ],
            'mailing_address' => 'San Bernardino County Recorder, 222 W. Hospitality Lane, 1st Floor, San Bernardino, CA 92415-0022',
            'notes' => 'The county page does not describe e-recording; San Bernardino is listed as a SECURE sub-county certified 5/29/2020 on the Attorney General\'s ERDS list. The page did not mention the SB 2 or involuntary lien notice fees.',
        ],
        [
            'county' => 'Santa Clara',
            'office' => 'Santa Clara County Clerk-Recorder\'s Office',
            'url' => 'https://clerkrecorder.santaclaracounty.gov/recording-documents/recording-document-fees',
            'erecording' => true,
            'erecording_vendors' => [
                'SECURE (multi-county ERDS; authorized submitters only)',
            ],
            'fee' => [
                'first_page_cents' => 10200,
                'additional_page_cents' => 300,
                'flat_cents' => null,
                'summary' => '$102 for the first page of a standard document, which includes the $10 real estate fraud fee and the $75 SB 2 fee, $3 each additional page, plus a $4 lien notice fee for each named lienee.',
                'url' => 'https://clerkrecorder.santaclaracounty.gov/recording-documents/recording-document-fees',
            ],
            'mailing_address' => 'Clerk-Recorder\'s Office, ATTN: Recording, 110 West Tasman Drive, San Jose, CA 95134',
            'notes' => 'The county site returned 403 to direct fetches; the fee and mailing address come from search-result excerpts of the official fee and recording pages and should be rechecked. Santa Clara is a SECURE sub-county certified 1/28/2021 on the Attorney General\'s ERDS list.',
        ],
        [
            'county' => 'Alameda',
            'office' => 'Alameda County Clerk-Recorder\'s Office (Auditor-Controller/Clerk-Recorder)',
            'url' => 'http://www.acgov.org/auditor/clerk/',
            'erecording' => true,
            'erecording_vendors' => [
                'CERTNA (multi-county ERDS; authorized submitters only)',
            ],
            'fee' => [
                'first_page_cents' => 8900,
                'additional_page_cents' => 300,
                'flat_cents' => null,
                'summary' => '$89 first page per title (includes the SB 2 fee), $3 each additional page; mechanic\'s liens also pay the $10 real estate fraud prosecution fee and $7 involuntary lien notification per debtor (fee schedule effective January 1, 2024).',
                'url' => 'https://www.acgov.org/forms/auditor/ADMN-Clerk-Recorder-Fee-Schedule-rev-01-01-2024.pdf',
            ],
            'mailing_address' => 'Alameda County Clerk-Recorder\'s Office, 1106 Madison Street, 1st Floor, Oakland, CA 94607',
            'notes' => 'Address is the office address on the fee schedule. Alameda is a CERTNA sub-county certified 11/14/2019 on the Attorney General\'s ERDS list.',
        ],
        [
            'county' => 'Sacramento',
            'office' => 'Sacramento County Clerk/Recorder',
            'url' => 'https://ccr.saccounty.gov/',
            'erecording' => true,
            'erecording_vendors' => [
                'SECURE (multi-county ERDS; authorized submitters only)',
            ],
            'fee' => [
                'first_page_cents' => 2000,
                'additional_page_cents' => 300,
                'flat_cents' => null,
                'summary' => '$20 first page/title, $3 each additional page; documents subject to the SB 2 fee are $95 for the first page. Mechanic\'s liens pay a $13 involuntary lien notification fee per owner.',
                'url' => 'https://ccr.saccounty.gov/content/ccr/us/en/fees.html',
            ],
            'mailing_address' => 'Sacramento County Clerk/Recorder, 3636 American River Drive, Suite 110, Sacramento, CA 95864',
            'notes' => 'The office says documents received before 3:00 p.m. \'in-person or electronically\' are recorded the same day.',
        ],
        [
            'county' => 'Contra Costa',
            'office' => 'Contra Costa County Clerk-Recorder, Recorder Division',
            'url' => 'https://www.contracostavote.gov/recorder/recording-fees/',
            'erecording' => true,
            'erecording_vendors' => [
                'SECURE (multi-county ERDS; authorized submitters only)',
            ],
            'fee' => [
                'first_page_cents' => null,
                'additional_page_cents' => null,
                'flat_cents' => null,
                'summary' => null,
                'url' => 'https://www.contracostavote.gov/wp-content/uploads/Recorder-Fee-Schedule-2026-UPDATED.pdf',
            ],
            'mailing_address' => null,
            'notes' => 'The county fee pages and 2026 fee schedule PDF returned no readable content. Search excerpts of that PDF show a $14 base fee subject to the SB 2 fee and a $9 involuntary lien notice per owner/debtor, and give 555 Escobar Street, Martinez, CA 94553 as the address; not verified directly. Contra Costa is a SECURE sub-county certified 1/28/2021.',
        ],
        [
            'county' => 'Fresno',
            'office' => 'Fresno County Assessor-Recorder, Recorder',
            'url' => 'https://www.fresnocountyca.gov/Departments/Recorder',
            'erecording' => true,
            'erecording_vendors' => [
                'SECURE (multi-county ERDS; authorized submitters only)',
            ],
            'fee' => [
                'first_page_cents' => 1900,
                'additional_page_cents' => 300,
                'flat_cents' => null,
                'summary' => '$19 first page, $3 each additional page, plus the $75 SB 2 fee per document and $6 involuntary lien notice per debtor name and address.',
                'url' => 'https://www.fresnocountyca.gov/Departments/Recorder/Fee-Schedule',
            ],
            'mailing_address' => null,
            'notes' => 'Office: 1250 Van Ness Ave, Fresno, CA 93721. The site gives that address for mailed copy requests; a recording-by-mail address was not stated. Fresno is a SECURE sub-county certified 1/28/2021.',
        ],
    ],
    'recent_changes' => [
        [
            'title' => 'AB 1466: restrictive covenant modification fee on recordings',
            'effective' => '2022-04-01',
            'summary' => 'Adds a $2 fee per title to most recorded documents, including mechanic\'s liens, unless exempt. Counties began charging on different dates (Los Angeles April 1, 2022; Riverside July 14, 2023).',
            'cite' => 'Gov. Code § 27388.2; Los Angeles and Riverside county fee schedules',
        ],
        [
            'title' => 'SB 61: 5% cap on retention in private works',
            'effective' => '2026-01-01',
            'summary' => 'For private-works contracts entered into on or after January 1, 2026, retention withheld at any tier is capped at 5% of each payment and of the contract price, with exceptions including residential projects under four stories. Affects how much retention a subcontractor may later need to lien for.',
            'cite' => 'Cal. Civ. Code § 8811 (added by SB 61, Stats. 2025)',
        ],
        [
            'title' => 'SB 440: private works change order and claim procedure',
            'effective' => '2026-01-01',
            'summary' => 'For most private projects other than residential under four stories, owners must review contractor claims within 30 days and pay undisputed amounts within 60 days; unpaid undisputed amounts accrue interest at 2% per month, and contractors gain a right to stop work after notice.',
            'cite' => 'Cal. Civ. Code § 8850 (added by SB 440, Stats. 2025)',
        ],
    ],
    'faqs' => [
        [
            'q' => 'Can I file a mechanics lien on a California public project?',
            'a' => 'No. The private-works lien chapter does not apply to public works. Instead, serve a stop payment notice on the public entity, or claim against the contractor\'s payment bond. The stop payment notice must be given within 30 days after a notice of completion, acceptance or cessation is recorded, or 90 days after completion if none is recorded. A suit on the payment bond is due within six months after that period ends.',
            'cite' => 'Cal. Civ. Code §§ 8160, 9356, 9558',
        ],
        [
            'q' => 'What if the work was ordered by a tenant, not the owner?',
            'a' => 'Work is authorized if the owner requested or agreed to it, or if a contractor or other person in charge of the work authorized it. An owner who learns of a tenant\'s project can protect its interest by posting a notice of nonresponsibility on the site and recording it within 10 days after learning of the work. If the owner does that, your lien generally reaches only the tenant\'s interest.',
            'cite' => 'Cal. Civ. Code §§ 8404, 8444',
        ],
        [
            'q' => 'What happens after I record a California mechanics lien?',
            'a' => 'The copy you mailed to the owner warns that a foreclosure suit may follow within 90 days. You must commence that suit within 90 days after recording, or the lien expires. A recorded extension of credit can push the date out, but never past one year after completion. The owner or a contractor can also record a lien release bond for 125% of the claim, which frees the property and moves your claim to the bond.',
            'cite' => 'Cal. Civ. Code §§ 8416, 8424, 8460',
        ],
        [
            'q' => 'Can the owner get my lien removed?',
            'a' => 'Yes. If you have not sued within 90 days, the owner can demand that you record a release and, after giving at least 10 days\' notice, petition the court for a release order. The prevailing party recovers reasonable attorney fees. An owner who disputes the lien can also record a lien release bond for 125% of the claimed amount at any time.',
            'cite' => 'Cal. Civ. Code §§ 8424, 8480, 8482, 8488',
        ],
        [
            'q' => 'What does it cost to record a claim of lien in California?',
            'a' => 'County recorders charge the fees set in the Government Code: a base fee for the first page (about $12 to $20 in the large counties) plus $3 for each additional page. Most also add the $75 SB 2 fee, a $5 to $10 real estate fraud fee, a $2 AB 1466 fee and an involuntary lien notice fee. Los Angeles County\'s own example puts a one-page mechanic\'s lien at $106.',
            'cite' => 'Cal. Civ. Code § 8060(d); Gov. Code §§ 27361, 27387, 27388, 27388.1, 27388.2',
        ],
        [
            'q' => 'What if I missed the recording deadline?',
            'a' => 'A claimant may not enforce a lien unless the claim is recorded within the statutory period, so a late claim gives you no lien on the property. You can still sue on your contract for payment. If a construction lender holds the funds, a timely stop payment notice may be another option, but it also requires the preliminary notice and has its own deadlines.',
            'cite' => 'Cal. Civ. Code §§ 8412, 8414, 8200',
        ],
    ],
    'sources' => [
        [
            'title' => 'Cal. Civ. Code § 8416 (FindLaw)',
            'url' => 'https://codes.findlaw.com/ca/civil-code/civ-sect-8416/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Cal. Civ. Code § 8200 (FindLaw)',
            'url' => 'https://codes.findlaw.com/ca/civil-code/civ-sect-8200/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Cal. Civ. Code § 8202 (FindLaw)',
            'url' => 'https://codes.findlaw.com/ca/civil-code/civ-sect-8202/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Cal. Civ. Code § 8204 (FindLaw)',
            'url' => 'https://codes.findlaw.com/ca/civil-code/civ-sect-8204/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Cal. Civ. Code § 8060 (FindLaw)',
            'url' => 'https://codes.findlaw.com/ca/civil-code/civ-sect-8060/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Cal. Civ. Code §§ 8106, 8110 (FindLaw)',
            'url' => 'https://codes.findlaw.com/ca/civil-code/civ-sect-8106/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Cal. Civ. Code §§ 8400, 8404, 8414, 8424, 8444 (FindLaw)',
            'url' => 'https://codes.findlaw.com/ca/civil-code/civ-sect-8424/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Cal. Civ. Code §§ 8480, 8482 (FindLaw)',
            'url' => 'https://codes.findlaw.com/ca/civil-code/civ-sect-8480/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Cal. Civ. Code §§ 8160, 9356, 9558 (FindLaw)',
            'url' => 'https://codes.findlaw.com/ca/civil-code/civ-sect-9356/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'California Attorney General, ERDS certified systems (Rev. 06/12/2025)',
            'url' => 'https://oag.ca.gov/system/files/media/certified-systems-6-12-25.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Levelset, California Mechanics Lien Guide & FAQs',
            'url' => 'https://www.levelset.com/mechanics-lien/california-lien-law-faqs/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Jones Day, California Mandates Important New Rules for Private Construction Projects (Nov. 2025)',
            'url' => 'https://www.jonesday.com/en/insights/2025/11/california-mandates-important-new-rules-for-private-construction-projects',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Smith Currie, New California Construction Laws for 2026',
            'url' => 'https://www.smithcurrie.com/publications/common-sense-contract-law/new-california-construction-laws-for-2026/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'LA County Registrar-Recorder, Recording Fee Bulletin',
            'url' => 'https://www.lavote.gov/docs/rrcc/documents/fees.pdf?v=3',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'LA County Registrar-Recorder, Contact Us',
            'url' => 'https://www.lavote.gov/contact-us/contact-us',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Orange County Clerk-Recorder, Fee Schedule (eff. 01/01/2026)',
            'url' => 'https://ocrecorder.com/sites/ocrecorder/files/2026-01/FeeSchedule.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Orange County Clerk-Recorder, 11 million e-recorded documents release',
            'url' => 'https://www.ocrecorder.com/news/orange-county-clerk-recorder-surpasses-11-million-electronically-submitted-documents',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Riverside County ACR, Document Recording Services',
            'url' => 'https://www.rivcoacr.org/DocumentRecordingServices',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Riverside County ACR, Fee Schedule ACR 156 (Rev. 01/2026)',
            'url' => 'https://evogov.s3.us-west-2.amazonaws.com/151/media/425304.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'San Bernardino County ARC, Document Recording',
            'url' => 'https://arc.sbcounty.gov/document-recording/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Santa Clara County Clerk-Recorder, Recording document fees',
            'url' => 'https://clerkrecorder.santaclaracounty.gov/recording-documents/recording-document-fees',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Alameda County Clerk-Recorder Fee Schedule (eff. Jan. 1, 2024)',
            'url' => 'https://www.acgov.org/forms/auditor/ADMN-Clerk-Recorder-Fee-Schedule-rev-01-01-2024.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Sacramento County Clerk/Recorder, home and fee schedule',
            'url' => 'https://ccr.saccounty.gov/content/ccr/us/en/fees.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Fresno County Recorder, Fee Schedule',
            'url' => 'https://www.fresnocountyca.gov/Departments/Recorder/Fee-Schedule',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'San Diego County ARCC, Recording',
            'url' => 'https://www.sdarcc.gov/content/arcc/home/divisions/recorder-clerk/recording.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Contra Costa County Recorder Division Fee Schedule 2026',
            'url' => 'https://www.contracostavote.gov/wp-content/uploads/Recorder-Fee-Schedule-2026-UPDATED.pdf',
            'accessed' => '2026-10-02',
        ],
    ],
];
