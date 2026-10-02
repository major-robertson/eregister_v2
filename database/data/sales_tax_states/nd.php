<?php

/*
 * North Dakota: North Dakota Office of State Tax Commissioner, Sales and
 * Use Tax Permit (sales, use and gross receipts tax permit). Researched
 * 2026-10-02 from tax.nd.gov, ndlegis.gov. Generated once from the EREG-13
 * sales tax research; edit this file directly from now on.
 */

return [
    'state' => 'ND',
    'name' => 'North Dakota',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'North Dakota Office of State Tax Commissioner',
        'short' => 'the Tax Commissioner',
        'url' => 'https://www.tax.nd.gov/',
    ],
    'registration' => [
        'term' => 'Sales and Use Tax Permit (sales, use and gross receipts tax permit)',
        'portal' => [
            'name' => 'North Dakota Taxpayer Access Point (ND TAP)',
            'url' => 'https://tap.tax.nd.gov/',
        ],
        'form' => [
            'number' => null,
            'title' => 'Application for Sales and Use Tax Permit (in ND TAP)',
            'pdf_url' => null,
            'online_only' => true,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'No fee for a first permit. A $50 fee applies to reissuing a permit after revocation.',
            'cite' => 'N.D.C.C. 57-39.2-14',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'Permits stay valid until revoked. The Commissioner may revoke a permit after four straight quarters of returns showing no tax due.',
            'cite' => 'N.D.C.C. 57-39.2-14(3) and (4)',
        ],
        'timing' => [
            'online' => null,
            'paper' => null,
            'temporary_number' => false,
            'summary' => 'The Tax Commissioner asks sellers to apply 30 days before opening for business. No processing time is published.',
            'cite' => 'Office of State Tax Commissioner, Sales and Use Tax page',
        ],
        'number' => [
            'name' => 'Sales and use tax permit number',
            'format' => null,
            'cite' => 'SALES TAX: Sales, Use, and Gross Receipts Tax Requirements Guideline (Rev. 10-2025)',
        ],
    ],
    'nexus' => [
        'physical' => 'Any business with a physical presence in North Dakota making taxable retail sales of goods or certain services, admissions or lodging must hold a permit for each place of business; transient merchants must show it to customers.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => null,
            'period' => 'previous or current calendar year (taxable sales into North Dakota)',
            'effective' => '2019-07-01',
            'cite' => 'N.D.C.C. 57-39.2-02.2; Office of State Tax Commissioner, Sales and Use Tax History (200-transaction test repealed)',
        ],
        'marketplace' => 'Out-of-state marketplace facilitators must collect on all North Dakota sales made through their marketplace once facilitated taxable sales exceed $100,000 in the current or previous calendar year; first-time facilitators had to start by October 1, 2019 (Sales and Use Tax History).',
    ],
    'filing' => [
        'frequencies' => 'quarterly by default; monthly for larger sellers; semiannual or annual where assigned',
        'rule' => 'Quarterly, unless taxable sales in the preceding calendar year were $333,000 or more, which requires monthly filing. The Commissioner reviews filing status each year (changes effective July 1) and may assign other periods such as semiannual or annual.',
        'due_day' => 'Last day of the month after the period (annual returns due January 31)',
        'zero_return_required' => true,
        'prepayments' => null,
        'cite' => 'N.D.C.C. 57-39.2-12(1); Sales, Use, and Gross Receipts Tax Requirements Guideline (Rev. 10-2025); Sales and Use Tax Deadlines page',
    ],
    'rates' => [
        'state_rate_pct' => 5.0,
        'local' => 'Cities and counties add local option sales and use taxes, administered by the Tax Commissioner. Gross receipts tax rates apply to alcohol (7%) and new farm machinery (3%).',
        'sourcing' => 'destination-based (Streamlined Sales Tax member); remote sellers collect local tax where a local use tax applies',
        'cite' => 'Office of State Tax Commissioner, Sales and Use Tax page; Requirements Guideline, Local Sales and Use Tax',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'Local option taxes, including city lodging and restaurant taxes, are administered by the Office of State Tax Commissioner. No separate local registration.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Selling at retail without a permit, or after it is revoked, is a class A misdemeanor for the business and its officers or managers.',
            'cite' => 'N.D.C.C. 57-39.2-18(2)',
        ],
        'late_filing' => [
            'summary' => '5% of the tax due or $5, whichever is greater, for the first month, plus 5% for each additional month, up to 25%. Interest is 1% a month after the first month (12% a year).',
            'cite' => 'N.D.C.C. 57-39.2-18(1); Requirements Guideline, Penalties',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales tax',
            'use tax',
            'gross receipts tax',
            'local option sales and use taxes',
        ],
        'prerequisites' => null,
        'cite' => 'Requirements Guideline, Permit Requirements',
    ],
    'facts' => [
        [
            'text' => 'North Dakota repealed its 200-transaction test, so remote sellers consider only the $100,000 taxable sales threshold in the previous or current calendar year.',
            'source_url' => 'https://www.tax.nd.gov/sales-and-use-tax-history',
        ],
        [
            'text' => 'A remote seller that meets the threshold must register and start collecting the next calendar year or 60 days after meeting it, whichever is earlier.',
            'source_url' => 'https://www.tax.nd.gov/sales-and-use-tax-history',
        ],
        [
            'text' => 'Permits are not transferable. A buyer of an existing business must apply for a new permit.',
            'source_url' => 'https://www.tax.nd.gov/business/sales-and-use-tax',
        ],
        [
            'text' => 'Bisbee, Cando and Scranton impose a local sales tax but no local use tax, so only businesses located in those cities collect their local tax.',
            'source_url' => 'https://www.tax.nd.gov/sites/www/files/documents/guidelines/business/sales-use/sales-use-and-gross-receipts-tax-requirements-1.pdf',
        ],
        [
            'text' => 'Religious, charitable and nonprofit organizations are not generally exempt from North Dakota sales tax on their purchases.',
            'source_url' => 'https://www.tax.nd.gov/business/sales-and-use-tax',
        ],
    ],
    'state_notes' => 'In North Dakota you need a Sales and Use Tax Permit. The Office of State Tax Commissioner issues it. You apply online in the North Dakota Taxpayer Access Point (ND TAP). There is no fee for a first permit. Apply about 30 days before you open. A permit covers one place of business and stays valid until it is revoked. It cannot be transferred, so a buyer of an existing business applies for a new one. Remote sellers must register once their taxable sales into North Dakota pass $100,000 in the current or prior calendar year. There is no transaction count. After you register, most sellers file quarterly. If your taxable sales were $333,000 or more last year, you file monthly. Returns are due on the last day of the month after the period, and you file them in ND TAP. You must file even when no tax is due. Late returns cost 5% or $5, whichever is more, for the first month, plus 5% a month up to 25%. The one mistake to avoid: filing zero returns for a year and then going quiet. Four straight quarters with no tax due can lead the Commissioner to revoke your permit.',
    'sources' => [
        [
            'title' => 'Sales and Use Tax (Office of State Tax Commissioner)',
            'url' => 'https://www.tax.nd.gov/business/sales-and-use-tax',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Sales, Use, and Gross Receipts Tax Requirements Guideline (Rev. 10-2025)',
            'url' => 'https://www.tax.nd.gov/sites/www/files/documents/guidelines/business/sales-use/sales-use-and-gross-receipts-tax-requirements-1.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Sales and Use Tax History',
            'url' => 'https://www.tax.nd.gov/sales-and-use-tax-history',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Sales and Use Tax Deadlines',
            'url' => 'https://www.tax.nd.gov/sales-and-use-tax-deadlines',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'N.D.C.C. chapter 57-39.2 (57-39.2-12, -14, -18)',
            'url' => 'https://ndlegis.gov/cencode/t57c39-2.pdf',
            'accessed' => '2026-10-02',
        ],
    ],
];
