<?php

/*
 * Maine: Maine Revenue Services, Retailer Certificate (sales and use tax
 * registration). Researched 2026-10-02 from maine.gov,
 * legislature.maine.gov, taxjar.com. Generated once from the EREG-13 sales
 * tax research; edit this file directly from now on.
 */

return [
    'state' => 'ME',
    'name' => 'Maine',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Maine Revenue Services',
        'short' => 'MRS',
        'url' => 'https://www.maine.gov/revenue/',
    ],
    'registration' => [
        'term' => 'Retailer Certificate (sales and use tax registration)',
        'portal' => [
            'name' => 'Maine Tax Portal (MTP), Register a New Business',
            'url' => 'https://revenue.maine.gov/',
        ],
        'form' => [
            'number' => null,
            'title' => 'Registration Application (income tax withholding, sales and use tax and other business taxes), revised June 2023',
            'pdf_url' => 'https://www.maine.gov/revenue/sites/maine.gov.revenue/files/inline-files/23_Central_reg_app_booklet_MTP_ff.pdf',
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'No fee. Application forms are furnished free of charge, and MRS states there is no fee for registration.',
            'cite' => '36 M.R.S. 1754-B; MRS Business Guide to Sales, Use and Service Provider Tax',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'Retailer Certificates have no expiration date; they stay valid until the retailer cancels or MRS revokes them. (Resale Certificates, issued separately to retailers with $3,000+ in annual sales, do expire and are reissued.)',
            'cite' => 'MRS Business Guide (Retailer Certificates); Instructional Bulletin No. 54',
        ],
        'timing' => [
            'online' => null,
            'paper' => null,
            'temporary_number' => false,
            'summary' => 'MRS does not publish a processing time on the pages reviewed.',
            'cite' => null,
        ],
        'number' => [
            'name' => 'Maine sales tax registration number',
            'format' => '8 digits, shown on the Retailer and Resale Certificates and the welcome letter (with a separate 3-digit business code)',
            'cite' => 'https://www.maine.gov/revenue/faq/sales-use-service-provider-tax',
        ],
    ],
    'nexus' => [
        'physical' => 'Every seller of tangible personal property or taxable services with any kind of business location in Maine, salespeople soliciting in Maine, or other substantial physical presence must register, including people who rent living quarters, even casually.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => null,
            'period' => 'previous or current calendar year (gross sales delivered into Maine)',
            'effective' => '2022-01-01',
            'cite' => '36 M.R.S. 1754-B(1-B)(B), as amended by PL 2021, c. 181 (200-transaction test removed effective Jan. 1, 2022)',
        ],
        'marketplace' => 'A marketplace facilitator whose gross sales for delivery in Maine exceed $100,000 in the previous or current calendar year must register and collect on facilitated sales; since Oct. 25, 2023 it also collects the recycling assistance fee (MRS Business Guide; 36 M.R.S. 1754-B).',
    ],
    'filing' => [
        'frequencies' => 'monthly, quarterly, semiannual or annual by average tax liability',
        'rule' => 'Average tax of $600 or more a month: monthly. $100 to $599 a month: quarterly. Under $100 a month but over $50 a year: semiannual. Under $50 a year: annual. MRS reviews liabilities each year and adjusts frequencies (Rule 304). Returns must be filed electronically through the Maine Tax Portal unless a waiver is granted.',
        'due_day' => '15th of the month after the period',
        'zero_return_required' => true,
        'prepayments' => null,
        'cite' => 'https://www.maine.gov/revenue/faq/sales-use-service-provider-tax; MRS Business Guide (Returns)',
    ],
    'rates' => [
        'state_rate_pct' => 5.5,
        'local' => 'No local sales taxes. Special state rates: 8% prepared food, 9% lodging, 10% short-term auto rental, 14% adult-use cannabis (rates effective 01/01/2026).',
        'sourcing' => null,
        'cite' => 'https://www.maine.gov/revenue/taxes/sales-use-service-provider-tax/rates-due-dates',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'No local sales tax and no local registration.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'A person required to register who makes retail sales in Maine without being registered commits a Class E crime.',
            'cite' => '36 M.R.S. 1754-B(3)',
        ],
        'late_filing' => [
            'summary' => 'Failure to file on time: the greater of $25 or 10% of the tax due (the greater of $25 or 25% if not filed within 60 days of a formal demand). Failure to pay: 1% of unpaid tax per month, up to 25%, plus interest.',
            'cite' => '36 M.R.S. 187-B; https://www.maine.gov/revenue/faq/sales-use-service-provider-tax',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales tax',
            'use tax',
            'income tax withholding',
            'recycling assistance fee',
            'prepaid wireless fee',
            'other Sales, Fuel and Special Tax Division taxes',
        ],
        'prerequisites' => 'FEIN for any entity other than a sole proprietor; unemployment tax is registered separately with the Maine Department of Labor.',
        'cite' => 'https://www.maine.gov/revenue/faq/sales-use-service-provider-tax; MRS Registration Application (June 2023)',
    ],
    'facts' => [
        [
            'text' => 'Maine repealed its Service Provider Tax on Jan. 1, 2026. Services formerly under it, such as cable and satellite TV, telecommunications and fabrication, are now taxed under the sales tax at 5.5%, and providers needed a sales and use tax account.',
            'source_url' => 'https://www.maine.gov/revenue/sites/maine.gov.revenue/files/inline-files/GIB%20115_FINAL_2025_10_17_0.pdf',
        ],
        [
            'text' => 'From Jan. 1, 2026, digital audiovisual and digital audio services (streaming with less than permanent use) are taxable at 5.5%.',
            'source_url' => 'https://www.maine.gov/revenue/sites/maine.gov.revenue/files/inline-files/GIB%20115_FINAL_2025_10_17_0.pdf',
        ],
        [
            'text' => 'MRS issues a separate Resale Certificate only to registered retailers reporting $3,000 or more in annual gross sales; smaller retailers pay tax on purchases for resale and take a credit on their return.',
            'source_url' => 'https://www.maine.gov/revenue/faq/sales-use-service-provider-tax',
        ],
        [
            'text' => 'A separate Retailer Certificate is needed for each place of business in Maine.',
            'source_url' => 'https://legislature.maine.gov/statutes/36/title36sec1754-B.html',
        ],
        [
            'text' => 'The adult-use cannabis sales tax rate is 14% as of Jan. 1, 2026.',
            'source_url' => 'https://www.maine.gov/revenue/taxes/sales-use-service-provider-tax/rates-due-dates',
        ],
    ],
    'state_notes' => 'In Maine, you register with Maine Revenue Services (MRS) for a sales and use tax account. MRS then issues a Retailer Certificate showing your 8-digit registration number. You apply online through the Maine Tax Portal using Register a New Business, or by mailing the paper Registration Application. Registration is free. You need a separate certificate for each place of business, and certificates do not expire. If you expect $3,000 or more in yearly sales, MRS also sends a Resale Certificate so you can buy inventory tax-free. Returns are due on the 15th of the month after each period and must be filed online. MRS sets your frequency by your average tax: monthly at $600 or more a month, quarterly from $100, and semiannual or annual below that. The general rate is 5.5%, with higher rates for prepared food (8%), lodging (9%) and short-term car rentals (10%). There are no local sales taxes. Since January 2026, telecom, cable and streaming services are also taxed at 5.5%. Remote sellers must register once Maine sales pass $100,000 in the current or prior calendar year. The mistake to avoid: selling before you register. Making retail sales without registering is a Class E crime in Maine.',
    'sources' => [
        [
            'title' => 'MRS: Sales and Use Tax FAQ',
            'url' => 'https://www.maine.gov/revenue/faq/sales-use-service-provider-tax',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'MRS: Sales and Use Tax Rates & Due Dates',
            'url' => 'https://www.maine.gov/revenue/taxes/sales-use-service-provider-tax/rates-due-dates',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'MRS Business Guide to Sales, Use and Service Provider Tax (Rev. Oct. 16, 2023)',
            'url' => 'https://www.maine.gov/revenue/sites/maine.gov.revenue/files/inline-files/BusinessGuide10162023_FINAL.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'MRS Registration Application (June 2023)',
            'url' => 'https://www.maine.gov/revenue/sites/maine.gov.revenue/files/inline-files/23_Central_reg_app_booklet_MTP_ff.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => '36 M.R.S. 1754-B Registration of sellers',
            'url' => 'https://legislature.maine.gov/statutes/36/title36sec1754-B.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'MRS General Information Bulletin No. 115 (Service Provider Tax repeal)',
            'url' => 'https://www.maine.gov/revenue/sites/maine.gov.revenue/files/inline-files/GIB%20115_FINAL_2025_10_17_0.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'MRS Instructional Bulletin No. 32 Rental of Living Quarters (2026-03-11)',
            'url' => 'https://www.maine.gov/revenue/sites/maine.gov.revenue/files/inline-files/IB32%20Rental%20of%20Living%20Quarters%202026_03_11.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'TaxJar: Maine removed transaction nexus threshold (cross-check)',
            'url' => 'https://www.taxjar.com/blog/2021-10-maine-transaction-threshold-removed',
            'accessed' => '2026-10-02',
        ],
    ],
];
