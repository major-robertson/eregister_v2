<?php

/*
 * Washington: Washington State Department of Revenue (Business Licensing
 * Service), Business License (state tax registration under a Unified
 * Business Identifier, UBI). Researched 2026-10-02 from dor.wa.gov,
 * app.leg.wa.gov. Generated once from the EREG-13 sales tax research; edit
 * this file directly from now on.
 */

return [
    'state' => 'WA',
    'name' => 'Washington',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Washington State Department of Revenue (Business Licensing Service)',
        'short' => 'the Department of Revenue (DOR)',
        'url' => 'https://dor.wa.gov/',
    ],
    'registration' => [
        'term' => 'Business License (state tax registration under a Unified Business Identifier, UBI)',
        'portal' => [
            'name' => 'Business Licensing Wizard / My DOR',
            'url' => 'https://dor.wa.gov/open-business/apply-business-license',
        ],
        'form' => [
            'number' => 'BLS 700 028',
            'title' => 'Business License Application',
            'pdf_url' => 'https://dor.wa.gov/sites/default/files/2022-03/700028_0.pdf',
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 5000,
            'summary' => 'A $50 non-refundable processing fee to open the first location of a new business (or reopen one). Adding a location is free. City, county and state endorsements carry their own fees.',
            'cite' => 'Business License Application, Form BLS 700 028, processing fee instructions; https://dor.wa.gov/open-business/apply-business-license',
        ],
        'renewal' => [
            'required' => null,
            'summary' => 'The state tax registration certificate stays valid as long as the taxpayer stays in business and pays the tax. Some city and state endorsements on the business license renew on their own schedules.',
            'cite' => 'RCW 82.32.030(1)',
        ],
        'timing' => [
            'online' => 'about 10 business days',
            'paper' => 'up to 3 weeks per the paper form (DOR\'s web page says up to six weeks by mail)',
            'temporary_number' => false,
            'summary' => 'Online applications are typically processed within ten business days. Endorsements that need city, county or state approval can add 2 to 3 weeks.',
            'cite' => 'Form BLS 700 028, p. 1; https://dor.wa.gov/open-business/apply-business-license',
        ],
        'number' => [
            'name' => 'Unified Business Identifier (UBI)',
            'format' => null,
            'cite' => 'https://dor.wa.gov/open-business/apply-business-license',
        ],
    ],
    'nexus' => [
        'physical' => 'Anyone engaging in business in Washington on which a state tax is imposed, including selling at retail from property or employees in the state, must register, with a separate certificate for each place of business (RCW 82.32.030).',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => null,
            'period' => 'current or prior calendar year (combined gross receipts sourced or attributed to Washington, including marketplace and exempt sales); once triggered, collect for the rest of that year and the next',
            'effective' => '2019-03-14',
            'cite' => 'DOR Remote sellers page (the 200-transaction test was eliminated March 14, 2019; current receipts threshold in force since January 1, 2020); RCW 82.04.067',
        ],
        'marketplace' => 'A marketplace facilitator with more than $100,000 in combined gross receipts sourced to Washington must register and collect retail sales tax on the sales it facilitates; its sellers deduct those sales (DOR Marketplace facilitators page).',
    ],
    'filing' => [
        'frequencies' => 'monthly, quarterly or annual',
        'rule' => 'Assigned by annual tax liability: $1,050 or less annual; $1,051 to $4,800 quarterly; more than $4,800 monthly. One combined excise tax return covers sales tax, B&O tax and other DOR excise taxes.',
        'due_day' => 'monthly returns the 25th of the following month; quarterly returns the last day of the month after the quarter; annual returns April 15',
        'zero_return_required' => true,
        'prepayments' => null,
        'cite' => 'https://dor.wa.gov/file-pay-taxes/filing-frequencies-due-dates; DOR My DOR help, Tax returns (file a no-business return when there is no activity)',
    ],
    'rates' => [
        'state_rate_pct' => 6.5,
        'local' => 'Local rates vary by city and county and change quarterly; DOR\'s rate lookup gives the rate and location code for each address',
        'sourcing' => 'destination: tax is collected at the rate where the customer receives the goods or services',
        'cite' => 'RCW 82.08.020(1); https://dor.wa.gov/taxes-rates/retail-sales-tax',
    ],
    'local_registration' => [
        'required' => true,
        'summary' => 'Many Washington cities and counties require a city or county business license endorsement. These are added to the same state Business License Application through the Business Licensing Service, each with its own fee.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Engaging in business without a certificate of registration, or letting a company do so as an officer, is a gross misdemeanor.',
            'cite' => 'RCW 82.32.290(1)',
        ],
        'late_filing' => [
            'summary' => 'Late payment penalty of 9% of the tax after the due date, 19% if not paid by the end of the following month, and 29% after the second month (minimum $5).',
            'cite' => 'RCW 82.32.090(1)',
        ],
    ],
    'connected' => [
        'covers' => [
            'retail sales tax',
            'business and occupation (B&O) tax',
            'other DOR excise taxes',
            'Employment Security and Labor & Industries accounts if hiring',
            'city, county and state endorsements',
            'trade name',
        ],
        'prerequisites' => 'Corporations, partnerships and LLCs must file with the Washington Secretary of State before applying for the business license',
        'cite' => 'https://dor.wa.gov/open-business/apply-business-license; Form BLS 700 028',
    ],
    'facts' => [
        [
            'text' => 'Since October 1, 2025 some business services are subject to retail sales tax under ESSB 5814, including certain information technology services.',
            'source_url' => 'https://dor.wa.gov/taxes-rates/retail-sales-tax',
        ],
        [
            'text' => 'Washington taxes many digital products, digital codes and digital automated services sold to end users.',
            'source_url' => 'https://app.leg.wa.gov/RCW/default.aspx?cite=82.08.020',
        ],
        [
            'text' => 'To buy inventory without paying sales tax, a Washington-registered business applies separately for a reseller permit. It is generally valid for four years (two years for contractors and newer businesses).',
            'source_url' => 'https://dor.wa.gov/taxes-rates/retail-sales-tax/reseller-permits',
        ],
        [
            'text' => 'A business with no retail sales and gross income under $125,000 a year (from 2023) may qualify for active non-reporting status and not file returns.',
            'source_url' => 'https://dor.wa.gov/file-pay-taxes/filing-frequencies-due-dates/active-non-reporting',
        ],
        [
            'text' => 'A business with more than one place of business dealing with the public needs a separate registration certificate for each one, posted at that location.',
            'source_url' => 'https://app.leg.wa.gov/RCW/default.aspx?cite=82.32.030',
        ],
    ],
    'state_notes' => 'Washington does not issue a stand-alone sales tax permit. You collect sales tax under your state business license, issued by the Department of Revenue\'s Business Licensing Service. The license gives you a Unified Business Identifier (UBI) number. Apply online through the Business Licensing Wizard or My DOR, or mail the Business License Application, Form BLS 700 028. The processing fee is $50 to open a new business. City and state endorsements cost extra. Online applications usually take about ten business days. Endorsements can add two to three weeks. Corporations and LLCs must file with the Secretary of State first. Sellers outside Washington must register once their Washington receipts pass $100,000 in the current or prior calendar year. The state rate is 6.5%, plus local tax based on where the buyer receives the item. You file one combined excise tax return for sales tax and the B&O tax. DOR assigns monthly, quarterly or annual filing based on how much tax you owe. Monthly returns are due the 25th. The mistake to avoid: assuming the business license lets you buy inventory tax-free. You need a separate reseller permit for that.',
    'sources' => [
        [
            'title' => 'DOR: Apply for a business license',
            'url' => 'https://dor.wa.gov/open-business/apply-business-license',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Business License Application, Form BLS 700 028',
            'url' => 'https://dor.wa.gov/sites/default/files/2022-03/700028_0.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DOR: Retail sales tax',
            'url' => 'https://dor.wa.gov/taxes-rates/retail-sales-tax',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DOR: Remote sellers',
            'url' => 'https://dor.wa.gov/taxes-rates/retail-sales-tax/marketplace-fairness-leveling-playing-field/remote-sellers',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DOR: Marketplace facilitators',
            'url' => 'https://dor.wa.gov/taxes-rates/retail-sales-tax/marketplace-fairness-leveling-playing-field/marketplace-facilitators',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DOR: Filing frequencies & due dates',
            'url' => 'https://dor.wa.gov/file-pay-taxes/filing-frequencies-due-dates',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DOR: Active non-reporting',
            'url' => 'https://dor.wa.gov/file-pay-taxes/filing-frequencies-due-dates/active-non-reporting',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DOR: Reseller permits',
            'url' => 'https://dor.wa.gov/taxes-rates/retail-sales-tax/reseller-permits',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'RCW 82.08.020',
            'url' => 'https://app.leg.wa.gov/RCW/default.aspx?cite=82.08.020',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'RCW 82.32.030',
            'url' => 'https://app.leg.wa.gov/RCW/default.aspx?cite=82.32.030',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'RCW 82.32.090',
            'url' => 'https://app.leg.wa.gov/RCW/default.aspx?cite=82.32.090',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'RCW 82.32.290',
            'url' => 'https://app.leg.wa.gov/RCW/default.aspx?cite=82.32.290',
            'accessed' => '2026-10-02',
        ],
    ],
];
