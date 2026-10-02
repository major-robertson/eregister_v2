<?php

/*
 * Illinois: Illinois Department of Revenue, Certificate of Registration
 * (sales and use tax, Retailers' Occupation Tax). Researched 2026-10-02
 * from tax.illinois.gov, law.onecle.com, ilga.gov. Generated once from the
 * EREG-13 sales tax research; edit this file directly from now on.
 */

return [
    'state' => 'IL',
    'name' => 'Illinois',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Illinois Department of Revenue',
        'short' => 'IDOR',
        'url' => 'https://tax.illinois.gov/',
    ],
    'registration' => [
        'term' => 'Certificate of Registration (sales and use tax, Retailers\' Occupation Tax)',
        'portal' => [
            'name' => 'MyTax Illinois (Register a New Business, Form REG-1)',
            'url' => 'https://mytax.illinois.gov/',
        ],
        'form' => [
            'number' => 'REG-1',
            'title' => 'Illinois Business Registration Application',
            'pdf_url' => 'https://tax.illinois.gov/content/dam/soi/en/web/tax/forms/reg/documents/reg-1.pdf',
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'No registration fee appears on IDOR\'s registration pages or in 35 ILCS 120/2a. IDOR may require a surety bond or irrevocable bank letter of credit of up to three times average monthly tax liability or $50,000, whichever is lower.',
            'cite' => 'https://tax.illinois.gov/businesses/registration.html; 35 ILCS 120/2a',
        ],
        'renewal' => [
            'required' => true,
            'summary' => 'For retailers who file sales tax returns, the Certificate expires after one year and renews automatically unless IDOR says otherwise. Resellers who do not file returns must reapply every three years using a renewal packet IDOR sends.',
            'cite' => 'IDOR Publication 113, Registering your Business, https://tax.illinois.gov/research/publications/pubs/retailers-overview-of-sales-and-use-tax/registering-your-business.html',
        ],
        'timing' => [
            'online' => 'about 1 to 2 business days',
            'paper' => '4 to 6 weeks',
            'temporary_number' => false,
            'summary' => 'IDOR says a MyTax Illinois registration takes approximately one to two business days and a paper REG-1 four to six weeks. The Certificate is issued electronically and printed from MyTax Illinois.',
            'cite' => 'https://tax.illinois.gov/businesses/registration.html; PIO-117 (R-01/26), https://tax.illinois.gov/content/dam/soi/en/web/tax/businesses/documents/pio-117.pdf',
        ],
        'number' => [
            'name' => 'Illinois Account ID (Sales and Use Tax Account ID)',
            'format' => '8 digits, XXXX-XXXX',
            'cite' => 'PIO-117 (R-01/26), https://tax.illinois.gov/content/dam/soi/en/web/tax/businesses/documents/pio-117.pdf',
        ],
    ],
    'nexus' => [
        'physical' => 'Anyone in the business of selling tangible personal property at retail in Illinois, including from an Illinois place of business, must hold a Certificate of Registration (35 ILCS 120/2a).',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => null,
            'period' => '12-month lookback period (cumulative gross receipts from sales of tangible personal property to Illinois purchasers)',
            'effective' => '2026-01-01',
            'cite' => 'P.A. 104-0006; IDOR Bulletin FY 2026-12, https://tax.illinois.gov/research/publications/bulletins/fy-2026-12.html',
        ],
        'marketplace' => 'A marketplace facilitator meeting the same $100,000 lookback test is treated as the retailer for sales it facilitates and collects state and destination-based local Retailers\' Occupation Tax (IDOR Bulletin FY 2026-12, https://tax.illinois.gov/research/publications/bulletins/fy-2026-12.html).',
    ],
    'filing' => [
        'frequencies' => 'monthly, quarterly or annual (Form ST-1)',
        'rule' => 'IDOR assigns monthly or quarterly filing at registration. Quarterly if average monthly liability is $200 or less, annual if $50 or less; monthly otherwise. Retailers averaging $20,000 or more a month over the prior four quarters make quarter-monthly accelerated payments after IDOR notice.',
        'due_day' => '20th of the month after the period',
        'zero_return_required' => null,
        'prepayments' => 'Quarter-monthly payments due the 7th, 15th, 22nd and last day of the month for retailers with average monthly liability of $20,000 or more (22.5% of actual liability for the month, or 25% of the same month last year).',
        'cite' => 'PIO-102 (effective Jan. 6, 2025), https://tax.illinois.gov/content/dam/soi/en/web/tax/research/taxinformation/sales/documents/pio-102.pdf; 86 Ill. Adm. Code 130.501, 130.535; 35 ILCS 120/3',
    ],
    'rates' => [
        'state_rate_pct' => 6.25,
        'local' => 'Home-rule and non-home-rule city, county and district taxes add to the 6.25% state rate and are administered by IDOR. The state 1% tax on groceries ended January 1, 2026; cities and counties may impose a local 1% grocery tax by ordinance.',
        'sourcing' => 'Sellers with an Illinois place of business generally source by where the selling activity occurs; since January 1, 2025 remote retailers and sales from out-of-state inventory use destination-based local rates.',
        'cite' => 'https://tax.illinois.gov/research/publications/bulletins/fy-2026-03.html; https://tax.illinois.gov/research/publications/bulletins/fy-2026-12.html',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => null,
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Selling at retail in Illinois without a Certificate of Registration is unlawful, and each day is a separate offense.',
            'cite' => '35 ILCS 120/2a',
        ],
        'late_filing' => [
            'summary' => 'Late-filing penalty of 2% of the tax required to be shown on the return, up to $250, plus late-payment penalties and interest.',
            'cite' => '35 ILCS 735/3-3',
        ],
    ],
    'connected' => [
        'covers' => [
            'Retailers\' Occupation Tax and Use Tax',
            'local taxes administered by IDOR',
            'withholding and other IDOR taxes on the same REG-1 (with schedules for liquor, tobacco, tire fee and others)',
        ],
        'prerequisites' => 'An FEIN where the organization type requires one; corporations, LLCs and limited partnerships register with the Illinois Secretary of State first.',
        'cite' => 'PIO-117 (R-01/26), https://tax.illinois.gov/content/dam/soi/en/web/tax/businesses/documents/pio-117.pdf',
    ],
    'facts' => [
        [
            'text' => 'From January 1, 2026, Illinois dropped the 200-transaction test; the only remote seller threshold is $100,000 in gross receipts over the 12-month lookback period (P.A. 104-0006).',
            'source_url' => 'https://tax.illinois.gov/research/publications/bulletins/fy-2026-12.html',
        ],
        [
            'text' => 'The state 1% tax on qualifying food (groceries) ended January 1, 2026; municipalities and counties that filed ordinances may now impose a local 1% grocery tax, collected by IDOR.',
            'source_url' => 'https://tax.illinois.gov/research/publications/bulletins/fy-2026-03.html',
        ],
        [
            'text' => 'Since January 1, 2025, remote retailers and retailers shipping from out-of-state inventory collect destination-based local Retailers\' Occupation Tax, and must keep the customer\'s full ship-to address.',
            'source_url' => 'https://tax.illinois.gov/research/publications/bulletins/fy-2026-12.html',
        ],
        [
            'text' => 'The Illinois Account ID on the Certificate of Registration has the format XXXX-XXXX, and the certificate must be displayed at the place of business.',
            'source_url' => 'https://tax.illinois.gov/content/dam/soi/en/web/tax/businesses/documents/pio-117.pdf',
        ],
        [
            'text' => 'Buyers use Form CRT-61, Certificate of Resale, and give their Illinois account ID or resale number.',
            'source_url' => 'https://tax.illinois.gov/businesses/crtinfo.html',
        ],
    ],
    'state_notes' => 'In Illinois, you need a Certificate of Registration from the Illinois Department of Revenue (IDOR). You apply online in MyTax Illinois using Form REG-1, the Illinois Business Registration Application, or on the paper REG-1. IDOR says online registrations take about one to two business days and paper ones four to six weeks. IDOR\'s pages list no registration fee, but it can ask some applicants for a bond. Register with the Illinois Secretary of State first if you are a corporation or LLC, and get an FEIN if your business type needs one. Your certificate shows your Account ID in the format XXXX-XXXX and renews automatically each year while you file returns. You file Form ST-1 monthly or quarterly, as IDOR assigns, by the 20th of the next month. Small accounts can file yearly. The state rate is 6.25%, and IDOR also collects city, county and district taxes. Since January 2026, groceries carry no state tax, but many towns add 1%. The one mistake to avoid: using the wrong local rate. Rates depend on where you sell from, and remote sellers must use the buyer\'s address.',
    'sources' => [
        [
            'title' => 'IDOR: Business Registration',
            'url' => 'https://tax.illinois.gov/businesses/registration.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'IDOR PIO-117: How to Register your Illinois Business (R-01/26)',
            'url' => 'https://tax.illinois.gov/content/dam/soi/en/web/tax/businesses/documents/pio-117.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'IDOR Publication 113: Registering your Business',
            'url' => 'https://tax.illinois.gov/research/publications/pubs/retailers-overview-of-sales-and-use-tax/registering-your-business.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'IDOR Bulletin FY 2026-12: Destination-based ROT changes',
            'url' => 'https://tax.illinois.gov/research/publications/bulletins/fy-2026-12.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'IDOR Bulletin FY 2026-03: Grocery tax changes effective January 1, 2026',
            'url' => 'https://tax.illinois.gov/research/publications/bulletins/fy-2026-03.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'IDOR PIO-102: Filing, payment and refund resources (effective Jan. 6, 2025)',
            'url' => 'https://tax.illinois.gov/content/dam/soi/en/web/tax/research/taxinformation/sales/documents/pio-102.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => '35 ILCS 120/2a (onecle copy)',
            'url' => 'https://law.onecle.com/illinois/35ilcs120/2a.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => '35 ILCS 735/3-3',
            'url' => 'https://ilga.gov/documents/legislation/ilcs/documents/003507350K3-3.htm',
            'accessed' => '2026-10-02',
        ],
    ],
];
