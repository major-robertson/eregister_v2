<?php

/*
 * Vermont: Vermont Department of Taxes, Sales and Use Tax License (Vermont
 * Business Tax Account). Researched 2026-10-02 from tax.vermont.gov,
 * legislature.vermont.gov. Generated once from the EREG-13 sales tax
 * research; edit this file directly from now on.
 */

return [
    'state' => 'VT',
    'name' => 'Vermont',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Vermont Department of Taxes',
        'short' => 'the Department of Taxes',
        'url' => 'https://tax.vermont.gov/',
    ],
    'registration' => [
        'term' => 'Sales and Use Tax License (Vermont Business Tax Account)',
        'portal' => [
            'name' => 'myVTax (Sign Up), or the Secretary of State\'s online business registration portal',
            'url' => 'https://myvtax.vermont.gov/_/',
        ],
        'form' => [
            'number' => 'BR-400',
            'title' => 'Application for Business Tax Account',
            'pdf_url' => 'https://tax.vermont.gov/sites/tax/files/documents/BR-400.pdf',
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'Registration is free. The Commissioner issues the license without charge.',
            'cite' => '32 V.S.A. 9707(a); Vermont FS-1017 (rev. Aug. 2024)',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'You do not renew a business tax account with the Department of Taxes (Secretary of State annual reports are separate).',
            'cite' => 'https://tax.vermont.gov/business/register',
        ],
        'timing' => [
            'online' => null,
            'paper' => 'about two weeks',
            'temporary_number' => false,
            'summary' => 'The paper BR-400 says to allow two weeks for processing and to contact the Department for expedited processing. The Department says online registration is faster but gives no figure.',
            'cite' => 'Form BR-400 (Rev. 08/25), p. 3',
        ],
        'number' => [
            'name' => 'Vermont Sales and Use Tax Account Number',
            'format' => null,
            'cite' => null,
        ],
    ],
    'nexus' => [
        'physical' => 'Every person required to collect Vermont sales tax, including any business with a place of business in Vermont, must get a license before starting business or opening a new location, with a separate license displayed at each location (32 V.S.A. 9707).',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => 200,
            'period' => 'the 12-month period preceding the monthly period for which liability is determined; register within 30 days of crossing and collect from the first day of the following month',
            'effective' => '2018-07-01',
            'cite' => '32 V.S.A. 9701(9)(F); Act 134 of 2016; Vermont Department of Taxes, Sales Tax and Wayfair FAQs',
        ],
        'marketplace' => 'Since June 1, 2019 a marketplace facilitator that facilitated at least $100,000 or 200 transactions of sales into Vermont in the preceding 12 months must collect and remit on its marketplace sellers\' Vermont sales (32 V.S.A. 9701(9)(J)).',
    ],
    'filing' => [
        'frequencies' => 'monthly, quarterly or annual by prior-year liability',
        'rule' => 'Prior calendar year liability of $500 or less: annual. More than $500 but less than $2,500: quarterly. Otherwise monthly. The Commissioner may set other periods. E-filing is required for local option tax sellers, multi-location sellers, and sellers who remitted more than $100,000 in the prior year.',
        'due_day' => '25th of the month after the period (23rd in February); annual returns January 25',
        'zero_return_required' => true,
        'prepayments' => null,
        'cite' => '32 V.S.A. 9775(a), (b); Vermont FS-1017 (rev. Aug. 2024), \'Always File\'',
    ],
    'rates' => [
        'state_rate_pct' => 6.0,
        'local' => '1% local option sales tax in municipalities that adopt it (7% combined)',
        'sourcing' => 'destination: where the buyer takes possession or the item is delivered',
        'cite' => 'Vermont FS-1017 (rev. Aug. 2024)',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'Local option tax is collected and filed with the state return in myVTax; no separate municipal registration.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => null,
            'cite' => null,
        ],
        'late_filing' => [
            'summary' => '5% of the unpaid tax for each month or part of a month the return is late, up to 25%. If the return is more than 60 days late, a minimum $50 penalty applies even when no tax is due. Interest is charged separately.',
            'cite' => '32 V.S.A. 3202(b)(1)',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales and use tax',
        ],
        'prerequisites' => 'Business entities register with the Vermont Secretary of State first; its online portal can register the business for sales and use, meals and rooms, and withholding tax accounts at the same time. Each of those taxes is a separate account.',
        'cite' => 'Form BR-400 (Rev. 08/25) cover page; https://tax.vermont.gov/business/register',
    ],
    'facts' => [
        [
            'text' => 'Since July 1, 2024 all prewritten software is taxable in Vermont, including software accessed remotely (SaaS). The earlier exemption for remotely accessed software ended June 30, 2024 (Act 183 of 2024).',
            'source_url' => 'https://tax.vermont.gov/business-and-corp/sales-and-use-tax/prewritten-computer-software',
        ],
        [
            'text' => 'Clothing, footwear and food are among the items exempt from Vermont sales tax.',
            'source_url' => 'https://tax.vermont.gov/sites/tax/files/documents/FS-1017.pdf',
        ],
        [
            'text' => 'Vermont still counts transactions: a remote seller must register at $100,000 in sales or 200 transactions in the prior 12 months.',
            'source_url' => 'https://tax.vermont.gov/business-and-corp/sales-and-use-tax/wayfair/faqs',
        ],
        [
            'text' => 'Businesses with more than one location need a separate license for each location but should use one myVTax account. Vendors with no permanent location, such as cart vendors, may use one license.',
            'source_url' => 'https://tax.vermont.gov/sites/tax/files/documents/FS-1017.pdf',
        ],
        [
            'text' => 'Vermont is a Streamlined Sales Tax member state; the SST exemption certificate is accepted alongside Form S-3.',
            'source_url' => 'https://tax.vermont.gov/content/form-s-3',
        ],
    ],
    'state_notes' => 'In Vermont you need a sales and use tax license, issued through a Vermont business tax account. The Vermont Department of Taxes issues it. Register online through myVTax or the Secretary of State\'s business registration portal. If you cannot go online, mail or fax Form BR-400, Application for Business Tax Account, and allow about two weeks. Registration is free, and you do not renew the account. You need a separate license, displayed at each location. Sellers outside Vermont must register once they reach $100,000 in sales or 200 transactions into Vermont in the prior 12 months. They have 30 days to register. The state rate is 6%. Some towns add a 1% local option tax, based on where the buyer receives the item. Clothing and food are exempt, but software, including software used online, is taxable. The Department sets your filing schedule from last year\'s tax: annual at $500 or less, quarterly under $2,500, and monthly above that. Returns are due on the 25th of the month after the period, or the 23rd in February. The mistake to avoid: skipping a return when you owe nothing. A return more than 60 days late draws a $50 minimum penalty even with no tax due.',
    'sources' => [
        [
            'title' => 'Vermont Department of Taxes: Sales and Use Tax',
            'url' => 'https://tax.vermont.gov/business/sales-and-use-tax',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Vermont Department of Taxes: Register for a Business Tax Account',
            'url' => 'https://tax.vermont.gov/business/register',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Vermont FS-1017, Sales and Use Tax for Businesses (rev. Aug. 2024)',
            'url' => 'https://tax.vermont.gov/sites/tax/files/documents/FS-1017.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Form BR-400 (Rev. 08/25)',
            'url' => 'https://tax.vermont.gov/sites/tax/files/documents/BR-400.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Vermont Department of Taxes: Sales Tax and Wayfair FAQs',
            'url' => 'https://tax.vermont.gov/business-and-corp/sales-and-use-tax/wayfair/faqs',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Vermont Department of Taxes: Marketplace Facilitators',
            'url' => 'https://tax.vermont.gov/business-and-corp/sales-and-use-tax/marketplace',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Vermont Department of Taxes: Prewritten Computer Software',
            'url' => 'https://tax.vermont.gov/business-and-corp/sales-and-use-tax/prewritten-computer-software',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => '32 V.S.A. 9701',
            'url' => 'https://legislature.vermont.gov/statutes/section/32/233/09701',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => '32 V.S.A. 9707',
            'url' => 'https://legislature.vermont.gov/statutes/section/32/233/09707',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => '32 V.S.A. 9775',
            'url' => 'https://legislature.vermont.gov/statutes/section/32/233/09775',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => '32 V.S.A. 3202',
            'url' => 'https://legislature.vermont.gov/statutes/section/32/103/03202',
            'accessed' => '2026-10-02',
        ],
    ],
];
