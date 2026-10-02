<?php

/*
 * Arkansas: Arkansas Department of Finance and Administration, Sales and
 * Use Tax Section, Sales and Use Tax Permit. Researched 2026-10-02 from
 * dfa.arkansas.gov, law.cornell.edu, law.justia.com, kark.com. Generated
 * once from the EREG-13 sales tax research; edit this file directly from
 * now on.
 */

return [
    'state' => 'AR',
    'name' => 'Arkansas',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Arkansas Department of Finance and Administration, Sales and Use Tax Section',
        'short' => 'DFA',
        'url' => 'https://www.dfa.arkansas.gov/office/taxes/excise-tax-administration/sales-use-tax/',
    ],
    'registration' => [
        'term' => 'Sales and Use Tax Permit',
        'portal' => [
            'name' => 'Arkansas Taxpayer Access Point (ATAP)',
            'url' => 'https://atap.arkansas.gov/?link=Register',
        ],
        'form' => [
            'number' => 'AR-1R',
            'title' => 'Combined Business Tax Registration (filed online in ATAP; no current paper PDF confirmed)',
            'pdf_url' => null,
            'online_only' => null,
        ],
        'fee' => [
            'amount_cents' => 5000,
            'summary' => '$50 sales tax permit fee, paid electronically when the ATAP registration is submitted. Other tax liabilities must be cleared before a new permit is issued.',
            'cite' => 'https://www.dfa.arkansas.gov/office/taxes/excise-tax-administration/sales-use-tax/register-for-a-tax-account/',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'No renewal was found on DFA\'s pages; secondary sources describe the $50 fee as one-time with no renewal fee.',
            'cite' => null,
        ],
        'timing' => [
            'online' => 'up to 2 weeks',
            'paper' => null,
            'temporary_number' => false,
            'summary' => 'DFA says processing takes up to 2 weeks.',
            'cite' => 'https://www.dfa.arkansas.gov/office/taxes/excise-tax-administration/sales-use-tax/register-for-a-tax-account/',
        ],
        'number' => [
            'name' => 'Arkansas sales/use tax permit number',
            'format' => null,
            'cite' => null,
        ],
    ],
    'nexus' => [
        'physical' => 'A business with a place of business, inventory or staff in Arkansas that makes taxable sales must hold a sales and use tax permit before selling.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => 200,
            'period' => 'current or previous calendar year (sales of tangible personal property, taxable services, digital codes or specified digital products delivered into Arkansas; either test)',
            'effective' => '2019-07-01',
            'cite' => 'Act 822 of 2019; https://www.dfa.arkansas.gov/office/taxes/excise-tax-administration/sales-use-tax/remote-sellers/',
        ],
        'marketplace' => 'A marketplace facilitator that meets the same $100,000 or 200-transaction threshold must collect and remit sales and use tax on the sales it facilitates, effective July 1, 2019 (Act 822 of 2019; https://www.dfa.arkansas.gov/office/taxes/excise-tax-administration/sales-use-tax/remote-sellers/).',
    ],
    'filing' => [
        'frequencies' => 'monthly; quarterly or annual on DFA\'s notice for small accounts',
        'rule' => 'Monthly by default. DFA may allow quarterly filing if average tax for the prior fiscal year (July to June) is $100 a month or less, and annual filing if it is $25 a month or less.',
        'due_day' => '20th of the month after the period (annual returns January 20)',
        'zero_return_required' => null,
        'prepayments' => 'Retailers with average net sales over $200,000 a month for the prior fiscal year must prepay by EFT from the next January 1: either 40% of average monthly tax by the 12th and again by the 24th, or at least 80% of the month\'s liability by the 24th. Timely prepayers get a discount of the lesser of 2% or $1,000.',
        'cite' => 'Ark. Gross Receipts Tax Rule GR-77 (006.05.06-005), https://www.law.cornell.edu/regulations/arkansas/006-05-06-Ark-Code-R-005-GR-77; Ark. Code 26-52-512; https://www.dfa.arkansas.gov/office/taxes/excise-tax-administration/sales-use-tax/due-dates/',
    ],
    'rates' => [
        'state_rate_pct' => 6.5,
        'local' => 'City and county sales taxes apply on top of the 6.5% state rate; DFA collects them on the state return.',
        'sourcing' => 'destination: local tax is based on the point of delivery',
        'cite' => 'https://www.dfa.arkansas.gov/office/taxes/excise-tax-administration/sales-use-tax/sales-and-use-tax-faqs/',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => null,
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => null,
            'cite' => null,
        ],
        'late_filing' => [
            'summary' => 'Failure to file: 5% of the tax for the first month plus 5% for each further month or part of a month, up to 35% in total.',
            'cite' => 'Ark. Code 26-18-208',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales tax',
            'use tax',
            'other DFA tax types available in the same ATAP registration (such as withholding)',
        ],
        'prerequisites' => 'Any other Arkansas tax liabilities must be cleared first. Have a signed lease or bill of sale if leasing a location or buying an existing business, and the Arkansas start date. The location address cannot be a P.O. box.',
        'cite' => 'https://www.dfa.arkansas.gov/office/taxes/excise-tax-administration/sales-use-tax/register-for-a-tax-account/',
    ],
    'facts' => [
        [
            'text' => 'Act 1008 of 2025 removed the 0.125% state sales tax on groceries from January 1, 2026; local sales taxes on groceries still apply.',
            'source_url' => 'https://www.kark.com/news/state-news/arkansas-removing-state-sales-tax-on-groceries-in-2026/',
        ],
        [
            'text' => 'Arkansas is a Streamlined Sales Tax member state; remote sellers can register through the Streamlined Sales Tax Registration System (sstregister.org) or directly in ATAP.',
            'source_url' => 'https://www.dfa.arkansas.gov/office/taxes/excise-tax-administration/sales-use-tax/remote-sellers/',
        ],
        [
            'text' => 'Arkansas\'s remote seller threshold still counts 200 transactions as an alternative to $100,000 in sales.',
            'source_url' => 'https://www.dfa.arkansas.gov/office/taxes/excise-tax-administration/sales-use-tax/remote-sellers/',
        ],
        [
            'text' => 'Local sales tax is sourced to the point of delivery; a seller delivering into Arkansas remits the city and county tax where the items are first delivered.',
            'source_url' => 'https://www.dfa.arkansas.gov/office/taxes/excise-tax-administration/sales-use-tax/sales-and-use-tax-faqs/',
        ],
        [
            'text' => 'Arkansas sales for resale can be documented with Form ST391 or the Streamlined certificate of exemption.',
            'source_url' => 'https://www.dfa.arkansas.gov/excise-tax/sales-and-use-tax/sales-and-use-tax-forms/',
        ],
    ],
    'state_notes' => 'In Arkansas, you need a Sales and Use Tax Permit. The Arkansas Department of Finance and Administration (DFA) issues it. You register online through the Arkansas Taxpayer Access Point (ATAP). The registration is the AR-1R Combined Business Tax Registration. The permit fee is $50, paid when you submit. DFA says processing can take up to 2 weeks. DFA will not issue a new permit if you owe other Arkansas taxes, so clear those first. You will need your Arkansas start date, and a signed lease or bill of sale if you lease a location or bought a business. The address cannot be a P.O. box. After you register, you file monthly by the 20th. DFA may move small accounts to quarterly or annual filing. Large sellers, with average sales over $200,000 a month, must also make prepayments by electronic transfer. The state rate is 6.5%, and city and county taxes are added. You report them all on the state return; you do not register with cities. The one mistake to avoid: charging the local rate where your store is for goods you deliver. Arkansas local tax follows the point of delivery.',
    'sources' => [
        [
            'title' => 'DFA: Register for a Tax Account',
            'url' => 'https://www.dfa.arkansas.gov/office/taxes/excise-tax-administration/sales-use-tax/register-for-a-tax-account/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DFA: Remote Sellers and Marketplace Facilitators',
            'url' => 'https://www.dfa.arkansas.gov/office/taxes/excise-tax-administration/sales-use-tax/remote-sellers/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DFA: Sales and Use Tax FAQs',
            'url' => 'https://www.dfa.arkansas.gov/office/taxes/excise-tax-administration/sales-use-tax/sales-and-use-tax-faqs/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'DFA: Sales and Use Tax Due Dates',
            'url' => 'https://www.dfa.arkansas.gov/office/taxes/excise-tax-administration/sales-use-tax/due-dates/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Ark. Gross Receipts Tax Rule GR-77 (Cornell LII)',
            'url' => 'https://www.law.cornell.edu/regulations/arkansas/006-05-06-Ark-Code-R-005-GR-77',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Ark. Code 26-52-512 (Justia, via search)',
            'url' => 'https://law.justia.com/codes/arkansas/title-26/subtitle-5/chapter-52/subchapter-5/section-26-52-512/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Ark. Code 26-18-208 (Justia, via search)',
            'url' => 'https://law.justia.com/codes/arkansas/title-26/subtitle-2/chapter-18/subchapter-2/section-26-18-208/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'KARK: Arkansas removing state sales tax on groceries in 2026',
            'url' => 'https://www.kark.com/news/state-news/arkansas-removing-state-sales-tax-on-groceries-in-2026/',
            'accessed' => '2026-10-02',
        ],
    ],
];
