<?php

/*
 * Kansas: Kansas Department of Revenue, Retailers' Sales Tax Registration
 * Certificate. Researched 2026-10-02 from ksrevenue.gov, ksrevisor.gov.
 * Generated once from the EREG-13 sales tax research; edit this file
 * directly from now on.
 */

return [
    'state' => 'KS',
    'name' => 'Kansas',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Kansas Department of Revenue',
        'short' => 'KDOR',
        'url' => 'https://www.ksrevenue.gov/',
    ],
    'registration' => [
        'term' => 'Retailers\' Sales Tax Registration Certificate',
        'portal' => [
            'name' => 'KDOR Customer Service Center (KCSC)',
            'url' => 'https://www.kdor.ks.gov/Apps/KCSC/Secure/Default.aspx',
        ],
        'form' => [
            'number' => 'CR-16',
            'title' => 'Business Tax Application (instructions in Pub. KS-1216)',
            'pdf_url' => 'https://www.ksrevenue.gov/pdf/cr16.pdf',
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'No fee and no bond at initial registration for sales and use tax. KDOR may require a bond later. (Cigarette retailer licenses and vending permits carry separate fees.)',
            'cite' => 'Pub. KS-1216 (Rev. 9-11-25), Required Bonds and Fees; https://www.ksrevenue.gov/faqs-taxbusreg.html',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'The registration is valid until canceled at the owner\'s request or revoked by the Director of Taxation for failure to file or pay, or to post a bond on request.',
            'cite' => 'Pub. KS-1216, Your Certificate of Registration; K.S.A. 79-3608',
        ],
        'timing' => [
            'online' => '2 to 3 business days',
            'paper' => '2 to 3 weeks',
            'temporary_number' => false,
            'summary' => 'KDOR asks for 2 to 3 business days to process online applications and 2 to 3 weeks for mailed or faxed applications, and recommends applying 3 to 4 weeks before the start date. Online filers get a confirmation number and account number at the end of the application. In-person registration by appointment gives same-day service.',
            'cite' => 'Pub. KS-1216; https://www.ksrevenue.gov/faqs-taxbusreg.html',
        ],
        'number' => [
            'name' => 'Kansas sales tax registration (account) number',
            'format' => '004-XXXXXXXXXF-0X: tax type 004, the FEIN (or an 8-digit K or A number) followed by F, then a 2-digit suffix, e.g. 004-481880059F-01',
            'cite' => 'resale research 2026-10-01 (KDOR registration number check)',
        ],
    ],
    'nexus' => [
        'physical' => 'Anyone who sells goods or admissions or provides taxable services in Kansas, including taxable labor services such as repair and remodeling of commercial property, must register; pure wholesalers do not need a sales tax number.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => null,
            'period' => 'current or preceding calendar year (cumulative gross receipts from sales to Kansas customers); register within 30 days of exceeding',
            'effective' => '2021-07-01',
            'cite' => 'K.S.A. 79-3702(h)(1)(G); KDOR Notice 21-17; Pub. KS-1510',
        ],
        'marketplace' => 'Since July 1, 2021 (2021 S.B. 50), a marketplace facilitator with over $100,000 in Kansas sales, including facilitated sales, must register within 30 days and collect on all Kansas sales it facilitates (KDOR Notice 21-14; Pub. KS-1510).',
    ],
    'filing' => [
        'frequencies' => 'monthly, quarterly or annual by annual tax liability',
        'rule' => '$0 to $1,000 a year: annual. $1,000.01 to $5,000: quarterly. Over $5,000: monthly. Seasonal businesses file monthly during their operating period.',
        'due_day' => '25th of the month after the period (annual returns due January 25)',
        'zero_return_required' => true,
        'prepayments' => null,
        'cite' => 'https://www.ksrevenue.gov/faqs-taxsales.html; Pub. KS-1510',
    ],
    'rates' => [
        'state_rate_pct' => 6.5,
        'local' => 'City taxes up to 3% and county taxes up to 1% (local rates range from 0.10% to 3%); state rate on food and food ingredients is 0% since Jan. 1, 2025, but local rates still apply to food.',
        'sourcing' => 'destination: the combined rate where the customer takes delivery or the service is performed',
        'cite' => 'Pub. KS-1510 (Rev. 11/24); Pub. KS-1216; KDOR Notice 24-21',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'No separate local registration. City and county sales taxes are collected under the state registration and reported to KDOR.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'It is unlawful to sell at retail or furnish taxable services without a registration certificate. Violations of the sales tax act carry, on conviction, a fine of $500 to $10,000, county jail of one to six months, or both.',
            'cite' => 'K.S.A. 79-3608; K.S.A. 79-3615(h)',
        ],
        'late_filing' => [
            'summary' => '1% of the unpaid tax for each month or part of a month the return or payment is late, up to 24%, plus interest.',
            'cite' => 'K.S.A. 79-3615(d)',
        ],
    ],
    'connected' => [
        'covers' => [
            'retailers\' sales tax',
            'retailers\' compensating use tax',
            'consumers\' compensating use tax',
            'withholding tax',
            'transient guest tax',
            'liquor drink tax',
            'cigarette and tobacco licenses',
        ],
        'prerequisites' => 'FEIN where applicable; corporations and LLCs can register with the Secretary of State through the same KDOR Customer Service Center. Each owner, partner or officer must sign the paper application.',
        'cite' => 'Pub. KS-1216; https://www.ksrevenue.gov/faqs-taxbusreg.html',
    ],
    'facts' => [
        [
            'text' => 'Since Jan. 1, 2025, the Kansas state sales tax rate on food and food ingredients is 0%; local sales taxes still apply to food.',
            'source_url' => 'https://www.ksrevenue.gov/taxnotices/notice24-21.pdf',
        ],
        [
            'text' => 'Kansas taxes labor services to install, apply, repair, service, alter or maintain tangible personal property, such as auto repair and commercial remodeling.',
            'source_url' => 'https://www.ksrevenue.gov/pdf/pub1216.pdf',
        ],
        [
            'text' => 'Prewritten computer software is taxable in Kansas; customized software is exempt.',
            'source_url' => 'https://www.ksrevenue.gov/pub1510.html',
        ],
        [
            'text' => 'Kansas participates in the Streamlined Sales Tax Project and uses destination sourcing.',
            'source_url' => 'https://www.ksrevenue.gov/pub1510.html',
        ],
        [
            'text' => 'In-person registration at the KDOR assistance center, by appointment, provides same-day registration.',
            'source_url' => 'https://www.ksrevenue.gov/pdf/pub1216.pdf',
        ],
    ],
    'state_notes' => 'In Kansas, the sales tax permit is a Retailers\' Sales Tax Registration Certificate. The Kansas Department of Revenue (KDOR) issues it. You can apply online through the KDOR Customer Service Center, or file the Business Tax Application, Form CR-16, by mail or fax. There is no fee, and no bond is required at first registration. KDOR may ask for a bond later. Online applications take about 2 to 3 business days. Paper applications take 2 to 3 weeks, so KDOR suggests applying 3 to 4 weeks before you open. The certificate does not expire. It stays valid until you cancel it or KDOR revokes it. KDOR sets your filing frequency by your yearly tax. Up to $1,000 a year files annually, up to $5,000 quarterly, and more than that monthly. Returns are due on the 25th of the month after the period. The state rate is 6.5%, plus city and county taxes based on where the buyer takes delivery. Kansas also taxes many repair and installation services. Remote sellers must register within 30 days of passing $100,000 in Kansas sales in a calendar year. The mistake to avoid: skipping a return because you had no sales. KDOR requires a return every period, even when you report zero tax.',
    'sources' => [
        [
            'title' => 'KDOR: Frequently Asked Questions About Business Registration',
            'url' => 'https://www.ksrevenue.gov/faqs-taxbusreg.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Pub. KS-1216 Business Tax Application and Instructions (Rev. 9-11-25)',
            'url' => 'https://www.ksrevenue.gov/pdf/pub1216.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'CR-16 Business Tax Application',
            'url' => 'https://www.ksrevenue.gov/pdf/cr16.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Pub. KS-1510 Sales Tax and Compensating Use Tax (Rev. 11/24)',
            'url' => 'https://www.ksrevenue.gov/pub1510.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'KDOR: Frequently Asked Questions About Sales',
            'url' => 'https://www.ksrevenue.gov/faqs-taxsales.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'KDOR Notice 21-17 Remote Sellers',
            'url' => 'https://www.ksrevenue.gov/taxnotices/notice21-17.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'KDOR Notice 21-14 Marketplace Facilitators',
            'url' => 'https://ksrevenue.gov/taxnotices/notice21-14.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'KDOR Notice 24-21 Food Sales Tax Rate Reduction',
            'url' => 'https://www.ksrevenue.gov/taxnotices/notice24-21.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'K.S.A. 79-3608',
            'url' => 'https://ksrevisor.gov/statutes/chapters/ch79/079_036_0008.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'K.S.A. 79-3615',
            'url' => 'https://ksrevisor.gov/statutes/chapters/ch79/079_036_0015.html',
            'accessed' => '2026-10-02',
        ],
    ],
];
