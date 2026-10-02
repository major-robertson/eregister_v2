<?php

/*
 * Missouri: Missouri Department of Revenue, Retail Sales License (Missouri
 * Tax I.D. Number); out-of-state sellers register for vendor's use tax.
 * Researched 2026-10-02 from dor.mo.gov, revisor.mo.gov. Generated once
 * from the EREG-13 sales tax research; edit this file directly from now
 * on.
 */

return [
    'state' => 'MO',
    'name' => 'Missouri',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Missouri Department of Revenue',
        'short' => 'the Department of Revenue (DOR)',
        'url' => 'https://dor.mo.gov/',
    ],
    'registration' => [
        'term' => 'Retail Sales License (Missouri Tax I.D. Number); out-of-state sellers register for vendor\'s use tax',
        'portal' => [
            'name' => 'Online Business Registration; MyTax Missouri Portal for filing',
            'url' => 'https://mytax.mo.gov/',
        ],
        'form' => [
            'number' => '2643',
            'title' => 'Missouri Tax Registration Application',
            'pdf_url' => 'https://dor.mo.gov/forms/2643.pdf',
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'No cost. The retail sales license is issued at no cost to the licensee. Since Aug. 28, 2018, no bond is required for sales or vendor\'s use tax unless the business is delinquent.',
            'cite' => 'RSMo 144.083.1; https://dor.mo.gov/faq/taxation/business/registration.html',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'The license is valid until revoked by the Director or surrendered when sales stop.',
            'cite' => 'RSMo 144.083.1',
        ],
        'timing' => [
            'online' => '10 business days',
            'paper' => '10 business days',
            'temporary_number' => false,
            'summary' => 'DOR asks applicants to allow 10 business days to process a retail sales tax application (15 calendar days for other tax types); the statute requires the license within ten working days of a properly completed application. The Tax I.D. Number arrives by mail.',
            'cite' => 'https://dor.mo.gov/faq/taxation/business/registration.html; RSMo 144.083.1',
        ],
        'number' => [
            'name' => 'Missouri Tax I.D. Number',
            'format' => '8 digits',
            'cite' => 'resale research 2026-10-01',
        ],
    ],
    'nexus' => [
        'physical' => 'Anyone making retail sales of tangible personal property or taxable services (such as telephone, room rental, fitness clubs and movie theaters) in Missouri must hold a retail sales license before selling; 100% wholesalers do not.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => null,
            'period' => 'preceding 12 months, tested at the end of each calendar quarter (gross receipts from taxable sales into Missouri); collection starts no later than three months after the quarter closes',
            'effective' => '2023-01-01',
            'cite' => 'RSMo 144.605 and 144.752 (2021 SB 153); https://dor.mo.gov/faq/taxation/business/remote-seller-and-marketplace-facilitator.html',
        ],
        'marketplace' => 'A marketplace facilitator whose taxable Missouri sales, including facilitated sales, exceed $100,000 must register and collect vendor\'s use tax on facilitated sales; marketplace-only sellers need not register (DOR Remote Seller and Marketplace Facilitator FAQs).',
    ],
    'filing' => [
        'frequencies' => 'quarter-monthly, monthly, quarterly or annual by state tax collected',
        'rule' => 'State tax of $500 or more a month: monthly. $500 or less a month: quarterly. Less than $200 a quarter: annual. Local tax is not counted. Large filers make quarter-monthly payments and must file electronically. DOR reviews frequencies every year. Businesses reporting from three or more locations must e-file.',
        'due_day' => 'last day of the month after the period (annual returns due January 31)',
        'zero_return_required' => true,
        'prepayments' => 'Quarter-monthly payments for the largest filers (RSMo 144.081).',
        'cite' => 'https://dor.mo.gov/faq/taxation/business/sales-tax-filing.html; https://dor.mo.gov/taxation/business/tax-types/sales-use/',
    ],
    'rates' => [
        'state_rate_pct' => 4.225,
        'local' => 'Cities, counties and special districts (such as fire districts and transportation development districts) add local sales taxes, which change quarterly; DOR publishes a rate lookup. Food is taxed at a reduced state rate.',
        'sourcing' => 'origin for in-state sellers: the combined rate at the seller\'s location',
        'cite' => 'https://dor.mo.gov/taxation/business/tax-types/sales-use/',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'No separate local sales tax registration; DOR collects local sales taxes with the state tax. But a city or county occupation (business) license requires a Missouri retail sales license and a DOR statement of no tax due dated within 90 days.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Selling without a valid retail sales license: an administrative penalty of up to $500 for the first day and $100 a day after, up to $10,000, on top of other penalties. The penalty does not apply for the first 20 days to a business opening in Missouri for the first time.',
            'cite' => 'RSMo 144.118',
        ],
        'late_filing' => [
            'summary' => 'Additions to tax: 5% of tax due if a return is filed but paid late; 5% per month if no return is filed, up to 25%. Interest also applies, and the 2% timely payment allowance is lost.',
            'cite' => 'https://dor.mo.gov/faq/taxation/business/sales-tax-filing.html (RSMo 144.250)',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales tax',
            'vendor\'s use tax',
            'consumer\'s use tax',
            'employer withholding tax',
            'corporate income tax',
            'tire and lead-acid battery fees',
            '911 fees',
        ],
        'prerequisites' => 'FEIN or SSN; Secretary of State charter number for entities. Past-due Missouri taxes must be paid before a license is issued. Buyers of an existing business should get a Certificate of No Tax Due from the seller.',
        'cite' => 'Form 2643 (Revised 02-2026); RSMo 144.083.1',
    ],
    'facts' => [
        [
            'text' => 'Since Jan. 1, 2023, remote sellers and marketplace facilitators must collect Missouri vendor\'s use tax once taxable Missouri sales exceed $100,000 in the prior 12 months, checked each quarter.',
            'source_url' => 'https://dor.mo.gov/faq/taxation/business/remote-seller-and-marketplace-facilitator.html',
        ],
        [
            'text' => 'Missouri dropped its sales tax bond requirement on Aug. 28, 2018; DOR may still require a bond from delinquent businesses.',
            'source_url' => 'https://dor.mo.gov/faq/taxation/business/registration.html',
        ],
        [
            'text' => 'Sellers who file and pay on time keep a 2% timely payment allowance.',
            'source_url' => 'https://dor.mo.gov/faq/taxation/business/sales-tax-filing.html',
        ],
        [
            'text' => 'A Missouri retail sales license is a prerequisite for any city or county occupation license for a retail business.',
            'source_url' => 'https://revisor.mo.gov/main/OneSection.aspx?section=144.083',
        ],
        [
            'text' => 'On July 22, 2025, the Missouri Supreme Court limited county add-on taxes on adult-use marijuana to unincorporated areas.',
            'source_url' => 'https://dor.mo.gov/taxation/business/tax-types/sales-use/',
        ],
    ],
    'state_notes' => 'In Missouri, the permit is a Retail Sales License, tied to your Missouri Tax I.D. Number. The Missouri Department of Revenue (DOR) issues it. You apply online through Online Business Registration or by mailing Form 2643, the Missouri Tax Registration Application. The license is free, and most businesses no longer post a bond. DOR asks you to allow 10 business days for processing, and the number comes by mail. The license does not expire. Get it before your first sale. Selling without one can cost up to $500 for the first day and $100 a day after. Your local city or county business license also requires it. Filing frequency depends on the state tax you collect. $500 or more a month files monthly, less files quarterly, and under $200 a quarter files annually. Returns are due on the last day of the month after the period. The state rate is 4.225%, plus city, county and district taxes based on your location. Remote sellers must register once taxable Missouri sales pass $100,000 in 12 months. The mistake to avoid: skipping zero returns. Every license holder must file each period, even with no sales.',
    'sources' => [
        [
            'title' => 'DOR: Sales/Use Tax',
            'url' => 'https://dor.mo.gov/taxation/business/tax-types/sales-use/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DOR: Business Tax Registration FAQs',
            'url' => 'https://dor.mo.gov/faq/taxation/business/registration.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DOR: Sales Tax FAQs',
            'url' => 'https://dor.mo.gov/faq/taxation/business/sales-tax-filing.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DOR: Remote Seller and Marketplace Facilitator FAQs',
            'url' => 'https://dor.mo.gov/faq/taxation/business/remote-seller-and-marketplace-facilitator.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Form 2643 Missouri Tax Registration Application (Revised 02-2026)',
            'url' => 'https://dor.mo.gov/forms/2643.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'RSMo 144.083',
            'url' => 'https://revisor.mo.gov/main/OneSection.aspx?section=144.083',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'RSMo 144.118',
            'url' => 'https://revisor.mo.gov/main/OneSection.aspx?section=144.118',
            'accessed' => '2026-10-02',
        ],
    ],
];
