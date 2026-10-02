<?php

/*
 * New York: New York State Department of Taxation and Finance, Certificate
 * of Authority. Researched 2026-10-02 from tax.ny.gov. Generated once from
 * the EREG-13 sales tax research; edit this file directly from now on.
 */

return [
    'state' => 'NY',
    'name' => 'New York',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'New York State Department of Taxation and Finance',
        'short' => 'the Tax Department',
        'url' => 'https://www.tax.ny.gov/',
    ],
    'registration' => [
        'term' => 'Certificate of Authority',
        'portal' => [
            'name' => 'New York Business Express',
            'url' => 'https://www.businessexpress.ny.gov/',
        ],
        'form' => [
            'number' => 'DTF-17',
            'title' => 'Application to Register for a Sales Tax Certificate of Authority',
            'pdf_url' => null,
            'online_only' => true,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'No fee to apply for a Certificate of Authority.',
            'cite' => 'TB-ST-360, How to Register for New York State Sales Tax (no fee mentioned in the application process)',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'A regular Certificate of Authority does not expire. A temporary certificate, for sellers active no more than two consecutive sales tax quarters in 12 months, carries fixed start and end dates.',
            'cite' => 'TB-ST-360',
        ],
        'timing' => [
            'online' => null,
            'paper' => null,
            'temporary_number' => false,
            'summary' => 'Apply at least 20 days before you begin business. The Tax Department processes the application and, if approved, mails the Certificate of Authority; you cannot legally make taxable sales until you receive it. Application status updates arrive by email. No processing time is published.',
            'cite' => 'TB-ST-360; Tax Department, Register as a sales tax vendor',
        ],
        'number' => [
            'name' => 'New York sales tax identification number',
            'format' => null,
            'cite' => 'TB-ST-360 (all locations receive certificates with the same sales tax identification number)',
        ],
    ],
    'nexus' => [
        'physical' => 'Every person who sells taxable tangible personal property or taxable services in New York, even from home, as a temporary vendor or once a year, including at craft shows and flea markets, must register before beginning business.',
        'economic' => [
            'revenue_usd' => 500000,
            'transactions' => 100,
            'period' => 'immediately preceding four sales tax quarters (both tests must be met: over $500,000 and more than 100 sales of tangible personal property delivered into New York)',
            'effective' => '2019-06-24',
            'cite' => 'Tax Law 1101(b)(8)(iv); TSB-M-19(4)S; Tax Department, Registration requirement for businesses with no physical presence in New York State',
        ],
        'marketplace' => 'Marketplace providers without physical presence must register if their made or facilitated sales of tangible personal property delivered into New York exceeded $500,000 and more than 100 sales in the previous four sales tax quarters, and then collect on facilitated sales (Tax Law 1101(e); TSB-M-19(4)S).',
    ],
    'filing' => [
        'frequencies' => 'quarterly, annual or part-quarterly (monthly)',
        'rule' => 'New vendors usually file quarterly (ST-100). Annual (ST-101) if tax due is $3,000 or less for the year. Part-quarterly monthly filing (ST-809 plus ST-810) starts the quarter after taxable receipts, purchases, rents and amusement charges reach $300,000 or more in a quarter. PrompTax electronic payments apply to large vendors, generally over $500,000 in annual liability.',
        'due_day' => '20th day after the end of the period; sales tax quarters end May 31, August 31, November 30 and the last day of February; annual returns due March 20',
        'zero_return_required' => true,
        'prepayments' => 'PrompTax electronic payments for large vendors (generally annual sales and use tax liability over $500,000)',
        'cite' => 'TB-ST-275, Filing Requirements for Sales and Use Tax Returns',
    ],
    'rates' => [
        'state_rate_pct' => 4.0,
        'local' => 'Counties and some cities add local tax; combined rates run from 7% to 8.875%, including the 0.375% Metropolitan Commuter Transportation District tax in New York City and nearby counties.',
        'sourcing' => 'destination-based (tax at the rate where the customer receives the item)',
        'cite' => 'Publication 718 (2/25), New York State Sales and Use Tax Rates by Jurisdiction',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'State and local sales taxes, including New York City\'s, are registered and filed with the Tax Department. No separate local sales tax registration.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Selling without a required Certificate of Authority: up to $500 for the first day plus up to $200 for each later day, up to $10,000. Failing to display the certificate: $50.',
            'cite' => 'Tax Law 1145(a)(3) and 1145(a)(4); TB-ST-805, Sales and Use Tax Penalties',
        ],
        'late_filing' => [
            'summary' => '10% of the tax due for the first month plus 1% for each additional month, up to 30%, with a $50 minimum. A late return with no tax due draws a $50 penalty.',
            'cite' => 'Tax Law 1145(a)(1)(i); TB-ST-805',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales tax',
            'use tax',
        ],
        'prerequisites' => 'A NY.gov Business account to use New York Business Express. A separate Tax Department Business Online Services account is needed to file returns online.',
        'cite' => 'Tax Department, Register as a sales tax vendor',
    ],
    'facts' => [
        [
            'text' => 'Once you hold a Certificate of Authority you are treated as in business for sales tax even if you never make a sale, so returns are due on time even with no sales, and late filing draws a penalty even if no tax is owed.',
            'source_url' => 'https://www.tax.ny.gov/pubs_and_bulls/tg_bulletins/st/how_to_register_for_nys_sales_tax.htm',
        ],
        [
            'text' => 'New York sales tax quarters run March through May, June through August, September through November, and December through February, not calendar quarters.',
            'source_url' => 'https://www.tax.ny.gov/pubs_and_bulls/tg_bulletins/st/filing_requirements_for_sales_and_use_tax_returns.htm',
        ],
        [
            'text' => 'A remote seller must meet both tests, more than $500,000 in sales and more than 100 sales delivered into New York, in the prior four sales tax quarters before registration is required.',
            'source_url' => 'https://www.tax.ny.gov/pubs_and_bulls/publications/sales/nexus.htm',
        ],
        [
            'text' => 'Show and entertainment vendors (craft shows, flea markets, sporting events) must hold a regular Certificate of Authority; the Department no longer issues a separate show vendor certificate.',
            'source_url' => 'https://www.tax.ny.gov/pubs_and_bulls/tg_bulletins/st/how_to_register_for_nys_sales_tax.htm',
        ],
        [
            'text' => 'A Certificate of Authority cannot be transferred. A buyer of an existing business, or a business that changes its legal form, needs its own new certificate before it starts selling.',
            'source_url' => 'https://www.tax.ny.gov/pubs_and_bulls/tg_bulletins/st/how_to_register_for_nys_sales_tax.htm',
        ],
    ],
    'state_notes' => 'In New York you need a Certificate of Authority to collect sales tax. The New York State Department of Taxation and Finance issues it. You apply online through New York Business Express; the application is Form DTF-17. There is no fee. Apply at least 20 days before you start selling. The Department reviews the application and mails the certificate. You cannot legally make taxable sales until it arrives, and you must display it where you sell. Remote sellers must register when they had more than $500,000 in sales and more than 100 sales delivered into New York in the prior four sales tax quarters. Both tests must be met. Most new vendors file quarterly. Small vendors owing $3,000 or less a year may file annually, and vendors with $300,000 or more in quarterly taxable receipts file monthly. Returns are due 20 days after each period ends. New York\'s sales tax quarters end in May, August, November and February. The one mistake to avoid: skipping returns when you had no sales. Once you hold a certificate, every return is due, and a late zero return still costs $50. Selling without a certificate can cost up to $500 for the first day and $200 a day after.',
    'sources' => [
        [
            'title' => 'Register as a sales tax vendor',
            'url' => 'https://www.tax.ny.gov/bus/st/register.htm',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'TB-ST-360, How to Register for New York State Sales Tax',
            'url' => 'https://www.tax.ny.gov/pubs_and_bulls/tg_bulletins/st/how_to_register_for_nys_sales_tax.htm',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Registration requirement for businesses with no physical presence in New York State',
            'url' => 'https://www.tax.ny.gov/pubs_and_bulls/publications/sales/nexus.htm',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'TB-ST-275, Filing Requirements for Sales and Use Tax Returns',
            'url' => 'https://www.tax.ny.gov/pubs_and_bulls/tg_bulletins/st/filing_requirements_for_sales_and_use_tax_returns.htm',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'TB-ST-805, Sales and Use Tax Penalties',
            'url' => 'https://www.tax.ny.gov/pubs_and_bulls/tg_bulletins/st/sales_and_use_tax_penalties.htm',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Publication 718 (2/25), Sales and Use Tax Rates by Jurisdiction',
            'url' => 'https://www.tax.ny.gov/pdf/publications/sales/pub718.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Exemption Certificates for Sales Tax (TB-ST-240)',
            'url' => 'https://www.tax.ny.gov/pubs_and_bulls/tg_bulletins/st/exemption_certificates_for_sales_tax.htm',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Sales tax forms list (DTF-17)',
            'url' => 'https://www.tax.ny.gov/forms/sales_cur_forms.htm',
            'accessed' => '2026-10-02',
        ],
    ],
];
