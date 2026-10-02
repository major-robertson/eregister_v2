<?php

/*
 * Texas: Texas Comptroller of Public Accounts, Sales and Use Tax Permit.
 * Researched 2026-10-02 from comptroller.texas.gov, tcss.legis.texas.gov,
 * law.cornell.edu. Generated once from the EREG-13 sales tax research;
 * edit this file directly from now on.
 */

return [
    'state' => 'TX',
    'name' => 'Texas',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Texas Comptroller of Public Accounts',
        'short' => 'the Comptroller',
        'url' => 'https://comptroller.texas.gov/',
    ],
    'registration' => [
        'term' => 'Sales and Use Tax Permit',
        'portal' => [
            'name' => 'Comptroller eSystems (Apply for Permit)',
            'url' => 'https://security.app.cpa.state.tx.us/',
        ],
        'form' => [
            'number' => 'AP-201',
            'title' => 'Texas Application for Sales Tax Permit and/or Use Tax Permit',
            'pdf_url' => 'https://comptroller.texas.gov/forms/ap-201.pdf',
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'No fee for the permit. The Comptroller may require a security bond, and can require one later from a permit holder who becomes delinquent.',
            'cite' => 'Comptroller Sales Tax Permit FAQ (https://comptroller.texas.gov/taxes/sales/faq/permit.php); Tex. Tax Code 151.251',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'The permit has no renewal. It is valid only for the holder and the place of business shown on it, cannot be transferred, and can be suspended or revoked for noncompliance.',
            'cite' => 'Tex. Tax Code 151.201, 151.203; Comptroller Sales Tax Permit FAQ',
        ],
        'timing' => [
            'online' => '2 to 3 weeks',
            'paper' => 'about 4 weeks after the Comptroller receives a complete, signed AP-201',
            'temporary_number' => false,
            'summary' => 'The Comptroller says to allow 2 to 3 weeks to receive the permit. The paper AP-201 says about four weeks. A temporary permit exists only to give an applicant time to post required security, not as an instant number.',
            'cite' => 'https://comptroller.texas.gov/taxes/permit/; AP-201 (Rev. 7-26/31) instructions; Tex. Tax Code 151.252',
        ],
        'number' => [
            'name' => 'Texas taxpayer number',
            'format' => '11 digits',
            'cite' => 'AP-201 (Rev. 7-26/31), item 12 ("11-digit Texas Taxpayer Number")',
        ],
    ],
    'nexus' => [
        'physical' => 'A seller engaged in business in Texas, for example through an office, warehouse or other location, in-state representatives, or property in the state, must hold a permit for each active place of business (Tex. Tax Code 151.107, 151.201).',
        'economic' => [
            'revenue_usd' => 500000,
            'transactions' => null,
            'period' => 'preceding twelve calendar months (total Texas revenue); permit and collection required no later than the first day of the fourth month after the month the $500,000 is exceeded',
            'effective' => '2019-10-01',
            'cite' => '34 TAC 3.286; Comptroller Remote Sellers page (https://comptroller.texas.gov/taxes/sales/remote-sellers.php)',
        ],
        'marketplace' => 'Since October 1, 2019 a marketplace provider must collect and remit state and local tax on all sales it processes for marketplace sellers and certify that to them; a remote seller who sells only through such a provider does not need a Texas permit (Tex. Tax Code 151.0242, added by HB 1525).',
    ],
    'filing' => [
        'frequencies' => 'monthly, quarterly or yearly',
        'rule' => 'Monthly by default. Quarterly if the taxpayer owes less than $500 a month or $1,500 a quarter. Yearly, with Comptroller authorization, if state tax is less than $1,000 a year. A quarterly prepayment option also exists (Tax Code 151.424).',
        'due_day' => '20th of the month after the period (quarterly: April 20, July 20, October 20, January 20; yearly: January 20)',
        'zero_return_required' => true,
        'prepayments' => 'Optional: a 1.25% discount for prepaying, on top of the 0.5% timely filing discount',
        'cite' => 'Tex. Tax Code 151.401, 151.402; 34 TAC 3.286; Comptroller Sales and Use Tax page and Permit FAQ',
    ],
    'rates' => [
        'state_rate_pct' => 6.25,
        'local' => 'Up to 2% local tax, for a maximum combined rate of 8.25%; remote sellers may elect a single local use tax rate (1.75%)',
        'sourcing' => 'origin for in-state sellers (local tax follows the seller\'s place of business); destination for out-of-state sellers',
        'cite' => 'Tex. Tax Code 151.051, 151.0595; Comptroller Sales and Use Tax page; Comptroller publication 94-105, Local Sales and Use Tax Collection',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => null,
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Doing business as a retailer without a permit, or after a permit is suspended, is a Class C misdemeanor for a first offense, rising to Class B and Class A for repeat offenses. Each day is a separate offense.',
            'cite' => 'Tex. Tax Code 151.708',
        ],
        'late_filing' => [
            'summary' => '5% of the tax due if late, plus another 5% after 30 days (minimum $1), and a flat $50 penalty for each late report even if no tax was due. Interest starts 60 days after the due date.',
            'cite' => 'Tex. Tax Code 151.703',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales tax',
            'use tax',
            '9-1-1 emergency communications fees',
            'prepaid wireless 9-1-1 fee',
            'off-road heavy-duty diesel equipment surcharge',
        ],
        'prerequisites' => 'SSN for a sole owner (applicants without one must use paper AP-201); FEIN or SSNs for partners; Texas Secretary of State file number for corporations and LLCs; NAICS code',
        'cite' => 'AP-201 (Rev. 7-26/31); https://comptroller.texas.gov/taxes/permit/',
    ],
    'facts' => [
        [
            'text' => 'Data processing services, which include software as a service, are taxable in Texas on 80% of the charge; 20% is exempt.',
            'source_url' => 'https://comptroller.texas.gov/taxes/publications/94-127.php',
        ],
        [
            'text' => 'A remote seller can elect the single local use tax rate (currently 1.75%) instead of the combined local rate at each delivery address. Marketplace providers and in-state businesses cannot use it.',
            'source_url' => 'https://comptroller.texas.gov/taxes/sales/remote-sellers.php',
        ],
        [
            'text' => 'A permit holder must file a return every period, even with no taxable sales or purchases to report. A late report costs a flat $50 penalty even when no tax was due.',
            'source_url' => 'https://comptroller.texas.gov/taxes/sales/faq/permit.php',
        ],
        [
            'text' => 'Applicants must be at least 18; a parent or legal guardian may apply for a minor. A sole owner without a Social Security number must apply on paper form AP-201.',
            'source_url' => 'https://comptroller.texas.gov/taxes/permit/',
        ],
        [
            'text' => 'The permit must be displayed at the place of business it covers, and a seller needs one for each active place of business.',
            'source_url' => 'https://comptroller.texas.gov/taxes/sales/faq/permit.php',
        ],
    ],
    'state_notes' => 'In Texas the permit is called a Sales and Use Tax Permit. The Texas Comptroller of Public Accounts issues it. Most businesses apply online through the Comptroller\'s eSystems. You can also use paper form AP-201, the Texas Application for Sales Tax Permit and/or Use Tax Permit. The Comptroller says to allow 2 to 3 weeks for the permit; the paper form says about four weeks. There is no fee, but the Comptroller can ask for a security bond. You need a permit for each place of business, and you must display it there. Out-of-state sellers with no physical presence need a permit once Texas revenue passes $500,000 in the preceding twelve calendar months. Sellers who sell only through a marketplace that collects Texas tax do not need one. The state rate is 6.25%, and local tax can bring the total to 8.25%. The Comptroller assigns monthly, quarterly or yearly filing based on how much tax you owe. Returns are due on the 20th of the month after the period. The mistake to avoid: skipping a return because you had no sales. Texas charges a $50 penalty for every late report, even when no tax is due.',
    'sources' => [
        [
            'title' => 'Texas Comptroller: Sales and Use Tax Permit',
            'url' => 'https://comptroller.texas.gov/taxes/permit/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Texas Comptroller: Sales Tax Permit FAQ',
            'url' => 'https://comptroller.texas.gov/taxes/sales/faq/permit.php',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Texas Comptroller: Sales and Use Tax',
            'url' => 'https://comptroller.texas.gov/taxes/sales/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Texas Comptroller: Remote Sellers',
            'url' => 'https://comptroller.texas.gov/taxes/sales/remote-sellers.php',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Texas Comptroller: Marketplace Providers and Sellers',
            'url' => 'https://comptroller.texas.gov/taxes/sales/marketplace-providers-sellers.php',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Texas Comptroller: Resale Certificate FAQ',
            'url' => 'https://comptroller.texas.gov/taxes/sales/faq/resale.php',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Form AP-201 (Rev. 7-26/31)',
            'url' => 'https://comptroller.texas.gov/forms/ap-201.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Texas Tax Code chapter 151',
            'url' => 'https://tcss.legis.texas.gov/resources/TX/htm/TX.151.htm',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Texas Comptroller publication 94-127, Data Processing Services are Taxable',
            'url' => 'https://comptroller.texas.gov/taxes/publications/94-127.php',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Texas Comptroller publication 94-105, Local Sales and Use Tax Collection',
            'url' => 'https://comptroller.texas.gov/taxes/publications/94-105.php',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Cornell LII: 34 Tex. Admin. Code 3.286 (cross-check for yearly filing)',
            'url' => 'https://www.law.cornell.edu/regulations/texas/34-Tex-Admin-Code-SS-3-286',
            'accessed' => '2026-10-02',
        ],
    ],
];
