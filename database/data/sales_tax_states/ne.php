<?php

/*
 * Nebraska: Nebraska Department of Revenue, Sales Tax Permit. Researched
 * 2026-10-02 from revenue.nebraska.gov. Generated once from the EREG-13
 * sales tax research; edit this file directly from now on.
 */

return [
    'state' => 'NE',
    'name' => 'Nebraska',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Nebraska Department of Revenue',
        'short' => 'DOR',
        'url' => 'https://revenue.nebraska.gov/',
    ],
    'registration' => [
        'term' => 'Sales Tax Permit',
        'portal' => [
            'name' => 'Nebraska Tax Application online registration (Register Your New Business Online)',
            'url' => 'https://revenue.nebraska.gov/businesses/register-your-new-business-online',
        ],
        'form' => [
            'number' => '20',
            'title' => 'Nebraska Tax Application',
            'pdf_url' => 'https://revenue.nebraska.gov/sites/default/files/doc/business/f_20.pdf',
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'No registration fee is listed on Form 20 or in Reg-1-004. A $25 fee ($50 for later revocations) and a security deposit apply only to reissuing a permit after revocation.',
            'cite' => 'Neb. Admin. Code Reg-1-004.08; Form 20 (Rev. 6-2022)',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'The permit is permanent and does not expire. It is not transferable and is valid only for the location shown.',
            'cite' => 'Neb. Admin. Code Reg-1-004.05',
        ],
        'timing' => [
            'online' => 'Most permits are available online immediately upon approval',
            'paper' => null,
            'temporary_number' => false,
            'summary' => 'DOR\'s Form 20 says most permits are available online immediately upon approval when you register online. DOR does not publish a processing time for paper applications mailed or faxed to 402-471-5927.',
            'cite' => 'Form 20, Nebraska Tax Application (Rev. 6-2022), header',
        ],
        'number' => [
            'name' => 'Nebraska Sales Tax ID Number',
            'format' => 'Printed on the permit; resale certificates show it after the prefix 01-. The FEIN is not a substitute.',
            'cite' => 'Form 10 instructions (How to Obtain a Permit); Form 13',
        ],
    ],
    'nexus' => [
        'physical' => 'A retailer that keeps an office, warehouse or other place of business in Nebraska, has agents, salespeople or representatives selling or delivering there, or rents property in the state is engaged in business and needs a permit for each location.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => 200,
            'period' => 'prior calendar year or current calendar year',
            'effective' => '2019-04-01',
            'cite' => 'LB 284 (2019); Neb. Rev. Stat. 77-2701.13; DOR Remote Seller and Marketplace Facilitator FAQs',
        ],
        'marketplace' => 'Multivendor Marketplace Platforms (MMPs) that meet the same $100,000 or 200-transaction threshold must hold a sales tax permit and collect Nebraska and local tax on the sales they facilitate (LB 284, effective April 1, 2019).',
    ],
    'filing' => [
        'frequencies' => 'monthly, quarterly or annually by annual tax liability',
        'rule' => 'Monthly if annual sales tax liability is $3,000 or more; quarterly if $900 to $2,999; annually if less than $900. Changes in frequency need DOR approval (Form 22).',
        'due_day' => '20th of the month after the period (annual returns due January 20)',
        'zero_return_required' => true,
        'prepayments' => null,
        'cite' => 'DOR Information Guide 4-787 (April 2024), Reporting and Payment of Taxes; Form 10 instructions',
    ],
    'rates' => [
        'state_rate_pct' => 5.5,
        'local' => 'Cities, villages and some counties add local sales tax, commonly 0.5% to 2%. Inside a Good Life District within city limits the state rate is 2.75%.',
        'sourcing' => 'destination-based (Streamlined Sales Tax member)',
        'cite' => 'DOR Nebraska Sales and Use Tax page, local rate notices and LB 1317 (2024) Good Life District notice',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'Local sales taxes are administered by DOR and reported on Schedule I of Form 10. No separate city or county registration.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Doing business as a retailer without a permit is a misdemeanor (Class IV per DOR guide), with a fine of up to $500 for each day of operation.',
            'cite' => 'Neb. Admin. Code Reg-1-004.09; Neb. Rev. Stat. 77-2705; DOR Information Guide 4-787',
        ],
        'late_filing' => [
            'summary' => 'A return not filed or not paid by the due date may be assessed a penalty of 10% of the tax due or $25, whichever is greater, plus interest.',
            'cite' => 'Neb. Rev. Stat. 77-2708; Form 10 instructions',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales tax',
            'use tax',
            'income tax withholding',
            'business income tax',
            'lodging tax',
            'tire fee',
            'litter fee',
            'prepaid wireless surcharge',
        ],
        'prerequisites' => 'An EIN generally comes first; if not yet issued, DOR asks for it later on Form 22. A separate application is needed for each Nebraska retail location unless registering through Streamlined.',
        'cite' => 'Form 20 instructions, Line 2; Reg-1-004.03',
    ],
    'facts' => [
        [
            'text' => 'Nebraska requires a separate sales tax permit for each retail location, unless the seller registers through the Streamlined Sales Tax Registration System. The permit must be displayed at the place of business.',
            'source_url' => 'https://revenue.nebraska.gov/sites/default/files/doc/legal/regs/1-004.pdf',
        ],
        [
            'text' => 'Retailers keep a sales tax collection fee of 2.5% of the tax due, capped at $75 per return (Form 10 line 8).',
            'source_url' => 'https://revenue.nebraska.gov/sites/default/files/doc/tax-forms/f_10.pdf',
        ],
        [
            'text' => 'LB 1317 (2024) cut the state rate to 2.75% for sales sourced to a Good Life District inside city limits, starting July 1, 2024. A planned 2.75% Good Life District local option tax was cancelled by LB 707 before its July 1, 2025 start.',
            'source_url' => 'https://revenue.nebraska.gov/businesses/nebraska-sales-and-use-tax',
        ],
        [
            'text' => 'Nebraska is a Streamlined Sales Tax member, so remote sellers can register through sstregister.org and may qualify for free Certified Service Provider services.',
            'source_url' => 'https://revenue.nebraska.gov/about/frequently-asked-questions/remote-seller-and-marketplace-facilitator-faqs',
        ],
        [
            'text' => 'Local rate changes take effect only at the start of a calendar quarter; for example, Winside starts a 1% local tax on January 1, 2027.',
            'source_url' => 'https://revenue.nebraska.gov/businesses/nebraska-sales-and-use-tax',
        ],
    ],
    'state_notes' => 'In Nebraska the registration is called a Sales Tax Permit. The Nebraska Department of Revenue (DOR) issues it. You apply on the Nebraska Tax Application, Form 20. You can file it online through DOR\'s Register Your New Business Online page, or mail or fax the paper form. DOR says most permits are available online right after approval. There is no fee to register. The permit does not expire, but you need a separate permit for each retail location in Nebraska. Get your EIN first if you need one. Remote sellers must register once they pass $100,000 in Nebraska sales or 200 transactions in the current or prior calendar year. After you register, you file Form 10. DOR assigns monthly filing if your yearly sales tax is $3,000 or more, quarterly for $900 to $2,999, and annual below $900. Returns are due on the 20th of the month after the period. You must file even when you owe nothing. If you file late, the penalty is 10% of the tax or $25, whichever is more. The one mistake to avoid: opening a second store under your first permit. Each location needs its own permit, and selling without one is a misdemeanor with fines of up to $500 a day.',
    'sources' => [
        [
            'title' => 'Register Your New Business Online (Nebraska DOR)',
            'url' => 'https://revenue.nebraska.gov/businesses/register-your-new-business-online',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Form 20, Nebraska Tax Application',
            'url' => 'https://revenue.nebraska.gov/sites/default/files/doc/business/f_20.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Reg-1-004, Permits',
            'url' => 'https://revenue.nebraska.gov/sites/default/files/doc/legal/regs/1-004.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Remote Seller and Marketplace Facilitator FAQs',
            'url' => 'https://revenue.nebraska.gov/about/frequently-asked-questions/remote-seller-and-marketplace-facilitator-faqs',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Form 10, Nebraska and Local Sales and Use Tax Return and instructions',
            'url' => 'https://revenue.nebraska.gov/sites/default/files/doc/tax-forms/f_10.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Information Guide 4-787, Statutory Responsibilities for Collecting, Reporting, and Remitting Sales Taxes',
            'url' => 'https://revenue.nebraska.gov/sites/default/files/doc/info/4-787.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Nebraska Sales and Use Tax (rate notices)',
            'url' => 'https://revenue.nebraska.gov/businesses/nebraska-sales-and-use-tax',
            'accessed' => '2026-10-02',
        ],
    ],
];
