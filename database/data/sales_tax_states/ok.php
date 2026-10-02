<?php

/*
 * Oklahoma: Oklahoma Tax Commission, Sales Tax Permit. Researched
 * 2026-10-02 from oklahoma.gov, law.justia.com, oklegislature.gov,
 * salestaxinstitute.com, avalara.com. Generated once from the EREG-13
 * sales tax research; edit this file directly from now on.
 */

return [
    'state' => 'OK',
    'name' => 'Oklahoma',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Oklahoma Tax Commission',
        'short' => 'the OTC',
        'url' => 'https://oklahoma.gov/tax.html',
    ],
    'registration' => [
        'term' => 'Sales Tax Permit',
        'portal' => [
            'name' => 'OkTAP (Oklahoma Taxpayer Access Point) Online Business Registration',
            'url' => 'https://oktap.tax.ok.gov/OkTAP/Web/_/',
        ],
        'form' => [
            'number' => null,
            'title' => 'Oklahoma Business Registration (electronic application; instructions published as Packet A)',
            'pdf_url' => 'https://oklahoma.gov/content/dam/ok/en/tax/documents/forms/businesses/general/Packet-A.pdf',
            'online_only' => true,
        ],
        'fee' => [
            'amount_cents' => 2000,
            'summary' => '$20 for the permit, plus $10 for each additional place of business; each permit runs three years. A bond may be required, and the application is not processed until the correct fee is paid.',
            'cite' => '68 O.S. 1364; Oklahoma Business Registration Instructions (Packet A), Licenses, Bonds and Surety Information',
        ],
        'renewal' => [
            'required' => true,
            'summary' => 'Permits are issued for three years and must be secured again every three years for the $20 fee. A new permit is probationary for its first six months and then automatically extends 30 more months unless the OTC refuses.',
            'cite' => '68 O.S. 1364(A) and (B); Packet A',
        ],
        'timing' => [
            'online' => '5 to 10 business days',
            'paper' => null,
            'temporary_number' => false,
            'summary' => 'The OTC tells applicants to allow 5 to 10 business days for processing after completing the electronic business application in OkTAP; if approved, an OkTAP account number and the applicable permits are issued.',
            'cite' => 'OTC Help Center, Businesses (permit registration FAQ)',
        ],
        'number' => [
            'name' => 'Sales Account ID and site permit number',
            'format' => 'Sales Account ID like STS-15740582-04 (used for filing; shared by all locations) and a separate 9-digit site permit number for each location',
            'cite' => 'OTC Publication D (Revised December 2025), sample permit',
        ],
    ],
    'nexus' => [
        'physical' => 'Anyone selling tangible personal property in Oklahoma on an ongoing basis, keeping inventory in the state, leasing goods there, or selling or installing equipment in Oklahoma needs a permit for each place of business; occasional sellers do not.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => null,
            'period' => 'current or preceding calendar year (aggregate sales of tangible personal property delivered into Oklahoma)',
            'effective' => '2019-11-01',
            'cite' => '68 O.S. 1392; SB 513 (2019)',
        ],
        'marketplace' => 'Marketplace facilitators with $10,000 or more in Oklahoma sales in the preceding 12 months must register and either collect and remit tax (including OTC-administered local taxes since January 1, 2023) or meet notice and reporting rules (68 O.S. 1392; SB 418 and SB 1339, 2022).',
    ],
    'filing' => [
        'frequencies' => 'monthly; semiannual for small accounts',
        'rule' => 'Most permit holders file monthly. Semiannual filing is allowed when tax does not exceed $50 a month. Holders averaging $2,500 or more a month must use electronic funds transfer; new registrants generally must file and pay electronically.',
        'due_day' => '20th of the month after the period (semiannual returns due July 20 and January 20)',
        'zero_return_required' => true,
        'prepayments' => null,
        'cite' => 'OTC Help Center, Businesses (filing FAQ); Form STS-20002-A instructions; Packet A (OAC 710:65-3-1)',
    ],
    'rates' => [
        'state_rate_pct' => 4.5,
        'local' => 'Cities and counties add their own sales taxes, administered by the OTC; combined rates vary widely by location.',
        'sourcing' => null,
        'cite' => 'OTC, State Sales Tax on Food and Food Ingredients (4.5% state rate); Form STS-20002-A instructions (state, city and county tax reported on one return)',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'City and county sales taxes are administered by the OTC and reported on the state return. No separate local sales tax registration.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Engaging in business without a permit, or after it is suspended, is a misdemeanor punishable by a fine of up to $1,000. Repeated failure to file or pay three times in 24 months can lead to business closure.',
            'cite' => '68 O.S. 1364; Packet A (SB 1984 and HB 2343 notices)',
        ],
        'late_filing' => [
            'summary' => 'If the return and payment are not postmarked within 15 days after the due date, a 10% penalty is added, plus interest.',
            'cite' => '68 O.S. 217; Form STS-20002-A instructions, Line 9',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales tax',
            'use tax',
            'withholding tax',
            'other OTC business taxes and licenses on the same registration',
        ],
        'prerequisites' => 'An FEIN is required before registering. LLCs, corporations and limited partnerships register with the Oklahoma Secretary of State.',
        'cite' => 'Packet A, Do You Need to Apply for an FEIN? and Items 2 and 3',
    ],
    'facts' => [
        [
            'text' => 'A new Oklahoma sales tax permit is probationary for six months; it is not extended if the application had factual errors, the owners are delinquent on taxes, or the business was bought from a seller with a tax liability.',
            'source_url' => 'https://oklahoma.gov/content/dam/ok/en/tax/documents/forms/businesses/general/Publication-D.pdf',
        ],
        [
            'text' => 'Vendors delinquent in filing or paying business taxes three times in a 24-month period are subject to business closure (HB 2343, effective July 1, 2017).',
            'source_url' => 'https://oklahoma.gov/content/dam/ok/en/tax/documents/forms/businesses/general/Packet-A.pdf',
        ],
        [
            'text' => 'Possessing or using sales suppression software (a zapper) carries a $10,000 administrative fine and immediate revocation of the sales tax permit.',
            'source_url' => 'https://oklahoma.gov/content/dam/ok/en/tax/documents/forms/businesses/general/Packet-A.pdf',
        ],
        [
            'text' => 'Occasional (casual) sellers do not need a permit; they file a report only when sales are made.',
            'source_url' => 'https://oklahoma.gov/tax/helpcenter/businesses.html',
        ],
        [
            'text' => 'Since August 29, 2024, food and food ingredients are exempt from the 4.5% state sales tax (HB 1955), but local sales taxes still apply to them.',
            'source_url' => 'https://oklahoma.gov/tax/businesses/state-sales-tax-on-food-and-food-ingredients.html',
        ],
    ],
    'state_notes' => 'In Oklahoma you need a Sales Tax Permit from the Oklahoma Tax Commission (OTC). You apply online through OkTAP\'s business registration. The OTC\'s instructions are published as Packet A. Get your FEIN first. The permit costs $20, plus $10 for each extra location, and the OTC may require a bond. The OTC says to allow 5 to 10 business days for processing. A new permit is probationary for six months, then runs for 30 more months. You must renew every three years. Remote sellers must register once they have $100,000 in Oklahoma sales in the current or prior calendar year. There is no transaction count. After you register, most sellers file monthly, due on the 20th of the following month. If your tax is $50 a month or less, you may file twice a year. File a return every period, even with no sales. If you pay more than 15 days late, a 10% penalty is added. The one mistake to avoid: missing returns. Three late filings or payments in 24 months can lead the OTC to close your business. Selling without a permit is a misdemeanor with a fine of up to $1,000.',
    'sources' => [
        [
            'title' => 'Oklahoma Business Registration Instructions (Packet A)',
            'url' => 'https://oklahoma.gov/content/dam/ok/en/tax/documents/forms/businesses/general/Packet-A.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'OTC Publication D, Oklahoma Sales Tax Vendor Responsibilities (Revised December 2025)',
            'url' => 'https://oklahoma.gov/content/dam/ok/en/tax/documents/forms/businesses/general/Publication-D.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'OTC Help Center, Businesses',
            'url' => 'https://oklahoma.gov/tax/helpcenter/businesses.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Form STS-20002-A, Oklahoma Sales Tax Return and instructions',
            'url' => 'https://oklahoma.gov/content/dam/ok/en/tax/documents/forms/businesses/general/STS-20002-A.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'OTC, State Sales Tax on Food and Food Ingredients',
            'url' => 'https://oklahoma.gov/tax/businesses/state-sales-tax-on-food-and-food-ingredients.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => '68 O.S. 1364, Permits to do business',
            'url' => 'https://law.justia.com/codes/oklahoma/title-68/section-68-1364/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => '68 O.S. 217, Interest and penalties on delinquent taxes',
            'url' => 'https://law.justia.com/codes/oklahoma/title-68/section-68-217/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'SB 513 (2019), engrossed',
            'url' => 'https://www.oklegislature.gov/cf_pdf/2019-20%20ENGR/SB/SB513%20ENGR.PDF',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Sales Tax Institute: Oklahoma enacts changes to economic nexus provisions (cross-check)',
            'url' => 'https://www.salestaxinstitute.com/resources/oklahoma-enacts-changes-to-economic-nexus-provisions',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Avalara: Marketplace facilitators face new obligations in Oklahoma (cross-check)',
            'url' => 'https://www.avalara.com/blog/en/north-america/2022/06/marketplace-facilitators-face-new-tax-and-reporting-obligations-in-oklahoma.html',
            'accessed' => '2026-10-02',
        ],
    ],
];
