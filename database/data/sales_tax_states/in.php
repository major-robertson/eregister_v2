<?php

/*
 * Indiana: Indiana Department of Revenue, Registered Retail Merchant
 * Certificate (RRMC). Researched 2026-10-02 from in.gov,
 * codes.findlaw.com, secure.in.gov. Generated once from the EREG-13 sales
 * tax research; edit this file directly from now on.
 */

return [
    'state' => 'IN',
    'name' => 'Indiana',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Indiana Department of Revenue',
        'short' => 'DOR',
        'url' => 'https://www.in.gov/dor/',
    ],
    'registration' => [
        'term' => 'Registered Retail Merchant Certificate (RRMC)',
        'portal' => [
            'name' => 'INBiz (registration); INTIME (filing and account management)',
            'url' => 'https://inbiz.in.gov/',
        ],
        'form' => [
            'number' => 'BT-1',
            'title' => 'Business Tax Application (completed through INBiz)',
            'pdf_url' => null,
            'online_only' => null,
        ],
        'fee' => [
            'amount_cents' => 2500,
            'summary' => '$25 nonrefundable registration fee for each place of business listed on the application.',
            'cite' => 'IC 6-2.5-8-1(b); DOR New & Small Business Owners tax guide',
        ],
        'renewal' => [
            'required' => true,
            'summary' => 'The RRMC is valid for two years and DOR renews it automatically at no charge if all returns and payments are current; otherwise renewal is held until the account is brought current.',
            'cite' => 'IC 6-2.5-8-1(g); DOR Business FAQ',
        ],
        'timing' => [
            'online' => 'up to 2 business days',
            'paper' => null,
            'temporary_number' => false,
            'summary' => 'DOR says processing of the online INBiz application may take up to two business days; the guide says to allow a few days before the Indiana Taxpayer Identification Number arrives for setting up INTIME.',
            'cite' => 'https://www.in.gov/dor/i-am-a/business-corp/business-faq/',
        ],
        'number' => [
            'name' => 'Indiana Taxpayer Identification Number (TID) with location (LOC) number',
            'format' => '10-digit TID followed by a 3-digit LOC number',
            'cite' => 'DOR New & Small Business Owners tax guide (TID); resale research 2026-10-01',
        ],
    ],
    'nexus' => [
        'physical' => 'Any retail merchant making retail or wholesale sales from a place of business in Indiana, including food trucks and sellers at fairs and flea markets, must register each location.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => null,
            'period' => 'previous or current calendar year (gross revenue from sales into Indiana, including exempt sales)',
            'effective' => '2024-01-01',
            'cite' => 'IC 6-2.5-2-1; https://www.in.gov/dor/i-am-a/business-corp/remote-sellers/ (200-transaction test removed effective Jan. 1, 2024)',
        ],
        'marketplace' => 'Since July 1, 2019, a marketplace facilitator over the $100,000 threshold must register and collect Indiana sales tax on all sales it facilitates (IC 6-2.5-4-18; Sales Tax Information Bulletin #89).',
    ],
    'filing' => [
        'frequencies' => 'monthly or annual',
        'rule' => 'Returns (Form ST-103, filed electronically through INTIME) are due monthly unless annual collections are under $1,000, in which case DOR assigns annual filing. Frequency is based on average monthly liability for the tax year.',
        'due_day' => '30 days after the end of the month; 20 days after the end of the month for merchants whose average monthly liability exceeds $1,000',
        'zero_return_required' => true,
        'prepayments' => null,
        'cite' => 'DOR New & Small Business Owners tax guide; https://www.in.gov/dor/i-am-a/business-corp/business-faq/',
    ],
    'rates' => [
        'state_rate_pct' => 7.0,
        'local' => 'No local general sales tax; some counties and cities add food and beverage tax and county innkeeper\'s tax, filed with DOR.',
        'sourcing' => 'destination (Streamlined Sales Tax member)',
        'cite' => 'https://www.in.gov/dor/i-am-a/business-corp/sales-tax/',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'No separate local sales tax registration. Local food and beverage and innkeeper\'s taxes are registered through the same INBiz application. A county vendor\'s license is a separate local matter; contact the county clerk.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Making retail transactions without an RRMC, or after it is revoked or suspended, is a Class A misdemeanor, in addition to civil penalties under IC 6-8.1-8.',
            'cite' => 'DOR General Tax Information Bulletin #102 (March 2023), citing IC 6-2.5-8',
        ],
        'late_filing' => [
            'summary' => 'Late-filed returns are subject to a penalty of up to 20% (minimum $5), plus interest.',
            'cite' => 'IC 6-8.1-10-2.1; https://www.in.gov/dor/i-am-a/business-corp/business-faq/',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales tax',
            'use tax',
            'withholding tax',
            'food and beverage tax',
            'county innkeeper\'s tax',
            'motor vehicle rental excise tax',
            'tire fee',
            'prepaid wireless service charge',
        ],
        'prerequisites' => 'FEIN; Indiana Secretary of State registration (and control number) for formal entities. Sole proprietors may register directly with DOR through INTIME.',
        'cite' => 'DOR New & Small Business Owners tax guide',
    ],
    'facts' => [
        [
            'text' => 'Since Jan. 1, 2024, Indiana\'s remote seller test is $100,000 in gross revenue from Indiana sales in the current or previous calendar year; the 200-transaction test was removed.',
            'source_url' => 'https://www.in.gov/dor/i-am-a/business-corp/remote-sellers/',
        ],
        [
            'text' => 'Each business location needs its own RRMC and its own $25 fee. Each food truck counts as a separate location.',
            'source_url' => 'https://www.in.gov/dor/files/new-small-business-handbook.pdf',
        ],
        [
            'text' => 'Sales tax must be filed and paid electronically, through INTIME or a third-party vendor, on Form ST-103.',
            'source_url' => 'https://www.in.gov/dor/files/new-small-business-handbook.pdf',
        ],
        [
            'text' => 'Merchants who file and pay on time keep a collection allowance of 0.73%, 0.53% or 0.26% of tax, based on prior fiscal-year liability.',
            'source_url' => 'https://www.in.gov/dor/files/new-small-business-handbook.pdf',
        ],
        [
            'text' => 'An RRMC is not a county vendor\'s license; vendors may also need a license from the county clerk.',
            'source_url' => 'https://www.in.gov/dor/files/new-small-business-handbook.pdf',
        ],
    ],
    'state_notes' => 'In Indiana, the sales tax permit is called a Registered Retail Merchant Certificate, or RRMC. The Indiana Department of Revenue (DOR) issues it. You apply online through INBiz by completing the BT-1 Business Tax Application. The fee is $25 for each business location, and each location gets its own certificate. DOR says the online application can take up to two business days to process. You then receive an Indiana Taxpayer Identification Number, which you use to set up an INTIME account for filing. The RRMC lasts two years. DOR renews it automatically at no charge if your returns and payments are current. Most sellers file Form ST-103 monthly, electronically. If your annual collections are under $1,000, DOR may assign annual filing. Monthly returns are due 30 days after the month ends, or 20 days if your average monthly tax is over $1,000. Indiana has a 7% state rate and no local sales tax. Remote sellers must register once Indiana sales pass $100,000 in the current or prior calendar year. The mistake to avoid: skipping returns in slow months. You must file a $0 return for every period, or DOR will estimate a bill and add penalties. Unfiled returns can also block your RRMC renewal.',
    'sources' => [
        [
            'title' => 'DOR: Sales Tax',
            'url' => 'https://www.in.gov/dor/i-am-a/business-corp/sales-tax/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DOR: Business FAQ',
            'url' => 'https://www.in.gov/dor/i-am-a/business-corp/business-faq/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DOR: Remote Sellers',
            'url' => 'https://www.in.gov/dor/i-am-a/business-corp/remote-sellers/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Indiana Tax Guide for New & Small Business Owners',
            'url' => 'https://www.in.gov/dor/files/new-small-business-handbook.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'General Tax Information Bulletin #102 (Transient Merchants)',
            'url' => 'https://www.in.gov/dor/files/gb102.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Indiana Code 6-2.5-8-1 (FindLaw)',
            'url' => 'https://codes.findlaw.com/in/title-6-taxation/in-code-sect-6-2-5-8-1/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Sales Tax Information Bulletin #89 (cross-reference)',
            'url' => 'https://secure.in.gov/dor/files/sib89.pdf',
            'accessed' => '2026-10-02',
        ],
    ],
];
