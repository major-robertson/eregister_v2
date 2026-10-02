<?php

/*
 * Ohio: Ohio Department of Taxation, Vendor's License (county or
 * transient); Seller's Use Tax License for out-of-state sellers.
 * Researched 2026-10-02 from tax.ohio.gov, codes.ohio.gov. Generated once
 * from the EREG-13 sales tax research; edit this file directly from now
 * on.
 */

return [
    'state' => 'OH',
    'name' => 'Ohio',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Ohio Department of Taxation',
        'short' => 'the Department of Taxation',
        'url' => 'https://tax.ohio.gov/',
    ],
    'registration' => [
        'term' => 'Vendor\'s License (county or transient); Seller\'s Use Tax License for out-of-state sellers',
        'portal' => [
            'name' => 'OH|TAX eServices',
            'url' => 'https://tax.ohio.gov/ohtax',
        ],
        'form' => [
            'number' => 'UT 1000',
            'title' => 'Seller\'s use tax application (paper; required only for businesses located outside the U.S.)',
            'pdf_url' => null,
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 5000,
            'summary' => '$50 for each fixed place of business (county vendor\'s license), raised from $25 for new applications on April 9, 2025. A fee for the out-of-state seller\'s use tax license is not stated by the Department.',
            'cite' => 'R.C. 5739.17(A) and (C); Ohio Department of Taxation, Sales and Use Tax page (Vendor\'s License Fee Increase)',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'The license stays active until it is closed with the Department; returns are due for every period until then.',
            'cite' => 'Ohio Department of Taxation, Sales and Use Tax page (closing a vendor\'s license)',
        ],
        'timing' => [
            'online' => 'Immediately through OH|TAX eServices',
            'paper' => null,
            'temporary_number' => false,
            'summary' => 'Prospective retailers may obtain a vendor\'s license immediately through OH|TAX eServices after setting up an eServices account. Out-of-state sellers can also get a seller\'s use tax license right away there. Businesses may instead apply with their county auditor.',
            'cite' => 'Ohio Department of Taxation, Sales and Use Tax page (Registration)',
        ],
        'number' => [
            'name' => 'Vendor\'s license number',
            'format' => 'Begins with the 2-digit county code (for example 25 for Franklin County); the related Ohio sales account number is 8 digits starting with 01-88',
            'cite' => 'Ohio Department of Taxation, Sales and Use Tax page (TeleFile instructions)',
        ],
    ],
    'nexus' => [
        'physical' => 'Any person or business with a physical presence in Ohio making taxable sales or providing taxable services must first obtain a vendor\'s license, one for each fixed location; sellers at shows and flea markets need a statewide transient vendor\'s license.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => 200,
            'period' => 'current or previous calendar year',
            'effective' => '2019-08-01',
            'cite' => 'Am. Sub. H.B. 166 (2019); Ohio Department of Taxation, Sales and Use Tax page (Remote Sellers)',
        ],
        'marketplace' => 'Marketplace facilitators meeting the $100,000 or 200-transaction test (counting their own and facilitated sales) must hold a seller\'s use tax license and collect on all facilitated Ohio sales, starting the first day of the first month at least 30 days after meeting it; the earliest start was September 1, 2019.',
    ],
    'filing' => [
        'frequencies' => 'monthly or semiannual for vendors and sellers (quarterly only for direct pay permit and consumer use tax accounts)',
        'rule' => 'Vendors, transient vendors and out-of-state sellers file Form UST-1 monthly. Semiannual filing may be authorized if tax liability is less than $1,200 per six-month period. Over $75,000 annual liability must pay electronically.',
        'due_day' => '23rd of the month after the period',
        'zero_return_required' => true,
        'prepayments' => null,
        'cite' => 'Ohio Department of Taxation, Sales and Use Tax page (Filing)',
    ],
    'rates' => [
        'state_rate_pct' => 5.75,
        'local' => 'Counties and regional transit authorities add up to 3% in 0.05% steps; combined rates cannot exceed 8.75%. Warren County rises from 6.75% to 7.25% on October 1, 2026.',
        'sourcing' => 'origin-based for most sales by Ohio vendors to Ohio customers; destination-based for remote sellers and marketplace-facilitated sales',
        'cite' => 'Ohio Department of Taxation, Sales and Use Tax page (Rates; Sourcing, ST 2009-03; R.C. 5741.05)',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'The vendor\'s license is a county license for each fixed place of business, but it is issued through OH|TAX eServices on the county auditor\'s behalf or by the county auditor. There is no separate city or county sales tax registration.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Selling at retail without a vendor\'s license is punishable by a fine of $25 to $100 (a fourth-degree felony for a repeat offense). Selling as a transient vendor without a license: $100 to $500 or up to 10 days in jail for a first offense.',
            'cite' => 'R.C. 5739.31(A); R.C. 5739.99(C)',
        ],
        'late_filing' => [
            'summary' => 'An additional charge of up to $50 or 10% of the tax due for the period, whichever is greater, for each late or unpaid return, plus interest. Late filers also lose the 0.75% timely-filing discount.',
            'cite' => 'R.C. 5739.12(B) and (D)',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales tax',
            'seller\'s use tax (out-of-state sellers)',
        ],
        'prerequisites' => 'An OH|TAX eServices account, with FEIN or SSN and business contact details.',
        'cite' => 'Ohio Department of Taxation, Sales and Use Tax page (Filing)',
    ],
    'facts' => [
        [
            'text' => 'The vendor\'s license fee for new applications rose from $25 to $50 per fixed place of business on April 9, 2025.',
            'source_url' => 'https://tax.ohio.gov/business/sales-and-use-tax',
        ],
        [
            'text' => 'From returns due January 1, 2026, the 0.75% discount for filing and paying on time is capped at $750 per vendor\'s license for each month covered by the return (motor vehicle sales excepted).',
            'source_url' => 'https://codes.ohio.gov/ohio-revised-code/section-5739.12',
        ],
        [
            'text' => 'Ohio keeps origin sourcing for most in-state sales by Ohio vendors, but marketplace facilitators must source facilitated sales to where the customer receives the item.',
            'source_url' => 'https://tax.ohio.gov/business/sales-and-use-tax',
        ],
        [
            'text' => 'Each fixed location needs its own county vendor\'s license; a transient vendor\'s license covers sales at shows and flea markets statewide and must be displayed.',
            'source_url' => 'https://codes.ohio.gov/ohio-revised-code/section-5739.17',
        ],
        [
            'text' => 'Sellers who only sell through a marketplace that collects Ohio tax for them do not need a seller\'s use tax license.',
            'source_url' => 'https://tax.ohio.gov/business/sales-and-use-tax',
        ],
    ],
    'state_notes' => 'In Ohio the sales tax registration is called a vendor\'s license. The Ohio Department of Taxation runs it. If you sell from a fixed location in Ohio, you need a county vendor\'s license for each location. If you sell at shows or flea markets, you need a transient vendor\'s license. Out-of-state sellers get a seller\'s use tax license instead. You can apply in OH|TAX eServices and get the license right away, or apply through your county auditor. The fee is $50 per fixed location, up from $25 in April 2025. Remote sellers must register once they pass $100,000 in Ohio sales or 200 transactions in the current or prior calendar year. After you register, you file Form UST-1 monthly, due on the 23rd. Small sellers with less than $1,200 of tax per six months may be allowed to file semiannually. File every period until you close the license, even with no sales. A late return can cost $50 or 10% of the tax, whichever is greater. Filing on time earns a 0.75% discount, now capped at $750 a month. The one mistake to avoid: opening a second store under your first license. Each fixed location needs its own.',
    'sources' => [
        [
            'title' => 'Ohio Department of Taxation, Sales and Use Tax (registration, remote sellers, marketplace, filing, rates)',
            'url' => 'https://tax.ohio.gov/business/sales-and-use-tax',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'R.C. 5739.12 (returns, discount, additional charge)',
            'url' => 'https://codes.ohio.gov/ohio-revised-code/section-5739.12',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'R.C. 5739.17 (vendor\'s licenses, fee)',
            'url' => 'https://codes.ohio.gov/ohio-revised-code/section-5739.17',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'R.C. 5739.31 (selling without a license)',
            'url' => 'https://codes.ohio.gov/ohio-revised-code/section-5739.31',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'R.C. 5739.99 (penalties)',
            'url' => 'https://codes.ohio.gov/ohio-revised-code/section-5739.99',
            'accessed' => '2026-10-02',
        ],
    ],
];
