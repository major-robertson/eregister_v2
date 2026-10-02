<?php

/*
 * North Carolina: North Carolina Department of Revenue, Certificate of
 * Registration. Researched 2026-10-02 from ncdor.gov, ncleg.net,
 * salestaxinstitute.com. Generated once from the EREG-13 sales tax
 * research; edit this file directly from now on.
 */

return [
    'state' => 'NC',
    'name' => 'North Carolina',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'North Carolina Department of Revenue',
        'short' => 'NCDOR',
        'url' => 'https://www.ncdor.gov/',
    ],
    'registration' => [
        'term' => 'Certificate of Registration',
        'portal' => [
            'name' => 'NCDOR Online Business Registration',
            'url' => 'https://www.ncdor.gov/registration',
        ],
        'form' => [
            'number' => 'NC-BR',
            'title' => 'Business Registration Application for Income Tax Withholding, Sales and Use Tax, and Other Taxes and Service Charge',
            'pdf_url' => 'https://www.ncdor.gov/documents/files/form-nc-br-business-registration-application-income-tax-withholding-sales-and-use-tax-and-other/open',
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'No fee is charged for a Certificate of Registration.',
            'cite' => 'NCDOR Sales and Use Tax Frequently Asked Questions',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'The certificate stays valid unless revoked, but it becomes void if for 18 months you file no returns or only returns showing no sales.',
            'cite' => 'N.C.G.S. 105-164.29(c); NCDOR Sales and Use Tax FAQ',
        ],
        'timing' => [
            'online' => 'Account number instantly; certificate mailed within ten business days',
            'paper' => 'Up to four weeks',
            'temporary_number' => true,
            'summary' => 'Most applicants who register online receive their account number instantly, and the Certificate of Registration is mailed within ten business days. A registration not filed electronically may take up to four weeks.',
            'cite' => 'NCDOR Sales and Use Tax Frequently Asked Questions',
        ],
        'number' => [
            'name' => 'NCDOR account ID (sales and use tax Certificate of Registration number)',
            'format' => null,
            'cite' => 'NCDOR Business Registration page',
        ],
    ],
    'nexus' => [
        'physical' => 'Any person engaging in business in North Carolina as a retailer or wholesale merchant, including flea market vendors and sellers with a physical presence, must obtain a certificate before doing business.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => null,
            'period' => 'previous or current calendar year (gross sales sourced to North Carolina, including marketplace sales)',
            'effective' => '2024-07-01',
            'cite' => 'N.C.G.S. 105-164.8(b); Session Law 2024-28 (repealed the 200-transaction test); NCDOR Remote Sales page',
        ],
        'marketplace' => 'A marketplace facilitator engaged in business in North Carolina (including over $100,000 in gross sales sourced to the state) is the retailer of each facilitated sale and must collect and remit tax on all of them (NCDOR Marketplace Facilitators page; Sales and Use Tax Bulletin 59).',
    ],
    'filing' => [
        'frequencies' => 'monthly, quarterly, or monthly with prepayment',
        'rule' => 'Quarterly if tax is consistently less than $100 a month; monthly if more than $100 but less than $20,000 a month; monthly with prepayment if at least $20,000 a month (must file and pay electronically). Seasonal filers (six or fewer months a year) skip off-season periods.',
        'due_day' => 'Monthly returns by the 20th of the following month; quarterly returns by the last day of January, April, July and October',
        'zero_return_required' => true,
        'prepayments' => 'Monthly with prepayment filers (at least $20,000 a month) include a prepayment of the next month\'s tax',
        'cite' => 'NCDOR Sales and Use Tax Frequently Asked Questions',
    ],
    'rates' => [
        'state_rate_pct' => 4.75,
        'local' => 'Counties add 2% to 3.5% local tax; combined general rates run from 6.75% to 8.25% (Mecklenburg County since July 1, 2026).',
        'sourcing' => 'destination-based (Streamlined Sales Tax member)',
        'cite' => 'NCDOR Important Notice: Mecklenburg County Sales and Use Tax Increase; NCDOR Sales and Use Tax FAQ',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'County sales taxes are administered by NCDOR on the same return (Form E-500). One certificate covers all of a legal entity\'s locations in the state.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'No fixed fine for operating without a certificate is set out on NCDOR\'s pages. Failing to comply is grounds for revoking the certificate, and willful failure to collect or pay over tax is a Class 1 misdemeanor.',
            'cite' => 'N.C.G.S. 105-164.29(d); N.C.G.S. 105-236(a)(8)',
        ],
        'late_filing' => [
            'summary' => 'Failure to file: 5% of the tax per month or part of a month, up to 25%. Failure to pay: 5%. Interest runs from the due date.',
            'cite' => 'N.C.G.S. 105-236(a); NCDOR Sales and Use Tax FAQ',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales and use tax',
            'income tax withholding',
            'other taxes listed on Form NC-BR',
        ],
        'prerequisites' => 'SSN or FEIN, and the North Carolina Secretary of State number if the business is registered there.',
        'cite' => 'NCDOR Business Registration page; Form NC-BR',
    ],
    'facts' => [
        [
            'text' => 'Mecklenburg County added a 1% county sales tax on July 1, 2026, raising the combined general rate there from 7.25% to 8.25%.',
            'source_url' => 'https://www.ncdor.gov/taxes-forms/sales-and-use-tax/other-sales-and-use-tax-resources/important-notices-issued-sales-and-use-tax-division/important-notice-mecklenburg-county-sales-and-use-tax-increase',
        ],
        [
            'text' => 'Since July 2, 2026, a remote seller whose only link is the $100,000 threshold must register and start collecting by the first day of the first month at least 60 days after it passes the threshold.',
            'source_url' => 'https://www.ncdor.gov/taxes-forms/sales-and-use-tax/other-sales-and-use-tax-resources/remote-sales',
        ],
        [
            'text' => 'North Carolina dropped its 200-transaction test for remote sellers effective July 1, 2024 (Session Law 2024-28), leaving only the $100,000 sales test.',
            'source_url' => 'https://www.salestaxinstitute.com/resources/north-carolina-repeals-transaction-count-from-economic-nexus-threshold',
        ],
        [
            'text' => 'One Certificate of Registration covers all of a legal entity\'s businesses and locations in the state, and a copy must be displayed at each place of business. A new owner or a business that incorporates needs a new certificate.',
            'source_url' => 'https://www.ncleg.net/EnactedLegislation/Statutes/HTML/BySection/Chapter_105/GS_105-164.29.html',
        ],
        [
            'text' => 'Wholesale-only merchants must still hold a certificate of registration even though they generally do not file returns.',
            'source_url' => 'https://www.ncdor.gov/taxes-forms/sales-and-use-tax/sales-and-use-tax-frequently-asked-questions',
        ],
    ],
    'state_notes' => 'In North Carolina the sales tax registration is called a Certificate of Registration. The North Carolina Department of Revenue (NCDOR) issues it. You can register online through NCDOR\'s Online Business Registration, or mail Form NC-BR, which also covers income tax withholding. There is no fee. Online, most applicants get their account number right away, and the certificate is mailed within ten business days. Paper applications can take up to four weeks. Have your SSN or FEIN and your Secretary of State number ready. One certificate covers all of your locations; display a copy at each one. Remote sellers must register once they pass $100,000 in North Carolina sales in the current or prior calendar year. There is no transaction count. After you register, NCDOR sets your filing frequency. Most businesses file Form E-500 monthly, due on the 20th. Very small accounts, under $100 a month, file quarterly. Accounts with $20,000 or more a month also prepay. File a return even when you had no sales. Late filing costs 5% a month, up to 25%. The one mistake to avoid: letting the account go quiet. Eighteen months of missing or zero returns voids the certificate.',
    'sources' => [
        [
            'title' => 'NCDOR Sales and Use Tax Frequently Asked Questions',
            'url' => 'https://www.ncdor.gov/taxes-forms/sales-and-use-tax/sales-and-use-tax-frequently-asked-questions',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'NCDOR Business Registration',
            'url' => 'https://www.ncdor.gov/registration',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'NCDOR Remote Sales',
            'url' => 'https://www.ncdor.gov/taxes-forms/sales-and-use-tax/other-sales-and-use-tax-resources/remote-sales',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'NCDOR Marketplace Facilitators and Marketplace Sellers',
            'url' => 'https://www.ncdor.gov/taxes-forms/sales-and-use-tax/marketplace-facilitators-and-marketplace-sellers',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Important Notice: Mecklenburg County Sales and Use Tax Increase',
            'url' => 'https://www.ncdor.gov/taxes-forms/sales-and-use-tax/other-sales-and-use-tax-resources/important-notices-issued-sales-and-use-tax-division/important-notice-mecklenburg-county-sales-and-use-tax-increase',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'N.C.G.S. 105-164.29',
            'url' => 'https://www.ncleg.net/EnactedLegislation/Statutes/HTML/BySection/Chapter_105/GS_105-164.29.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'N.C.G.S. 105-236',
            'url' => 'https://www.ncleg.net/EnactedLegislation/Statutes/HTML/BySection/Chapter_105/GS_105-236.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Sales Tax Institute: North Carolina repeals transaction count (cross-check)',
            'url' => 'https://www.salestaxinstitute.com/resources/north-carolina-repeals-transaction-count-from-economic-nexus-threshold',
            'accessed' => '2026-10-02',
        ],
    ],
];
