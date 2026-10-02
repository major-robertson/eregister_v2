<?php

/*
 * Utah: Utah State Tax Commission, Sales and Use Tax License. Researched
 * 2026-10-02 from files.tax.utah.gov, le.utah.gov, tap.utah.gov,
 * salestaxinstitute.com. Generated once from the EREG-13 sales tax
 * research; edit this file directly from now on.
 */

return [
    'state' => 'UT',
    'name' => 'Utah',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Utah State Tax Commission',
        'short' => 'the Tax Commission',
        'url' => 'https://tax.utah.gov/',
    ],
    'registration' => [
        'term' => 'Sales and Use Tax License',
        'portal' => [
            'name' => 'Taxpayer Access Point (TAP), "Apply for a tax account(s) - TC-69"',
            'url' => 'https://tap.utah.gov/',
        ],
        'form' => [
            'number' => 'TC-69',
            'title' => 'Utah State Business and Tax Registration',
            'pdf_url' => null,
            'online_only' => null,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'No license fee. The Tax Commission can require a bond before issuing a license when the applicant, or a fiduciary of the applicant, has a revoked license or unpaid sales tax.',
            'cite' => 'Utah Code 59-12-106(2)(e), (2)(l)',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'The license is valid until the holder stops doing business or changes business address, or the Commission revokes it. It is not assignable.',
            'cite' => 'Utah Code 59-12-106(2)(b)',
        ],
        'timing' => [
            'online' => null,
            'paper' => null,
            'temporary_number' => null,
            'summary' => 'No processing time was found on a Tax Commission page (tax.utah.gov blocks automated reads). Secondary sites say a few business days online.',
            'cite' => null,
        ],
        'number' => [
            'name' => 'Utah sales tax license number (sales and use tax account number)',
            'format' => null,
            'cite' => null,
        ],
    ],
    'nexus' => [
        'physical' => 'A seller has physical presence if it has or uses an office, warehouse or other place of business in Utah, keeps inventory there, regularly solicits orders there, regularly delivers there other than by common carrier or mail, or regularly leases or services property there; certain related-seller ties also create nexus.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => null,
            'period' => 'previous or current calendar year (gross revenue from sales into Utah must exceed $100,000)',
            'effective' => '2025-07-01',
            'cite' => 'Utah Code 59-12-107(2)(c), as amended by Laws 2025, ch. 293 (S.B. 47), which removed the 200-transaction test effective July 1, 2025; Utah Pub. 25 (Rev. 9/26)',
        ],
        'marketplace' => 'Since October 1, 2019 a marketplace facilitator over the same threshold (its own sales plus sales it facilitates) must collect and remit Utah tax on the sales it facilitates (Utah Code 59-12-107.6).',
    ],
    'filing' => [
        'frequencies' => 'monthly or quarterly',
        'rule' => 'Sales tax liability of $50,000 or more a year: monthly. Less than $50,000: may file quarterly. The Tax Commission sets the frequency and notifies the seller of changes. Monthly filers may keep a seller discount of 1.31%; quarterly filers may not.',
        'due_day' => 'last day of the month after the period',
        'zero_return_required' => true,
        'prepayments' => null,
        'cite' => 'Utah Pub. 25 (Rev. 9/26), p. 14; Utah Code 59-12-107(4)',
    ],
    'rates' => [
        'state_rate_pct' => 4.85,
        'local' => 'Statewide local option 1% and county option 0.25% apply everywhere, plus other local, transit and resort taxes that vary by location; grocery food is taxed at a 3% combined rate statewide',
        'sourcing' => 'origin: in-state sales of goods are sourced to the seller\'s fixed place of business; out-of-state sellers source to the customer\'s location',
        'cite' => 'Utah Pub. 25 (Rev. 9/26), pp. 4-6, Charts 1 and 2',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'Local sales taxes are part of the combined rate and are filed on the state return (TC-62S or TC-62M).',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Operating without a required license is a class B misdemeanor, with a fine of $500 to $1,000.',
            'cite' => 'Utah Code 59-12-106(2)(k); Utah Code 59-1-401(12)(b)',
        ],
        'late_filing' => [
            'summary' => 'Failure to file a tax-due return on time: the greater of $20 or up to 10% of the unpaid tax, graduated by how late it is. Another failure-to-pay penalty of the same size applies if tax is still unpaid 90 days after the due date. Monthly filers also lose the seller discount.',
            'cite' => 'Utah Pub. 25 (Rev. 9/26), p. 15; Utah Code 59-1-401',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales and use tax',
        ],
        'prerequisites' => null,
        'cite' => 'Utah Pub. 25 (Rev. 9/26), p. 2: apply in TAP under Apply for a tax account(s) - TC-69, the combined Utah State Business and Tax Registration',
    ],
    'facts' => [
        [
            'text' => 'Utah dropped the 200-transaction test for remote sellers on July 1, 2025 (S.B. 47, 2025). Only gross revenue over $100,000 in the previous or current calendar year now counts.',
            'source_url' => 'https://le.utah.gov/xcode/Title59/Chapter12/59-12-S107.html',
        ],
        [
            'text' => 'The state rate on grocery food is 1.75%, which with the mandatory local and county option taxes gives a 3% combined rate on grocery food everywhere in Utah.',
            'source_url' => 'https://files.tax.utah.gov/tax/forms/pubs/pub-25.pdf',
        ],
        [
            'text' => 'A seller must file a return every period, even with no tax liability.',
            'source_url' => 'https://files.tax.utah.gov/tax/forms/pubs/pub-25.pdf',
        ],
        [
            'text' => 'Sellers with one fixed Utah location file TC-62S. Sellers with several locations, no Utah location, or no fixed place of business file TC-62M with schedules.',
            'source_url' => 'https://files.tax.utah.gov/tax/forms/pubs/pub-25.pdf',
        ],
        [
            'text' => 'Utah is a Streamlined Sales Tax member state, so the SST exemption certificate is accepted for resale purchases.',
            'source_url' => 'https://tax.utah.gov/sales',
        ],
    ],
    'state_notes' => 'In Utah the permit is called a sales and use tax license. The Utah State Tax Commission issues it. You apply online in the Taxpayer Access Point (TAP) by choosing Apply for a tax account(s), which is the TC-69 Utah State Business and Tax Registration. Utah law says the license is issued without a fee. The Commission can require a bond if you or a business officer has unpaid Utah sales tax or a revoked license. The license stays valid until you stop doing business or move. Sellers without a Utah location need a license once their Utah sales pass $100,000 in the current or previous calendar year. Since July 1, 2025 the number of transactions no longer counts. The state rate is 4.85%, and local taxes add at least 1.25%. Grocery food is taxed at 3% statewide. If you owe $50,000 or more a year you file monthly; smaller sellers may file quarterly. Returns are due the last day of the month after the period, and all returns are filed electronically. The mistake to avoid: skipping a return when you had no sales. Utah requires a return every period, and a late tax-due return costs at least $20.',
    'sources' => [
        [
            'title' => 'Utah Publication 25, Sales and Use Tax General Information (Rev. 9/26)',
            'url' => 'https://files.tax.utah.gov/tax/forms/pubs/pub-25.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Utah Code 59-12-106',
            'url' => 'https://le.utah.gov/xcode/Title59/Chapter12/59-12-S106.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Utah Code 59-12-107 (effective 7/1/2025)',
            'url' => 'https://le.utah.gov/xcode/Title59/Chapter12/59-12-S107.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Utah Code 59-1-401',
            'url' => 'https://le.utah.gov/xcode/Title59/Chapter1/59-1-S401.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Utah Code 59-12-107.6 (effective 10/1/2019 version)',
            'url' => 'https://le.utah.gov/xcode/Title59/Chapter12/C59-12-S107.6_2019051420191001.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Utah Taxpayer Access Point',
            'url' => 'https://tap.utah.gov/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Sales Tax Institute: Utah removes transaction threshold (cross-check)',
            'url' => 'https://www.salestaxinstitute.com/resources/utah-economic-nexus-threshold-removed',
            'accessed' => '2026-10-02',
        ],
    ],
];
