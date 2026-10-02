<?php

/*
 * Mississippi: Mississippi Department of Revenue, Sales Tax Permit
 * (in-state sellers); Seller's Use Tax Permit (out-of-state sellers).
 * Researched 2026-10-02 from dor.ms.gov, law.justia.com. Generated once
 * from the EREG-13 sales tax research; edit this file directly from now
 * on.
 */

return [
    'state' => 'MS',
    'name' => 'Mississippi',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Mississippi Department of Revenue',
        'short' => 'MDOR',
        'url' => 'https://www.dor.ms.gov/',
    ],
    'registration' => [
        'term' => 'Sales Tax Permit (in-state sellers); Seller\'s Use Tax Permit (out-of-state sellers)',
        'portal' => [
            'name' => 'Taxpayer Access Point (TAP)',
            'url' => 'https://tap.dor.ms.gov/_/',
        ],
        'form' => [
            'number' => null,
            'title' => 'Online application through TAP',
            'pdf_url' => null,
            'online_only' => null,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'No fee to obtain a sales tax permit. A bond may be required depending on the business or the applicant\'s history; businesses with no permanent place of business in Mississippi must post a bond covering twice the estimated tax for three months.',
            'cite' => 'https://www.dor.ms.gov/business/business-tax-frequently-asked-questions; Miss. Code 27-65-27',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'No renewal is described. Permits are location-specific and not transferable; they can be revoked for failure to file or pay.',
            'cite' => 'https://www.dor.ms.gov/business/business-tax-frequently-asked-questions',
        ],
        'timing' => [
            'online' => null,
            'paper' => null,
            'temporary_number' => false,
            'summary' => 'Processing time depends on how complete the application is and whether the applicant owes existing taxes. Once the application is reviewed and approved, MDOR says the permit should arrive by mail within 2 weeks. A business may not start selling before it has the permit.',
            'cite' => 'https://www.dor.ms.gov/business/business-tax-frequently-asked-questions',
        ],
        'number' => [
            'name' => 'Mississippi sales tax permit / account number',
            'format' => null,
            'cite' => null,
        ],
    ],
    'nexus' => [
        'physical' => 'A business with property in Mississippi, or with employees or agents who serve customers or take orders there, has nexus; any company doing taxable work in Mississippi, including out-of-state contractors, must register before the work begins.',
        'economic' => [
            'revenue_usd' => 250000,
            'transactions' => null,
            'period' => 'any 12 consecutive months (sales into Mississippi)',
            'effective' => '2018-07-01',
            'cite' => 'MDOR Business Tax FAQ (states effective July 1, 2018); 35 Miss. Admin. Code Pt. IV, R. 3.09',
        ],
        'marketplace' => 'Since July 1, 2020 (HB 379, Mississippi Marketplace Facilitator Act of 2020), a marketplace facilitator with over $250,000 in facilitated sales into Mississippi in any 12-month period must register for a use tax account and collect on facilitated sales (MDOR Notice 72-20-04).',
    ],
    'filing' => [
        'frequencies' => 'monthly or quarterly (annual by assignment)',
        'rule' => 'Generally, a retailer with average liability of $300 or more a month files monthly; smaller retailers may file quarterly. MDOR reviews accounts every year and adjusts frequencies.',
        'due_day' => '20th of the month after the period',
        'zero_return_required' => true,
        'prepayments' => null,
        'cite' => 'https://www.dor.ms.gov/business/business-tax-frequently-asked-questions',
    ],
    'rates' => [
        'state_rate_pct' => 7.0,
        'local' => 'Only two city sales taxes: Tupelo adds 0.25% and Jackson adds 1% on certain sales. Many cities and counties levy tourism and economic development taxes on hotels, restaurants and bars. Groceries are taxed at 5% since July 1, 2025.',
        'sourcing' => 'origin for local city taxes (applies to sales made from businesses inside the city limits)',
        'cite' => 'https://www.dor.ms.gov/business/business-tax-frequently-asked-questions; https://www.dor.ms.gov/business/sales-use-tax/sales-tax-rates',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'No separate local registration; the Tupelo and Jackson taxes and local tourism and economic development taxes are reported to MDOR.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'It is unlawful to engage or continue in a taxable business without the required permit or after it is revoked; continuing after revocation may bring criminal charges.',
            'cite' => 'Miss. Code 27-65-85; Miss. Code 27-65-27; MDOR Business Tax FAQ',
        ],
        'late_filing' => [
            'summary' => 'Deficient or delinquent tax from negligence or noncompliance may draw a 10% penalty, interest of 0.5% a month, or both. The 2% timely-filing discount (up to $50) is lost.',
            'cite' => 'https://www.dor.ms.gov/business/business-tax-frequently-asked-questions',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales tax',
            'seller\'s use tax',
            'consumer use tax',
            'withholding tax',
            'contractor\'s tax',
        ],
        'prerequisites' => 'SSNs of corporate officers are required on the application. Past-due sales tax must be paid or a bond posted before a new permit is issued.',
        'cite' => 'https://www.dor.ms.gov/business/business-tax-frequently-asked-questions',
    ],
    'facts' => [
        [
            'text' => 'Since July 1, 2025, groceries (food and drink eligible for SNAP) are taxed at 5% instead of 7% under House Bill 1 of the 2025 session.',
            'source_url' => 'https://www.dor.ms.gov/news/reduced-sales-tax-groceries-begins-july-1',
        ],
        [
            'text' => 'Mississippi taxes many services at 7%, including auto repair, electrical and plumbing work, landscaping, pest control, and computer software sales and services.',
            'source_url' => 'https://www.dor.ms.gov/business/sales-use-tax/sales-tax-rates',
        ],
        [
            'text' => 'Each business location needs its own permit, even though one legal entity has a single account number; moving even across the street requires an update.',
            'source_url' => 'https://www.dor.ms.gov/business/business-tax-frequently-asked-questions',
        ],
        [
            'text' => 'Mississippi does not accept blanket exemption certificates; resale purchases are supported by the buyer\'s MDOR-issued permit or letter.',
            'source_url' => 'https://www.dor.ms.gov/business/business-tax-frequently-asked-questions',
        ],
        [
            'text' => 'Contractors on non-residential jobs over $10,000 may owe the 3.5% contractor\'s tax on the contract price.',
            'source_url' => 'https://www.dor.ms.gov/business/sales-use-tax/sales-tax-rates',
        ],
    ],
    'state_notes' => 'In Mississippi, in-state sellers need a Sales Tax Permit, and out-of-state sellers need a Seller\'s Use Tax Permit. The Mississippi Department of Revenue (MDOR) issues both. You apply online through the Taxpayer Access Point (TAP). There is no fee, but MDOR can require a bond, for example if you have no permanent place of business in the state or a history of late filing. Once your application is approved, the permit should arrive by mail within about two weeks. You may not start selling until you have it. Each business location needs its own permit. Returns are due on the 20th of the month after the period. Most sellers with $300 or more in tax a month file monthly, and smaller sellers may file quarterly. Filing and paying on time earns a 2% discount, up to $50. The state rate is 7%, with 5% on groceries since July 2025. Jackson and Tupelo add small city taxes, and many towns add tourism taxes on hotels and restaurants. Remote sellers must register once Mississippi sales pass $250,000 in any 12 months. The mistake to avoid: skipping returns in slow periods. Every permit holder must file each period, even with zero sales.',
    'sources' => [
        [
            'title' => 'MDOR: Business Tax Frequently Asked Questions',
            'url' => 'https://www.dor.ms.gov/business/business-tax-frequently-asked-questions',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'MDOR: Sales Tax Rates',
            'url' => 'https://www.dor.ms.gov/business/sales-use-tax/sales-tax-rates',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'MDOR: Reduced sales tax on groceries begins July 1',
            'url' => 'https://www.dor.ms.gov/news/reduced-sales-tax-groceries-begins-july-1',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'MDOR Notice 72-20-04 Marketplace Facilitators',
            'url' => 'https://www.dor.ms.gov/sites/default/files/news/72-20-04%20MARKETPLACE%20FACILITATORS.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Miss. Code 27-65-27 (Justia)',
            'url' => 'https://law.justia.com/codes/mississippi/title-27/chapter-65/in-general/section-27-65-27/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Miss. Code 27-65-85 (Justia)',
            'url' => 'https://law.justia.com/codes/mississippi/title-27/chapter-65/in-general/section-27-65-85/',
            'accessed' => '2026-10-02',
        ],
    ],
];
