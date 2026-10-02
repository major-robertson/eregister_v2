<?php

/*
 * Georgia: Georgia Department of Revenue, Sales and Use Tax Certificate of
 * Registration (sales tax number). Researched 2026-10-02 from
 * dor.georgia.gov, georgia.gov, law.cornell.edu, codes.findlaw.com,
 * law.justia.com. Generated once from the EREG-13 sales tax research; edit
 * this file directly from now on.
 */

return [
    'state' => 'GA',
    'name' => 'Georgia',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Georgia Department of Revenue',
        'short' => 'DOR',
        'url' => 'https://dor.georgia.gov/',
    ],
    'registration' => [
        'term' => 'Sales and Use Tax Certificate of Registration (sales tax number)',
        'portal' => [
            'name' => 'Georgia Tax Center (GTC)',
            'url' => 'https://gtc.dor.ga.gov/',
        ],
        'form' => [
            'number' => 'CRF-002',
            'title' => 'State Tax Registration Application (paper; DOR directs registrants to GTC)',
            'pdf_url' => null,
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'No registration fee: O.C.G.A. 48-8-59 sets no fee for a certificate of registration, except $1 to reissue one after suspension or revocation.',
            'cite' => 'O.C.G.A. 48-8-59(d)',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'Registration does not need renewal; it stays in effect while the business exists without a change in ownership or structure.',
            'cite' => 'https://dor.georgia.gov/taxes/business-taxes/sales-use-tax/sales-and-use-tax-registration-faq',
        ],
        'timing' => [
            'online' => 'about 15 minutes by email (DOR FAQ); georgia.gov says within a few hours',
            'paper' => null,
            'temporary_number' => false,
            'summary' => 'DOR\'s FAQ says that after an online submission \'you should receive your specific tax account number within 15 minutes by email.\' Georgia.gov says within a few hours, and longer for alcohol or tobacco sellers whose accounts need review.',
            'cite' => 'https://dor.georgia.gov/taxes/business-taxes/sales-use-tax/sales-and-use-tax-registration-faq; https://georgia.gov/register-business-georgia-department-revenue',
        ],
        'number' => [
            'name' => 'Georgia sales tax number',
            'format' => null,
            'cite' => null,
        ],
    ],
    'nexus' => [
        'physical' => 'Every person who wants to do business as a seller or dealer in Georgia must apply for a certificate of registration for each place of business (O.C.G.A. 48-8-59(a)).',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => 200,
            'period' => 'previous or current calendar year (retail sales of tangible personal property delivered into Georgia; either test)',
            'effective' => '2020-01-01',
            'cite' => 'O.C.G.A. 48-8-2 (definition of dealer); DOR Policy Bulletin SUT-2019-02, https://dor.georgia.gov/media/35301/download',
        ],
        'marketplace' => 'Marketplace facilitators that meet the remote seller thresholds must register for a Marketplace Facilitator Sales and Use Tax Account and collect on sales they facilitate, effective April 1, 2020 (https://dor.georgia.gov/how-register-sales-and-use-tax-account; https://dor.georgia.gov/marketplace-facilitators).',
    ],
    'filing' => [
        'frequencies' => 'monthly; quarterly or annual on written request after six months',
        'rule' => 'Monthly for the first six months after registration. After that, a dealer whose liability has averaged under $200 a month for six consecutive months may be allowed to file quarterly, and under $50 a month to file annually, on written request and approval.',
        'due_day' => '20th of the month after the period',
        'zero_return_required' => true,
        'prepayments' => 'Dealers whose prior-year liability exceeded $60,000 (excluding local taxes) must remit prepaid estimated tax of 50% of the estimated tax.',
        'cite' => 'Ga. Comp. R. & Regs. r. 560-12-1-.22, https://www.law.cornell.edu/regulations/georgia/Ga-Comp-R-Regs-R-560-12-1-.22; https://dor.georgia.gov/taxes/business-taxes/sales-use-tax/file-pay',
    ],
    'rates' => [
        'state_rate_pct' => 4.0,
        'local' => 'County and special district local sales taxes (LOST, SPLOST, ESPLOST and others) add to the 4% state rate; DOR collects them on the state return.',
        'sourcing' => 'destination (local rate where the goods are delivered); not confirmed on a DOR page read today',
        'cite' => 'https://dor.georgia.gov/taxes/sales-use-tax',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => null,
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => null,
            'cite' => null,
        ],
        'late_filing' => [
            'summary' => 'Failing to file or pay: 5% or $5 (whichever is greater) for the first 30 days, plus 5% or $5 for each further 30 days, up to 25% or $25 in total, plus interest.',
            'cite' => 'O.C.G.A. 48-8-66',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales and use tax',
            'other Georgia DOR taxes such as withholding through the same GTC registration',
        ],
        'prerequisites' => 'An EIN (or SSN for a sole proprietor), business structure, legal name, NAICS code, date of first Georgia sales, and officer and responsible-party details.',
        'cite' => 'https://georgia.gov/register-business-georgia-department-revenue',
    ],
    'facts' => [
        [
            'text' => 'Georgia issues one certificate of registration for a business operating in more than one county, covering all its Georgia operations, though each place of business displays a certificate.',
            'source_url' => 'https://codes.findlaw.com/ga/title-48-revenue-and-taxation/ga-code-sect-48-8-59/',
        ],
        [
            'text' => 'Dealers owing more than $500 must file and pay electronically; paper filers use Form ST-3.',
            'source_url' => 'https://dor.georgia.gov/taxes/business-taxes/sales-use-tax/file-pay',
        ],
        [
            'text' => 'Georgia lowered its remote seller dollar threshold from $250,000 to $100,000 on January 1, 2020, kept the 200-transaction test, and repealed the notice-and-reporting alternative in 2019.',
            'source_url' => 'https://dor.georgia.gov/media/35301/download',
        ],
        [
            'text' => 'A return is required every period even when no tax is due or no sales were made.',
            'source_url' => 'https://dor.georgia.gov/taxes/business-taxes/sales-use-tax/file-pay',
        ],
        [
            'text' => 'Georgia buyers use Form ST-5 for resale purchases; Georgia also accepts the Streamlined certificate.',
            'source_url' => 'https://dor.georgia.gov/taxes/business-taxes/sales-use-tax/nontaxable-sales',
        ],
    ],
    'state_notes' => 'In Georgia, you register for a sales and use tax number with the Georgia Department of Revenue (DOR). DOR issues a Certificate of Registration. You apply online through the Georgia Tax Center (GTC). DOR says you should get your account number by email within about 15 minutes; alcohol and tobacco sellers can take longer. There is no fee, and the registration does not need renewal. Have your EIN, NAICS code, first sales date and officer details ready. You file monthly for at least your first six months. After that, small accounts can ask in writing to file quarterly or yearly. Returns are due on the 20th, and you must file even with no sales. Dealers owing more than $500 must file and pay online. The state rate is 4%. County and special district taxes are added and reported on the same return. The one mistake to avoid: letting a return go late. Georgia charges 5% of the tax for each month late, up to 25%.',
    'sources' => [
        [
            'title' => 'DOR: Sales and Use Tax Registration FAQ',
            'url' => 'https://dor.georgia.gov/taxes/business-taxes/sales-use-tax/sales-and-use-tax-registration-faq',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DOR: How to Register a Sales and Use Tax Account',
            'url' => 'https://dor.georgia.gov/how-register-sales-and-use-tax-account',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Georgia.gov: Register a Business with Georgia Department of Revenue',
            'url' => 'https://georgia.gov/register-business-georgia-department-revenue',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DOR: Out-of-State Sellers',
            'url' => 'https://dor.georgia.gov/taxes/sales-use-tax/out-state-sellers',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DOR Policy Bulletin SUT-2019-02: Remote sellers',
            'url' => 'https://dor.georgia.gov/media/35301/download',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DOR: File and Pay (sales and use tax)',
            'url' => 'https://dor.georgia.gov/taxes/business-taxes/sales-use-tax/file-pay',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Ga. Comp. R. & Regs. r. 560-12-1-.22 (Cornell LII)',
            'url' => 'https://www.law.cornell.edu/regulations/georgia/Ga-Comp-R-Regs-R-560-12-1-.22',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'O.C.G.A. 48-8-59 (FindLaw)',
            'url' => 'https://codes.findlaw.com/ga/title-48-revenue-and-taxation/ga-code-sect-48-8-59/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'O.C.G.A. 48-8-66 (Justia, via search)',
            'url' => 'https://law.justia.com/codes/georgia/title-48/chapter-8/article-1/part-2/section-48-8-66/',
            'accessed' => '2026-10-02',
        ],
    ],
];
