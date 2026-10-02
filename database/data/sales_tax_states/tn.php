<?php

/*
 * Tennessee: Tennessee Department of Revenue, Sales and Use Tax
 * Certificate of Registration. Researched 2026-10-02 from tn.gov,
 * revenue.support.tn.gov, tntap.tn.gov. Generated once from the EREG-13
 * sales tax research; edit this file directly from now on.
 */

return [
    'state' => 'TN',
    'name' => 'Tennessee',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Tennessee Department of Revenue',
        'short' => 'the Department of Revenue',
        'url' => 'https://www.tn.gov/revenue.html',
    ],
    'registration' => [
        'term' => 'Sales and Use Tax Certificate of Registration',
        'portal' => [
            'name' => 'Tennessee Taxpayer Access Point (TNTAP), "Register a New Business"',
            'url' => 'https://tntap.tn.gov/eservices/_/',
        ],
        'form' => [
            'number' => 'RV-F1300501',
            'title' => 'Application for Registration',
            'pdf_url' => 'https://www.tn.gov/content/dam/tn/revenue/documents/forms/general/f13005_1.pdf',
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'No charge to register for a sales and use tax account. A separate business tax license, bought from the county clerk and, where applicable, the city, carries its own fee.',
            'cite' => 'TN DOR help article SUT-10, Sales and Use Tax Account - Registering for an Account (https://revenue.support.tn.gov/hc/en-us/articles/360058139252); Application for Registration instructions, RV-F1300501 (10/20)',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'The certificate does not expire; it can be revoked for violations (no new certificate for 12 months after revocation).',
            'cite' => 'TN Sales and Use Tax Manual (Aug. 2026), ch. 3, Revocation of Certificate of Registration; Tenn. Code Ann. 67-6-603, 67-6-604',
        ],
        'timing' => [
            'online' => null,
            'paper' => 'within a few days after the completed form is returned',
            'temporary_number' => false,
            'summary' => 'The paper Application for Registration says the certificate for each location arrives within a few days after the completed form is returned. Online registration in TNTAP is the Department\'s main route; no separate online time is stated.',
            'cite' => 'Application for Registration instructions, RV-F1300501 (10/20)',
        ],
        'number' => [
            'name' => 'Tennessee sales and use tax account number and location ID',
            'format' => 'Each location has a 10-digit location ID',
            'cite' => 'Carried from resale research of 2026-10-01; TN Sales and Use Tax Manual (Aug. 2026) refers to location IDs per business location',
        ],
    ],
    'nexus' => [
        'physical' => 'Any person that sells, rents or leases tangible personal property or provides taxable services from a temporary or permanent physical location in Tennessee, or through in-state employees, agents, inventory or repair and installation work, must register before doing business, one certificate per location.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => null,
            'period' => 'previous twelve-month period (all retail sales, including exempt retail sales, excluding sales for resale); collection starts the first day of the third month after the month the threshold is met',
            'effective' => '2020-10-01',
            'cite' => 'Tenn. Comp. R. & Regs. 1320-05-01-.129; Public Chapter 646 (2020); TN Sales and Use Tax Manual (Aug. 2026), ch. 2 (threshold cut from $500,000 to $100,000 on October 1, 2020)',
        ],
        'marketplace' => 'Since October 1, 2020 a marketplace facilitator whose own and facilitated sales to Tennessee customers exceed $100,000 in the previous twelve months must register and collect on its marketplace sellers\' sales (Public Chapter 646 (2020); TN Sales and Use Tax Manual, ch. 10).',
    ],
    'filing' => [
        'frequencies' => 'monthly by default; quarterly allowed for small filers; annual for some manufacturers, wholesalers and marketplace-only sellers',
        'rule' => 'Returns are monthly by law. A dealer whose tax has averaged $1,000 or less a month for 12 consecutive months may file monthly or quarterly (amount indexed for inflation every five years). Annual filing generally applies only to manufacturers, wholesalers and sellers whose sales all go through a collecting marketplace facilitator. All returns and payments must be filed electronically.',
        'due_day' => '20th of the month after the period (quarterly: January 20, April 20, July 20, October 20; annual: January 20)',
        'zero_return_required' => null,
        'prepayments' => null,
        'cite' => 'Tenn. Comp. R. & Regs. 1320-05-01-.74; TN Sales and Use Tax Manual (Aug. 2026), ch. 3, Filing Periods and Due Dates; Sales and Use Tax Return instructions (sls450)',
    ],
    'rates' => [
        'state_rate_pct' => 7.0,
        'local' => 'Local option tax of 1.5% to 2.75% (cap 2.75%); 4% state rate on food and food ingredients; an extra 2.75% state single article tax on single items with a sales price over $1,600 and under $3,200 (applied to that band)',
        'sourcing' => 'origin-based sourcing for sales made within Tennessee; out-of-state dealers report local tax by the ship-to or delivery address',
        'cite' => 'TN Sales and Use Tax Manual (Aug. 2026), ch. 1 and ch. 2; Tenn. Code Ann. 67-6-901 to 67-6-903',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'Local sales tax is reported on the state return. Businesses that also owe Tennessee business tax must get a business license from the county clerk and, where applicable, the city; that is a separate tax, not a sales tax registration.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Doing business as a dealer in Tennessee without a certificate of registration is a Class C misdemeanor.',
            'cite' => 'Tenn. Code Ann. 67-6-606; TN Sales and Use Tax Manual (Aug. 2026), ch. 3',
        ],
        'late_filing' => [
            'summary' => 'Delinquency penalty of 5% of the tax per month or part of a month, up to 25%, with a $15 minimum regardless of the tax due. Failing to file electronically can draw a penalty of up to $500 per return.',
            'cite' => 'TN Sales and Use Tax Manual (Aug. 2026), ch. 3, Penalties and Penalty Rates',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales and use tax',
        ],
        'prerequisites' => 'FEIN (or SSN for a sole proprietor); Tennessee Secretary of State control number where applicable. Registering for sales and use tax does not register the business for business tax or the business license; the Application for Registration can list both, plus franchise and excise tax.',
        'cite' => 'TN Sales and Use Tax Manual (Aug. 2026), ch. 3, Registration Process; RV-F1300501 (10/20)',
    ],
    'facts' => [
        [
            'text' => 'A dealer with gross sales of $4,800 a year or less ($400 a month) and taxable services of $1,200 a year or less may pay tax to suppliers instead of registering, at the Department\'s discretion.',
            'source_url' => 'https://www.tn.gov/content/dam/tn/revenue/documents/forms/general/f13005_1.pdf',
        ],
        [
            'text' => 'Remotely accessed software (SaaS used from a Tennessee location) has been taxable since July 1, 2015 under the Revenue Modernization Act.',
            'source_url' => 'https://www.tn.gov/content/dam/tn/revenue/documents/tax_manuals/august-2026/sales-use-tax-manual.pdf',
        ],
        [
            'text' => 'Food and food ingredients are taxed at a reduced 4% state rate, plus local tax.',
            'source_url' => 'https://www.tn.gov/content/dam/tn/revenue/documents/tax_manuals/august-2026/sales-use-tax-manual.pdf',
        ],
        [
            'text' => 'Remote sellers with no Tennessee location that register through the Streamlined Central Registration System may file using the Streamlined Simplified Electronic Return.',
            'source_url' => 'https://www.tn.gov/revenue/taxes/sales-and-use-tax/registration.html',
        ],
        [
            'text' => 'A business with more than one location needs a certificate for each location, and the certificate must be displayed there.',
            'source_url' => 'https://www.tn.gov/content/dam/tn/revenue/documents/tax_manuals/august-2026/sales-use-tax-manual.pdf',
        ],
    ],
    'state_notes' => 'In Tennessee you need a sales and use tax certificate of registration. The Tennessee Department of Revenue issues it. You register online in the Tennessee Taxpayer Access Point (TNTAP) under Register a New Business. A paper Application for Registration, form RV-F1300501, can also be mailed or delivered. The Department says the certificate arrives within a few days of a completed paper form. There is no charge to register. You need a certificate for each business location, and you must display it there. Out-of-state sellers with no physical presence must register once their Tennessee retail sales pass $100,000 in the previous 12 months. Collection starts on the first day of the third month after you pass that mark. The state rate is 7%, food is 4%, and local tax adds 1.5% to 2.75%. Most businesses file monthly. Returns and payments are due on the 20th of the following month and must be filed electronically. Small filers whose tax averages $1,000 or less a month may file quarterly. The mistake to avoid: assuming this registration also covers business tax. Tennessee business tax needs its own license from the county clerk, and from the city where it applies.',
    'sources' => [
        [
            'title' => 'TN DOR Sales and Use Tax Manual (August 2026)',
            'url' => 'https://www.tn.gov/content/dam/tn/revenue/documents/tax_manuals/august-2026/sales-use-tax-manual.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'TN DOR Application for Registration, RV-F1300501 (10/20)',
            'url' => 'https://www.tn.gov/content/dam/tn/revenue/documents/forms/general/f13005_1.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'TN DOR Sales and Use Tax: Registration',
            'url' => 'https://www.tn.gov/revenue/taxes/sales-and-use-tax/registration.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'TN DOR help article SUT-10, Registering for an Account (search snippet; page returned 403)',
            'url' => 'https://revenue.support.tn.gov/hc/en-us/articles/360058139252-SUT-10-Sales-and-Use-Tax-Account-Registering-for-an-Account',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'TN DOR Sales and Use Tax Return instructions',
            'url' => 'https://www.tn.gov/content/dam/tn/revenue/documents/forms/sales/sls450_instruct0721.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'TNTAP',
            'url' => 'https://tntap.tn.gov/eservices/_/',
            'accessed' => '2026-10-02',
        ],
    ],
];
