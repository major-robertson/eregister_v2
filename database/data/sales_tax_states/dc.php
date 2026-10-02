<?php

/*
 * District of Columbia: District of Columbia Office of Tax and Revenue,
 * Sales and Use Tax Certificate of Registration. Researched 2026-10-02
 * from otr.cfo.dc.gov, salestaxinstitute.com. Generated once from the
 * EREG-13 sales tax research; edit this file directly from now on.
 */

return [
    'state' => 'DC',
    'name' => 'District of Columbia',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'District of Columbia Office of Tax and Revenue',
        'short' => 'OTR',
        'url' => 'https://otr.cfo.dc.gov/',
    ],
    'registration' => [
        'term' => 'Sales and Use Tax Certificate of Registration',
        'portal' => [
            'name' => 'MyTax.DC.gov',
            'url' => 'https://mytax.dc.gov/',
        ],
        'form' => [
            'number' => 'FR-500',
            'title' => 'Combined Registration Application for Business DC Taxes/Fees/Assessments (filed online at MyTax.DC.gov)',
            'pdf_url' => 'https://otr.cfo.dc.gov/sites/default/files/dc/sites/otr/publication/attachments/FR-500_314.pdf',
            'online_only' => null,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'There is no charge for registering with OTR. OTR issues a Certificate of Registration for each location listed on the FR-500, and each location must display its own. (The separate DC Basic Business License from DLCP has its own fees.)',
            'cite' => 'FR-800V instructions (2025), https://otr.cfo.dc.gov/sites/default/files/dc/sites/otr/page_content/attachments/2025%20FR800V%20instructions%20v1.0_Final_08232024.pdf',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'No renewal of the OTR Certificate of Registration was found.',
            'cite' => null,
        ],
        'timing' => [
            'online' => null,
            'paper' => null,
            'temporary_number' => false,
            'summary' => null,
            'cite' => null,
        ],
        'number' => [
            'name' => 'DC sales and use tax account ID',
            'format' => null,
            'cite' => null,
        ],
    ],
    'nexus' => [
        'physical' => 'Anyone selling or delivering taxable goods or services in the District must file Form FR-500 and get a Certificate of Registration before making taxable sales.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => 200,
            'period' => 'previous or current calendar year (gross receipts from retail sales delivered into DC, or 200 separate retail sales; either test)',
            'effective' => '2019-01-01',
            'cite' => 'D.C. Code 47-2001(n-1); OTR Sales and Use Tax FAQs, https://otr.cfo.dc.gov/page/sales-and-use-tax-faqs',
        ],
        'marketplace' => 'Beginning April 1, 2019, a marketplace facilitator must collect District sales tax on behalf of its marketplace sellers (https://otr.cfo.dc.gov/page/sales-and-use-tax-faqs).',
    ],
    'filing' => [
        'frequencies' => 'monthly, quarterly or annual by liability per period',
        'rule' => 'Annual (FR-800A) if sales and use tax liability is $200 or less per period; quarterly (FR-800Q) from $201 to $1,200 per period; monthly (FR-800M) at $1,201 or more per period. Liability of $5,000 or more requires electronic filing and payment.',
        'due_day' => '20th of the month after the period; annual returns October 20',
        'zero_return_required' => true,
        'prepayments' => null,
        'cite' => 'FR-500 instructions (Rev. 03/14), https://otr.cfo.dc.gov/sites/default/files/dc/sites/otr/publication/attachments/FR-500_314.pdf; https://otr.cfo.dc.gov/page/sales-and-use-tax-faqs',
    ],
    'rates' => [
        'state_rate_pct' => 6.0,
        'local' => 'No separate local taxes; DC is one jurisdiction. Higher District rates apply to some items: 8% soft drinks, 10% restaurant meals and on-premises alcohol, 10.25% off-premises alcohol and rental vehicles, 15.95% hotel rooms, 18% parking. The scheduled rise of the general rate to 7% has been postponed to October 1, 2027.',
        'sourcing' => 'single District rate',
        'cite' => 'https://otr.cfo.dc.gov/page/sales-and-use-tax-faqs',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => null,
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Making taxable sales without a Certificate of Registration can bring a fine of up to $50 for each day of business in the District without one.',
            'cite' => 'FR-800V instructions (2025), https://otr.cfo.dc.gov/sites/default/files/dc/sites/otr/page_content/attachments/2025%20FR800V%20instructions%20v1.0_Final_08232024.pdf',
        ],
        'late_filing' => [
            'summary' => '5% per month (or part of a month) for failing to file or pay on time, up to 25% of the tax; interest at 10% a year, compounded daily.',
            'cite' => 'https://otr.cfo.dc.gov/page/sales-and-use-tax-faqs; FR-800V instructions (2025)',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales and use tax',
            'employer withholding',
            'franchise taxes',
            'personal property tax',
            'other DC taxes and fees on the same FR-500',
        ],
        'prerequisites' => 'OTR registration (FR-500) is the first step to doing business in DC and comes before the Basic Business License; unemployment tax is registered separately with DOES.',
        'cite' => 'https://otr.cfo.dc.gov/page/business-tax; FR-500 (Rev. 03/14)',
    ],
    'facts' => [
        [
            'text' => 'DC\'s general sales tax rate stays at 6% through September 30, 2027; the scheduled increase to 7% has been postponed and now applies to periods beginning October 1, 2027.',
            'source_url' => 'https://otr.cfo.dc.gov/page/sales-and-use-tax-faqs',
        ],
        [
            'text' => 'DC\'s remote seller test is $100,000 in sales or 200 separate sales delivered into the District, in the previous or current calendar year.',
            'source_url' => 'https://otr.cfo.dc.gov/page/sales-and-use-tax-faqs',
        ],
        [
            'text' => 'Sales and use tax registrants must file a return even if no sales were made, entering zero.',
            'source_url' => 'https://otr.cfo.dc.gov/sites/default/files/dc/sites/otr/page_content/attachments/2025%20FR800V%20instructions%20v1.0_Final_08232024.pdf',
        ],
        [
            'text' => 'Since November 1, 2017, OTR accepts resale certificates only on its authorized form (OTR-368), requested through MyTax.DC.gov and valid for one year.',
            'source_url' => 'https://otr.cfo.dc.gov/node/383742',
        ],
        [
            'text' => 'Many services are taxable in DC, including health-club services and car washing; OTR keeps a list of taxable and non-taxable services.',
            'source_url' => 'https://otr.cfo.dc.gov/page/taxable-and-non-taxable-services',
        ],
    ],
    'state_notes' => 'In the District of Columbia, you register for sales and use tax with the Office of Tax and Revenue (OTR). You apply online at MyTax.DC.gov using Form FR-500, the Combined Registration Application for Business DC Taxes/Fees/Assessments. There is no charge to register. OTR issues a Certificate of Registration for each location you list, and each location must display its own. The same FR-500 can also register you for withholding and other DC taxes. OTR registration comes before your DC Basic Business License, which is a separate license with its own fees. OTR sets your filing schedule by how much tax you owe each period: annual at $200 or less, quarterly from $201 to $1,200, and monthly above that. Returns are due on the 20th of the following month. You must file even when you had no sales. The general rate is 6%. The planned rise to 7% has been postponed to October 1, 2027. Restaurant meals, hotel rooms and parking have higher rates. The one mistake to avoid: selling before you have the certificate. OTR can fine you up to $50 for each day you do business without one.',
    'sources' => [
        [
            'title' => 'OTR: Sales and Use Tax FAQs',
            'url' => 'https://otr.cfo.dc.gov/page/sales-and-use-tax-faqs',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'OTR: FR-800V instructions (2025)',
            'url' => 'https://otr.cfo.dc.gov/sites/default/files/dc/sites/otr/page_content/attachments/2025%20FR800V%20instructions%20v1.0_Final_08232024.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'OTR: Form FR-500 (Rev. 03/14) with instructions',
            'url' => 'https://otr.cfo.dc.gov/sites/default/files/dc/sites/otr/publication/attachments/FR-500_314.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'OTR: Businesses',
            'url' => 'https://otr.cfo.dc.gov/page/business-tax',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Sales Tax Institute: DC sales tax rate increase to 7% (updated 2026-09-22)',
            'url' => 'https://www.salestaxinstitute.com/resources/dc-sales-tax-rate-increase-7-percent-2026',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'OTR: Taxable and Non-Taxable Services',
            'url' => 'https://otr.cfo.dc.gov/page/taxable-and-non-taxable-services',
            'accessed' => '2026-10-02',
        ],
    ],
];
