<?php

/*
 * South Carolina: South Carolina Department of Revenue, Retail License.
 * Researched 2026-10-02 from dor.sc.gov, scstatehouse.gov. Generated once
 * from the EREG-13 sales tax research; edit this file directly from now
 * on.
 */

return [
    'state' => 'SC',
    'name' => 'South Carolina',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'South Carolina Department of Revenue',
        'short' => 'the SCDOR',
        'url' => 'https://dor.sc.gov/',
    ],
    'registration' => [
        'term' => 'Retail License',
        'portal' => [
            'name' => 'MyDORWAY Business Tax Application',
            'url' => 'https://dor.sc.gov/mydorway',
        ],
        'form' => [
            'number' => null,
            'title' => 'Business Tax Application (MyDORWAY)',
            'pdf_url' => null,
            'online_only' => true,
        ],
        'fee' => [
            'amount_cents' => 5000,
            'summary' => '$50 non-refundable license tax for each retail location (and for a transient or temporary business); $20 for artists and craftsmen selling their own work at arts and crafts shows. The application is not processed without payment.',
            'cite' => 'S.C. Code 12-36-510(A); SCDOR, Licensing (Retail License)',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'Retail licenses do not expire, but must be updated if the business location changes. A remote seller that closes its license pays another $50 to get a new one.',
            'cite' => 'SCDOR, Licensing (Retail License); SCDOR, Remote Sellers',
        ],
        'timing' => [
            'online' => 'Up to 5 business days',
            'paper' => null,
            'temporary_number' => false,
            'summary' => 'Apply online with the MyDORWAY Business Tax Application. SCDOR says to allow up to 5 business days for processing and notifies you by email once the application is approved.',
            'cite' => 'SCDOR, Apply for a Business Tax Account',
        ],
        'number' => [
            'name' => 'South Carolina Retail License Number (File Number)',
            'format' => '9 digits',
            'cite' => '2026-10-01 resale research packet (SCDOR verify-a-retail-license guidance)',
        ],
    ],
    'nexus' => [
        'physical' => 'Every person engaged in retail sales in South Carolina, including online-only sellers shipping to South Carolina addresses and businesses with an office, warehouse or agent in the state, needs a Retail License for each retail location before making sales.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => null,
            'period' => 'previous or current calendar year (gross revenue from sales into South Carolina)',
            'effective' => '2018-11-01',
            'cite' => 'SCDOR, Remote Sellers page (economic nexus met on or after October 1, 2018; no collection required on sales before November 1, 2018)',
        ],
        'marketplace' => 'Since April 26, 2019, a marketplace facilitator is a retailer under South Carolina law and must remit sales and use tax on all sales made through its marketplace (SCDOR Information Letter #19-14).',
    ],
    'filing' => [
        'frequencies' => 'monthly; quarterly or annual with SCDOR approval',
        'rule' => 'Returns (Form ST-3 with local Schedule ST-389) are filed monthly. Quarterly filing may be authorized when tax does not exceed $100 a month; quarterly or annual filing requires a request to SCDOR. Liability of $15,000 or more per period must be filed and paid electronically.',
        'due_day' => '20th of the month after the period (annual returns due January 20)',
        'zero_return_required' => true,
        'prepayments' => null,
        'cite' => 'S.C. Code 12-36-2570 and 12-36-2580; SCDOR, Sales & Use Tax page (Due dates); SCDOR, Remote Sellers FAQ',
    ],
    'rates' => [
        'state_rate_pct' => 6.0,
        'local' => 'Counties may add local option sales taxes, typically 1% each, with voter approval; rates by municipality are in SCDOR\'s ST-575.',
        'sourcing' => 'destination-based: local tax follows the point of delivery',
        'cite' => 'SCDOR, Sales & Use Tax page',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'Local sales and use taxes administered by SCDOR are reported on the same return (ST-389). A county or city business license is a separate requirement, and some local hospitality taxes are paid directly to the county or city.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Doing business without a retail license, or after it is suspended, is a misdemeanor punishable by a fine of up to $200, up to 30 days in jail, or both. Failing to obtain the license or pay the license tax also carries a penalty of up to $500.',
            'cite' => 'S.C. Code 12-36-560 and 12-36-570',
        ],
        'late_filing' => [
            'summary' => 'Failure to file: 5% of the tax for each month or part of a month late, up to 25%. Failure to pay: 0.5% a month, up to 25%.',
            'cite' => 'S.C. Code 12-54-43(C) and (D)',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales and use tax (Retail License)',
            'withholding tax',
            'accommodations tax',
            'other SCDOR business tax accounts on the same application',
        ],
        'prerequisites' => 'Corporations, LLCs, LLPs and LPs must register with the South Carolina Secretary of State and have an FEIN; partnerships and fiduciaries need an FEIN.',
        'cite' => 'SCDOR, Apply for a Business Tax Account',
    ],
    'facts' => [
        [
            'text' => 'A Retail License is not the same as a county or city business license, which some local governments require separately.',
            'source_url' => 'https://dor.sc.gov/businesses/apply-business-tax-account/licensing-retail-license',
        ],
        [
            'text' => 'Shoppers aged 85 and older get a 1% reduction in the state sales tax on personal purchases, and retailers must post a sign telling them so.',
            'source_url' => 'https://dor.sc.gov/tax/sales',
        ],
        [
            'text' => 'South Carolina\'s remote seller test is $100,000 of gross revenue in the previous or current calendar year, with no transaction count.',
            'source_url' => 'https://dor.sc.gov/remotesellers',
        ],
        [
            'text' => 'Each retail location needs its own Retail License; MyDORWAY lets multi-location sellers file one consolidated return.',
            'source_url' => 'https://dor.sc.gov/tax/sales',
        ],
        [
            'text' => 'South Carolina\'s annual sales tax holiday on clothing, computers and school supplies falls on the first Friday, Saturday and Sunday in August.',
            'source_url' => 'https://dor.sc.gov/remotesellers',
        ],
    ],
    'state_notes' => 'In South Carolina you need a Retail License to make retail sales. The South Carolina Department of Revenue (SCDOR) issues it. You apply online with the Business Tax Application in MyDORWAY. The fee is $50 for each retail location, paid when you apply, and it is not refundable. SCDOR says to allow up to five business days and emails you when the application is approved. The license does not expire. If you are an LLC or corporation, register with the Secretary of State and get an FEIN first. Remote sellers need a license once their South Carolina sales pass $100,000 in the current or prior calendar year. There is no transaction count. After you register, you file Form ST-3 monthly, with Schedule ST-389 for local taxes, due on the 20th of the following month. Small accounts can ask SCDOR to file quarterly or yearly. File a return every period, even with zero sales. Late filing costs 5% a month, up to 25%. The one mistake to avoid: assuming one license covers every store. Each retail location needs its own license, and selling without one is a misdemeanor.',
    'sources' => [
        [
            'title' => 'SCDOR, Sales & Use Tax',
            'url' => 'https://dor.sc.gov/tax/sales',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'SCDOR, Licensing (Retail License)',
            'url' => 'https://dor.sc.gov/businesses/apply-business-tax-account/licensing-retail-license',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'SCDOR, Apply for a Business Tax Account',
            'url' => 'https://dor.sc.gov/businesses/apply-business-tax-account',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'SCDOR, Remote Sellers',
            'url' => 'https://dor.sc.gov/remotesellers',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'SCDOR Information Letter #19-14, Marketplace Facilitator (effective April 26, 2019)',
            'url' => 'https://dor.sc.gov/resources-site/lawandpolicy/Advisory%20Opinions/IL19-14.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'S.C. Code Title 12 Chapter 36 (12-36-510, -560, -570, -2570, -2580)',
            'url' => 'https://www.scstatehouse.gov/code/t12c036.php',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'S.C. Code Title 12 Chapter 54 (12-54-43)',
            'url' => 'https://www.scstatehouse.gov/code/t12c054.php',
            'accessed' => '2026-10-02',
        ],
    ],
];
