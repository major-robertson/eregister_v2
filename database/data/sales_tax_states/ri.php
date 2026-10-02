<?php

/*
 * Rhode Island: Rhode Island Department of Revenue, Division of Taxation,
 * Permit to Make Sales at Retail (sales and use tax permit). Researched
 * 2026-10-02 from tax.ri.gov. Generated once from the EREG-13 sales tax
 * research; edit this file directly from now on.
 */

return [
    'state' => 'RI',
    'name' => 'Rhode Island',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Rhode Island Department of Revenue, Division of Taxation',
        'short' => 'the Division of Taxation',
        'url' => 'https://tax.ri.gov/',
    ],
    'registration' => [
        'term' => 'Permit to Make Sales at Retail (sales and use tax permit)',
        'portal' => [
            'name' => 'RI Division of Taxation Taxpayer Portal (online Business Application and Registration)',
            'url' => 'https://taxportal.ri.gov/',
        ],
        'form' => [
            'number' => 'BAR',
            'title' => 'Business Application and Registration',
            'pdf_url' => 'https://tax.ri.gov/sites/g/files/xkgbur541/files/2025-01/BAR_2025.pdf',
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'No fee to apply for or renew a sales permit for periods after July 1, 2022; the former $10 application and renewal fees were repealed in 2021. A Taxpayer Status Affidavit must accompany the application.',
            'cite' => 'R.I.G.L. 44-19-1; Division of Taxation Advisory ADV 2022-05',
        ],
        'renewal' => [
            'required' => true,
            'summary' => 'Permits expire every June 30. Renewal applications are due by February 1 each year, with no fee.',
            'cite' => 'R.I.G.L. 44-19-1(a)(2); Division of Taxation, Sales and Use Tax FAQ; ADV 2022-05',
        ],
        'timing' => [
            'online' => null,
            'paper' => null,
            'temporary_number' => false,
            'summary' => 'You must register before starting business. The Division publishes no processing time. Temporary permits are available for itinerant, seasonal and other sellers without a fixed Rhode Island location.',
            'cite' => 'Division of Taxation, Sales and Use Tax FAQ; Sales & Excise Taxes page',
        ],
        'number' => [
            'name' => 'Permit to Make Sales at Retail number (Sales Tax Permit #)',
            'format' => null,
            'cite' => 'Form BAR Rev. 01/24',
        ],
    ],
    'nexus' => [
        'physical' => 'Anyone making retail sales in Rhode Island needs a permit for each place of business, and out-of-state vendors that solicit, deliver in their own vehicles, or install or repair products in Rhode Island must also register.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => 200,
            'period' => 'prior calendar year',
            'effective' => '2019-07-01',
            'cite' => 'R.I.G.L. 44-18.2; Division of Taxation, Remote Sellers page',
        ],
        'marketplace' => 'Marketplace facilitators and referrers without physical presence that meet the $100,000 or 200-transaction test must register and collect the 7% tax (since July 1, 2019); non-collecting retailers below it may still have notice and reporting duties (Division of Taxation, Remote Sellers page).',
    ],
    'filing' => [
        'frequencies' => 'monthly; quarterly with permission',
        'rule' => 'Returns are generally filed monthly. A seller may apply to file quarterly if sales and use tax liability averaged less than $200 a month for six consecutive months. An annual reconciliation is also due by January 31.',
        'due_day' => '20th of the following month (approved quarterly filers by the last day of the month after the quarter)',
        'zero_return_required' => true,
        'prepayments' => null,
        'cite' => 'Division of Taxation, Sales and Use Tax FAQ; ADV 2022-05',
    ],
    'rates' => [
        'state_rate_pct' => 7.0,
        'local' => 'No local sales tax. A 1% local meals and beverage tax applies to food and drink, and hotel rooms carry a separate 6% hotel tax.',
        'sourcing' => 'destination-based (Streamlined Sales Tax member)',
        'cite' => 'Division of Taxation, Sales and Use Tax page; Sales & Excise Taxes page',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'The 1% local meals and beverage tax is administered by the Division of Taxation. No city or town sales tax registration.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => null,
            'cite' => null,
        ],
        'late_filing' => [
            'summary' => '10% of the tax due for late payment, plus interest at the current rate but not less than 12% a year.',
            'cite' => 'Division of Taxation, Sales and Use Tax FAQ',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales and use tax permit',
            'income tax withholding',
            'unemployment insurance and TDI',
            'litter permit',
            'meals and beverage tax',
            'hotel tax',
            'other licenses on the BAR form',
        ],
        'prerequisites' => 'FEIN (or SSN for sole proprietors) and a signed Taxpayer Status Affidavit confirming state taxes are paid.',
        'cite' => 'Division of Taxation, Registration page; Form BAR Rev. 01/24',
    ],
    'facts' => [
        [
            'text' => 'Rhode Island eliminated the $10 fee to apply for or renew a sales permit for periods after July 1, 2022; the Division returns checks sent when no fee is due.',
            'source_url' => 'https://tax.ri.gov/sites/g/files/xkgbur541/files/2022-01/adv_2022_05_no_fee_sales_tax_permit_final.pdf',
        ],
        [
            'text' => 'Each new business location needs its own permit, and a change in ownership or structure (for example, sole proprietor to corporation) requires a new permit.',
            'source_url' => 'https://tax.ri.gov/tax-sections/sales-excise-taxes/sales-use-tax',
        ],
        [
            'text' => 'Flea market vendors do not get a regular retail permit; they buy a flea market vendor\'s permit for $120 a year, $40 a quarter or $10 for 30 days.',
            'source_url' => 'https://tax.ri.gov/tax-sections/sales-excise-taxes',
        ],
        [
            'text' => 'Show promoters must apply for a Promoter\'s Permit (Form SP-1) at least 10 days before each show.',
            'source_url' => 'https://tax.ri.gov/tax-sections/sales-excise-taxes',
        ],
        [
            'text' => 'Larger registrants must file and pay electronically for tax periods beginning on or after January 1, 2023.',
            'source_url' => 'https://tax.ri.gov/tax-sections/sales-excise-taxes',
        ],
    ],
    'state_notes' => 'In Rhode Island the sales tax registration is called a Permit to Make Sales at Retail. The Rhode Island Division of Taxation issues it. You can apply online through the Division\'s Taxpayer Portal, or mail or email the Business Application and Registration (BAR) form. The same form covers withholding and other state taxes. There is no fee; Rhode Island dropped its $10 permit fee in 2022. You must register before you start selling, and each location needs its own permit. Remote sellers must register once they had $100,000 in Rhode Island sales or 200 transactions in the prior calendar year. After you register, you file monthly, by the 20th of the following month. If your tax averages under $200 a month for six months, you can ask to file quarterly. You must file even when you had no sales; report zero. You also file an annual reconciliation by January 31. A late payment adds a 10% penalty plus interest. The one mistake to avoid: forgetting the renewal. Permits expire every June 30, and the renewal is due by February 1 each year.',
    'sources' => [
        [
            'title' => 'Registration (RI Division of Taxation)',
            'url' => 'https://tax.ri.gov/resources/businesses/registration',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Sales & Excise Taxes',
            'url' => 'https://tax.ri.gov/tax-sections/sales-excise-taxes',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Sales and Use Tax (FAQ)',
            'url' => 'https://tax.ri.gov/tax-sections/sales-excise-taxes/sales-use-tax',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Remote Sellers',
            'url' => 'https://tax.ri.gov/tax-sections/sales-excise-taxes/remote-sellers',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'ADV 2022-05, Sales tax permit fees',
            'url' => 'https://tax.ri.gov/sites/g/files/xkgbur541/files/2022-01/adv_2022_05_no_fee_sales_tax_permit_final.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Form BAR Rev. 01/24, Business Application and Registration',
            'url' => 'https://tax.ri.gov/sites/g/files/xkgbur541/files/2025-01/BAR_2025.pdf',
            'accessed' => '2026-10-02',
        ],
    ],
];
