<?php

/*
 * Wisconsin: Wisconsin Department of Revenue, Seller's Permit (issued
 * through Business Tax Registration). Researched 2026-10-02 from
 * revenue.wi.gov, docs.legis.wisconsin.gov, salestaxinstitute.com.
 * Generated once from the EREG-13 sales tax research; edit this file
 * directly from now on.
 */

return [
    'state' => 'WI',
    'name' => 'Wisconsin',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Wisconsin Department of Revenue',
        'short' => 'the Department of Revenue (DOR)',
        'url' => 'https://www.revenue.wi.gov/',
    ],
    'registration' => [
        'term' => 'Seller\'s Permit (issued through Business Tax Registration)',
        'portal' => [
            'name' => 'Online Business Tax Registration (BTR); returns filed in My Tax Account',
            'url' => 'https://www.revenue.wi.gov/Pages/FAQS/pcs-btr.aspx',
        ],
        'form' => [
            'number' => 'BTR-101',
            'title' => 'Application for Business Tax Registration',
            'pdf_url' => null,
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 2000,
            'summary' => 'A $20 Business Tax Registration fee covers two years. DOR may also require a security deposit of up to $15,000, most often when there is a history of delinquent taxes.',
            'cite' => 'DOR Business Tax Registration FAQ (https://www.revenue.wi.gov/Pages/FAQS/pcs-btr.aspx); Wisconsin Publication 201, Part 3.G',
        ],
        'renewal' => [
            'required' => true,
            'summary' => 'Business Tax Registration renews every two years for $10. The BTR certificate shows the seller\'s permit expiration date.',
            'cite' => 'DOR Business Tax Registration FAQ',
        ],
        'timing' => [
            'online' => 'typically the same day',
            'paper' => 'typically one business day after a faxed BTR-101 is received',
            'temporary_number' => true,
            'summary' => 'Online BTR applications are typically processed the same day. For a faxed BTR-101, DOR will call, email or fax the permit number on request. DOR advises applying at least three weeks before opening.',
            'cite' => 'DOR Business Tax Registration FAQ; Wisconsin Publication 201, Part 3.E',
        ],
        'number' => [
            'name' => 'Wisconsin seller\'s permit number / tax account number',
            'format' => null,
            'cite' => null,
        ],
    ],
    'nexus' => [
        'physical' => 'Every person selling, licensing, leasing or renting taxable products or services from a Wisconsin location or with a physical presence in the state must hold a seller\'s permit, with a separate permit for each business location (filed on one application).',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => null,
            'period' => 'previous or current calendar year (gross sales into Wisconsin must exceed $100,000)',
            'effective' => '2021-02-20',
            'cite' => '2021 Wis. Act 1 (removed the 200-transaction test and moved to calendar years effective February 20, 2021); DOR Remote Sellers page',
        ],
        'marketplace' => 'Since January 1, 2020 a marketplace provider must collect and remit Wisconsin tax on all taxable sales it facilitates for marketplace sellers, regardless of whether those sellers qualify for the small seller exception (2019 Wis. Act 10).',
    ],
    'filing' => [
        'frequencies' => 'monthly, quarterly or annual',
        'rule' => 'Generally quarterly unless DOR notifies the seller in writing to file monthly or annually. Sellers with more than $3,600 of tax a quarter may be told to file by the 20th. Returns must be filed electronically unless DOR grants a hardship waiver.',
        'due_day' => 'last day of the month after the period (the 20th for sellers DOR notifies)',
        'zero_return_required' => true,
        'prepayments' => null,
        'cite' => 'Wisconsin Publication 201, Part 8.A and 8.F',
    ],
    'rates' => [
        'state_rate_pct' => 5.0,
        'local' => '0.5% county tax in most counties; Milwaukee County 0.9% and City of Milwaukee 2% since January 1, 2024 (7.9% combined in the city); premier resort area taxes in some municipalities',
        'sourcing' => 'destination: tax is based on where the buyer receives the product',
        'cite' => 'DOR County and City Sales and Use Taxes (https://www.revenue.wi.gov/Pages/FAQS/pcs-county.aspx); Wisconsin Publication 201, Part 7.C and Part 18',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'County, city and stadium taxes are reported on the state sales and use tax return; the state seller\'s permit covers them.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Operating as a seller without a permit, or after it is suspended, revoked or expired, is a misdemeanor for the seller and each officer or member who acts for it.',
            'cite' => 'Wis. Stat. 77.52(12)',
        ],
        'late_filing' => [
            'summary' => 'Negligence penalty of 5% of the tax per month or part of a month, up to 25%; a $20 late filing fee; 18% annual interest on delinquent tax; and loss of the retailer\'s discount.',
            'cite' => 'Wisconsin Publication 201, Part 8.M',
        ],
    ],
    'connected' => [
        'covers' => [
            'seller\'s permit',
            'use tax certificate',
            'employer withholding registration',
            'excise tax permits',
        ],
        'prerequisites' => 'FEIN where the business has one; a new form of ownership or new FEIN requires a new seller\'s permit',
        'cite' => 'DOR Business Tax Registration FAQ; Wisconsin Publication 201, Part 3.I',
    ],
    'facts' => [
        [
            'text' => 'Since January 1, 2024 the City of Milwaukee charges a 2% city sales tax and Milwaukee County\'s rate rose to 0.9%, making 7.9% the combined rate in the city.',
            'source_url' => 'https://www.revenue.wi.gov/Pages/News/2023/Milwaukee-City-County-Sales-Tax.pdf',
        ],
        [
            'text' => 'Racine County\'s 0.5% county tax began April 1, 2025, and Manitowoc County\'s began January 1, 2025.',
            'source_url' => 'https://www.revenue.wi.gov/DOR%20Publications/pb201.pdf',
        ],
        [
            'text' => 'A permit holder must file a return for every reporting period, even if no tax is due.',
            'source_url' => 'https://www.revenue.wi.gov/DOR%20Publications/pb201.pdf',
        ],
        [
            'text' => 'Wisconsin dropped the 200-transaction test for remote sellers on February 20, 2021; only gross sales over $100,000 now count.',
            'source_url' => 'https://www.revenue.wi.gov/Pages/Businesses/remote-sellers.aspx',
        ],
        [
            'text' => 'Sellers can also register through the Streamlined Sales Tax registration system, since Wisconsin is an SST member state.',
            'source_url' => 'https://www.revenue.wi.gov/DOR%20Publications/pb201.pdf',
        ],
    ],
    'state_notes' => 'In Wisconsin you need a seller\'s permit. The Wisconsin Department of Revenue issues it through Business Tax Registration. Apply online through DOR\'s Business Tax Registration, which is usually processed the same day. You can also fax or mail Form BTR-101, Application for Business Tax Registration. The fee is $20, which covers two years. Renewal costs $10 every two years. DOR can ask for a security deposit, usually only if there is a history of unpaid taxes. You need a permit for each business location, displayed there. Sellers outside Wisconsin must register once their sales into the state pass $100,000 in the current or previous calendar year. The state rate is 5%. Most counties add 0.5%, and the City of Milwaukee and Milwaukee County add more. Most sellers file quarterly unless DOR assigns monthly or annual filing. Returns are due the last day of the month after the period, and must be filed electronically. File a return every period, even with no sales. The mistake to avoid: letting your registration lapse. A seller\'s permit that expires because the $10 renewal was not paid leaves you selling without a valid permit.',
    'sources' => [
        [
            'title' => 'DOR Business Tax Registration FAQ',
            'url' => 'https://www.revenue.wi.gov/Pages/FAQS/pcs-btr.aspx',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Wisconsin Publication 201, Sales and Use Tax Information',
            'url' => 'https://www.revenue.wi.gov/DOR%20Publications/pb201.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DOR Remote Sellers - Wayfair Decision',
            'url' => 'https://www.revenue.wi.gov/Pages/Businesses/remote-sellers.aspx',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DOR Marketplace Providers and Sellers',
            'url' => 'https://www.revenue.wi.gov/Pages/Businesses/marketplace-providers-sellers.aspx',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DOR County and City Sales and Use Taxes',
            'url' => 'https://www.revenue.wi.gov/Pages/FAQS/pcs-county.aspx',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DOR news release: City of Milwaukee and Milwaukee County certify new sales taxes',
            'url' => 'https://www.revenue.wi.gov/Pages/News/2023/Milwaukee-City-County-Sales-Tax.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Wis. Stat. 77.52(12)',
            'url' => 'https://docs.legis.wisconsin.gov/statutes/statutes/77/iii/52/12',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Sales Tax Institute: Wisconsin removes transaction threshold (cross-check)',
            'url' => 'https://www.salestaxinstitute.com/resources/wisconsin-removes-economic-nexus-transaction-threshold',
            'accessed' => '2026-10-02',
        ],
    ],
];
