<?php

/*
 * Massachusetts: Massachusetts Department of Revenue, Sales and Use Tax
 * Registration Certificate (Form ST-1). Researched 2026-10-02 from
 * mass.gov, malegislature.gov. Generated once from the EREG-13 sales tax
 * research; edit this file directly from now on.
 */

return [
    'state' => 'MA',
    'name' => 'Massachusetts',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Massachusetts Department of Revenue',
        'short' => 'DOR',
        'url' => 'https://www.mass.gov/orgs/massachusetts-department-of-revenue',
    ],
    'registration' => [
        'term' => 'Sales and Use Tax Registration Certificate (Form ST-1)',
        'portal' => [
            'name' => 'MassTaxConnect (Register a New Business)',
            'url' => 'https://mtc.dor.state.ma.us/mtc/_/',
        ],
        'form' => [
            'number' => null,
            'title' => 'Online registration in MassTaxConnect (DOR certificate issued: Form ST-1)',
            'pdf_url' => null,
            'online_only' => true,
        ],
        'fee' => [
            'amount_cents' => null,
            'summary' => 'DOR\'s registration instructions mention no fee. By statute, any registration fee is set annually by the Commissioner of Administration; no current fee amount was found.',
            'cite' => 'G.L. c. 62C, s. 67; https://www.mass.gov/info-details/register-your-business-with-masstaxconnect',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'DOR does not describe a renewal. By statute, certificates may be issued for a term of at least three years and renewed without an additional fee; they can be suspended or revoked.',
            'cite' => 'G.L. c. 62C, s. 67',
        ],
        'timing' => [
            'online' => null,
            'paper' => null,
            'temporary_number' => false,
            'summary' => 'Applicants get a confirmation email when the online registration is submitted; once approved, DOR mails a Form ST-1 certificate for each location. DOR does not publish a processing time.',
            'cite' => 'https://www.mass.gov/info-details/register-your-business-with-masstaxconnect',
        ],
        'number' => [
            'name' => 'Massachusetts Account ID (or Federal ID number)',
            'format' => null,
            'cite' => null,
        ],
    ],
    'nexus' => [
        'physical' => 'A vendor that sells, rents or leases tangible personal property or telecommunications services in Massachusetts, has a business location or soliciting representatives there, or delivers, repairs or installs goods there must register before opening.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => null,
            'period' => 'prior or current calendar year (Massachusetts sales, including exempt sales)',
            'effective' => '2019-10-01',
            'cite' => '830 CMR 64H.1.9; https://www.mass.gov/info-details/remote-seller-and-marketplace-facilitator-faqs',
        ],
        'marketplace' => 'Since Oct. 1, 2019, a marketplace whose direct and facilitated Massachusetts sales exceed $100,000 in a calendar year must register and collect on all facilitated sales, including software subscriptions (830 CMR 64H.1.9; DOR Remote Seller and Marketplace Facilitator FAQs).',
    ],
    'filing' => [
        'frequencies' => 'monthly, quarterly or annual by tax collected per year',
        'rule' => '$100 or less a year: annual. $101 to $1,200: quarterly. $1,201 or more: monthly. New businesses must file and pay electronically. A return is required for every period even when no tax is due.',
        'due_day' => '30th day after the end of the period',
        'zero_return_required' => true,
        'prepayments' => 'Vendors with over $150,000 in cumulative tax liability in the prior year must make advance payments before the return is due (G.L. c. 62C, s. 16B).',
        'cite' => 'https://www.mass.gov/guides/sales-and-use-tax; AP 616.3',
    ],
    'rates' => [
        'state_rate_pct' => 6.25,
        'local' => 'No local general sales tax. Cities and towns may adopt a 0.75% local option meals excise, for 7% on restaurant meals.',
        'sourcing' => null,
        'cite' => 'https://www.mass.gov/guides/sales-and-use-tax; https://www.mass.gov/guides/sales-tax-on-meals',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'No local registration. The local option meals excise is collected under the state meals tax registration with DOR.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => null,
            'cite' => null,
        ],
        'late_filing' => [
            'summary' => 'Late filing: 1% of the balance due per month or part of a month, up to 25%. Late payment: 1% of the unpaid tax per month, up to 25%. Interest is the federal short-term rate plus 4%, compounded daily.',
            'cite' => 'https://www.mass.gov/guides/sales-and-use-tax (G.L. c. 62C, s. 33)',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales and use tax',
            'sales tax on meals',
            'telecommunications services',
            'room occupancy excise',
            'withholding tax',
            'marijuana retail taxes',
            'Paid Family and Medical Leave',
        ],
        'prerequisites' => 'EIN for all businesses (sole proprietors without employees use an SSN); officer names, titles and SSNs for non-sole-proprietors. Entities register with the Secretary of the Commonwealth separately.',
        'cite' => 'AP 616.2; https://www.mass.gov/info-details/register-your-business-with-masstaxconnect',
    ],
    'facts' => [
        [
            'text' => 'Massachusetts taxes standardized software however delivered, and charges to access software on a remote server (SaaS). Other digital products like music, video and e-books delivered electronically are not taxed.',
            'source_url' => 'https://www.mass.gov/guides/sales-and-use-tax',
        ],
        [
            'text' => 'Clothing is exempt up to $175 per item; tax applies only to the amount over $175.',
            'source_url' => 'https://www.mass.gov/guides/sales-and-use-tax',
        ],
        [
            'text' => 'Since a FY21 budget change, sales tax returns are due on the 30th day after the period, and vendors with over $150,000 in prior-year liability make advance payments.',
            'source_url' => 'https://www.mass.gov/guides/sales-and-use-tax',
        ],
        [
            'text' => 'A business must register with DOR before it opens, and must display a Form ST-1 certificate at each location.',
            'source_url' => 'https://www.mass.gov/administrative-procedure/ap-616-registration-information-sales-tax-meals-tax-room-occupancy-excise-withholding-tax-and-other-miscellaneous-excises',
        ],
        [
            'text' => 'Massachusetts has no transaction-count test for remote sellers; only the $100,000 sales test applies.',
            'source_url' => 'https://www.mass.gov/info-details/remote-seller-and-marketplace-facilitator-faqs',
        ],
    ],
    'state_notes' => 'In Massachusetts, you register with the Department of Revenue (DOR) to collect sales and use tax. DOR then mails a Sales and Use Tax Registration Certificate, Form ST-1, for each business location. Registration is online only, through MassTaxConnect. Choose Register a New Business and have your EIN, start date and owner details ready. You must register before you open. You get a confirmation email when you apply, and the certificate comes by mail once approved. DOR does not publish a processing time. Post the certificate where customers can see it. DOR sets your filing schedule by how much tax you collect. Over $1,200 a year files monthly, $101 to $1,200 quarterly, and $100 or less annually. Returns are due on the 30th day after the period, and new businesses must file and pay online. Large vendors also make advance payments. The rate is 6.25%, with no local sales tax, though many towns add 0.75% on restaurant meals. Software, including SaaS, is taxable. Remote sellers must register once Massachusetts sales pass $100,000 in a calendar year. The mistake to avoid: skipping zero returns. DOR requires a return every period, even when no tax is due.',
    'sources' => [
        [
            'title' => 'DOR: Sales and Use Tax guide (updated May 7, 2026)',
            'url' => 'https://www.mass.gov/guides/sales-and-use-tax',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DOR: Register Your Business with MassTaxConnect',
            'url' => 'https://www.mass.gov/info-details/register-your-business-with-masstaxconnect',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'AP 616: Registration Information',
            'url' => 'https://www.mass.gov/administrative-procedure/ap-616-registration-information-sales-tax-meals-tax-room-occupancy-excise-withholding-tax-and-other-miscellaneous-excises',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DOR: Remote Seller and Marketplace Facilitator FAQs',
            'url' => 'https://www.mass.gov/info-details/remote-seller-and-marketplace-facilitator-faqs',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DOR: Sales Tax on Meals',
            'url' => 'https://www.mass.gov/guides/sales-tax-on-meals',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'G.L. c. 62C, s. 67',
            'url' => 'https://malegislature.gov/Laws/GeneralLaws/PartI/TitleIX/Chapter62C/Section67',
            'accessed' => '2026-10-02',
        ],
    ],
];
