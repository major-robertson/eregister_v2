<?php

/*
 * Virginia: Virginia Department of Taxation, Sales and Use Tax Certificate
 * of Registration. Researched 2026-10-02 from tax.virginia.gov,
 * law.lis.virginia.gov. Generated once from the EREG-13 sales tax
 * research; edit this file directly from now on.
 */

return [
    'state' => 'VA',
    'name' => 'Virginia',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Virginia Department of Taxation',
        'short' => 'Virginia Tax',
        'url' => 'https://www.tax.virginia.gov/',
    ],
    'registration' => [
        'term' => 'Sales and Use Tax Certificate of Registration',
        'portal' => [
            'name' => 'Virginia Tax online business registration (Online Services for Businesses)',
            'url' => 'https://www.tax.virginia.gov/register-business-virginia',
        ],
        'form' => [
            'number' => 'R-1',
            'title' => 'Business Registration Form',
            'pdf_url' => null,
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'No registration fee was found on Virginia Tax\'s pages or in the Code; the Tax Commissioner issues a certificate once the application is made.',
            'cite' => 'Va. Code 58.1-613(C)',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'The certificate does not need renewal. It expires if the holder stops doing business at that location, and can be suspended or revoked after a hearing.',
            'cite' => 'Va. Code 58.1-613(D), (F)',
        ],
        'timing' => [
            'online' => null,
            'paper' => null,
            'temporary_number' => null,
            'summary' => 'Online registration gives a 15-digit sales tax account number and the Certificate of Registration (Form ST-4), which can be printed from the Online Services account. No processing time is stated.',
            'cite' => 'https://www.tax.virginia.gov/retail-sales-and-use-tax',
        ],
        'number' => [
            'name' => 'Virginia sales tax account number',
            'format' => '15 digits',
            'cite' => 'https://www.tax.virginia.gov/retail-sales-and-use-tax',
        ],
    ],
    'nexus' => [
        'physical' => 'Any person engaging in business as a dealer in Virginia, for example by keeping an office, warehouse or other place of business or soliciting through in-state representatives, must hold a certificate of registration for each place of business (Va. Code 58.1-612, 58.1-613).',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => 200,
            'period' => 'previous or current calendar year',
            'effective' => '2019-07-01',
            'cite' => 'Va. Code 58.1-612(C)(10) and (11) (200 or more separate retail sales transactions); Virginia Tax, Remote Sellers, Marketplace Facilitators & Economic Nexus',
        ],
        'marketplace' => 'Since July 1, 2019 a marketplace facilitator over the same threshold must register and collect on its sellers\' Virginia sales unless it gets a waiver; sellers who sell only through such a facilitator generally need not collect (Va. Code 58.1-612.1; Virginia Tax economic nexus page).',
    ],
    'filing' => [
        'frequencies' => 'monthly or quarterly',
        'rule' => 'Monthly by law; the Tax Commissioner may assign a less frequent period, and Virginia Tax assigns monthly or quarterly by tax liability. The dollar line for quarterly filing is not stated on the pages reviewed.',
        'due_day' => '20th of the month after the period (quarterly: April 20, July 20, October 20, January 20)',
        'zero_return_required' => true,
        'prepayments' => null,
        'cite' => 'Va. Code 58.1-615(A); https://www.tax.virginia.gov/retail-sales-and-use-tax',
    ],
    'rates' => [
        'state_rate_pct' => 4.3,
        'local' => 'Statewide 1% local tax gives a 5.3% base; regional taxes bring it to 6% in Central Virginia, Hampton Roads and Northern Virginia, 6.3% in eight Southside counties, and 7% in James City County, Williamsburg and York County. Groceries are taxed at 1%.',
        'sourcing' => 'origin for in-state dealers (local tax is sourced to the dealer\'s place of business)',
        'cite' => 'https://www.tax.virginia.gov/retail-sales-and-use-tax; Va. Code 58.1-603',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'Local and regional sales taxes are filed on the state return. Va. Code 58.1-613 lets a local commissioner of the revenue accept certificate applications for the state, but it is the same state certificate.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Doing business as a dealer without a certificate of registration, or after it is suspended or revoked, is a Class 2 misdemeanor for the business and each corporate officer. Each day is a separate offense.',
            'cite' => 'Va. Code 58.1-613(E)',
        ],
        'late_filing' => [
            'summary' => '6% of the tax for the first month late plus 6% for each further month or part, up to 30%, with a $10 minimum that applies even when no tax is due.',
            'cite' => 'Va. Code 58.1-635(A)',
        ],
    ],
    'connected' => [
        'covers' => [
            'retail sales tax',
            'use tax',
            'employer withholding',
            'corporate or pass-through entity income tax',
        ],
        'prerequisites' => 'FEIN; some businesses must also register with the State Corporation Commission',
        'cite' => 'https://www.tax.virginia.gov/register-business-virginia',
    ],
    'facts' => [
        [
            'text' => 'Registered dealers must file a return every period even when no tax is due, and the $10 minimum late penalty applies to a late zero return.',
            'source_url' => 'https://law.lis.virginia.gov/vacode/title58.1/chapter6/section58.1-615/',
        ],
        [
            'text' => 'Food for home consumption (groceries) is taxed at a reduced 1% rate statewide.',
            'source_url' => 'https://www.tax.virginia.gov/retail-sales-and-use-tax',
        ],
        [
            'text' => 'The 2026 Appropriation Act lets every Virginia county and city adopt an extra 1% local sales tax for school construction (and, in Northern Virginia, transit) if voters approve it in a referendum. Before 2026 only nine localities had this option, so combined rates may rise in more places.',
            'source_url' => 'https://www.tax.virginia.gov/sites/default/files/inline-files/2026-legislative-summary.pdf',
        ],
        [
            'text' => 'The Certificate of Registration (Form ST-4) must be displayed at the registered location; a separate certificate is issued for each place of business.',
            'source_url' => 'https://law.lis.virginia.gov/vacode/title58.1/chapter6/section58.1-613/',
        ],
        [
            'text' => 'Virginia counts sales by commonly controlled persons together when deciding whether a dealer passes the $100,000 threshold.',
            'source_url' => 'https://law.lis.virginia.gov/vacode/title58.1/chapter6/section58.1-612/',
        ],
    ],
    'state_notes' => 'In Virginia you need a sales and use tax certificate of registration. The Virginia Department of Taxation, called Virginia Tax, issues it. Register online through Virginia Tax\'s business registration. If you cannot register online, mail Form R-1. When you finish, you get a 15-digit sales tax account number and the Certificate of Registration, Form ST-4. You can print it from your online account. You need a certificate for each place of business, displayed there. Sellers outside Virginia must register once they pass $100,000 in Virginia sales or 200 transactions in the current or previous calendar year. The general rate is 5.3%, which is 4.3% state and 1% local. Some regions add more, up to 7% in the Williamsburg area. Groceries are taxed at 1%. Virginia Tax assigns monthly or quarterly filing based on how much tax you owe. Returns are due on the 20th of the month after the period. The mistake to avoid: skipping a return when you had no sales. Virginia requires a return every period, and a late return costs at least $10 even when no tax is due.',
    'sources' => [
        [
            'title' => 'Virginia Tax: Retail Sales and Use Tax',
            'url' => 'https://www.tax.virginia.gov/retail-sales-and-use-tax',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Virginia Tax: Register your business',
            'url' => 'https://www.tax.virginia.gov/register-business-virginia',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Virginia Tax: Remote Sellers, Marketplace Facilitators & Economic Nexus',
            'url' => 'https://www.tax.virginia.gov/remote-sellers-marketplace-facilitators-economic-nexus',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Va. Code 58.1-612',
            'url' => 'https://law.lis.virginia.gov/vacode/title58.1/chapter6/section58.1-612/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Va. Code 58.1-613',
            'url' => 'https://law.lis.virginia.gov/vacode/title58.1/chapter6/section58.1-613/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Va. Code 58.1-615',
            'url' => 'https://law.lis.virginia.gov/vacode/title58.1/chapter6/section58.1-615/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Va. Code 58.1-635',
            'url' => 'https://law.lis.virginia.gov/vacode/title58.1/chapter6/section58.1-635/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Virginia Tax 2026 Legislative Summary',
            'url' => 'https://www.tax.virginia.gov/sites/default/files/inline-files/2026-legislative-summary.pdf',
            'accessed' => '2026-10-02',
        ],
    ],
];
