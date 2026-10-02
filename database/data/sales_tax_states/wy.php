<?php

/*
 * Wyoming: Wyoming Department of Revenue, Excise Tax Division, Sales Tax
 * License. Researched 2026-10-02 from excise-tax-div.wyo.gov, wyoleg.gov,
 * law.cornell.edu. Generated once from the EREG-13 sales tax research;
 * edit this file directly from now on.
 */

return [
    'state' => 'WY',
    'name' => 'Wyoming',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Wyoming Department of Revenue, Excise Tax Division',
        'short' => 'the Excise Tax Division',
        'url' => 'https://excise-tax-div.wyo.gov/',
    ],
    'registration' => [
        'term' => 'Sales Tax License',
        'portal' => [
            'name' => 'Wyoming Internet Filing System (WYIFS)',
            'url' => 'https://excise-tax-div.wyo.gov/wyifs',
        ],
        'form' => [
            'number' => null,
            'title' => 'Sales/Use/Lodging Tax Application - Regular Vendors',
            'pdf_url' => null,
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 6000,
            'summary' => 'A $60 license fee is required from each new vendor (remote vendors with no duty to register, or using a Streamlined technology model, are exempt). Reinstating a license forfeited for failing to file costs $60.',
            'cite' => 'W.S. 39-15-106(a), as amended by 2024 Wyo. Sess. Laws, Enrolled Act 38 (HB0197)',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'No periodic renewal. A license can be forfeited for failing to file returns ($60 to reinstate), and a vendor reporting no gross sales for three years must show cause why it should not be revoked.',
            'cite' => 'W.S. 39-15-106(a)',
        ],
        'timing' => [
            'online' => 'about two weeks for the whole two-step process',
            'paper' => 'longer than online because of mailing time',
            'temporary_number' => false,
            'summary' => 'Applicants first open a WYIFS account; once it is approved, they apply for the license in WYIFS. The Division says the entire process generally takes about two weeks. Paper applications are still processed but take longer.',
            'cite' => 'Excise Tax Division FAQs (https://excise-tax-div.wyo.gov/general-administrative/excise-tax-faqs)',
        ],
        'number' => [
            'name' => 'Wyoming sales tax license number',
            'format' => null,
            'cite' => null,
        ],
    ],
    'nexus' => [
        'physical' => 'Every vendor engaged in the business of selling taxable goods, admissions or services in Wyoming must obtain a sales tax license from the Department (W.S. 39-15-106(a)).',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => null,
            'period' => 'current calendar year or immediately preceding calendar year (gross revenue from sales delivered into Wyoming must exceed $100,000)',
            'effective' => '2024-07-01',
            'cite' => 'W.S. 39-15-501(a)(i); 2024 Wyo. Sess. Laws, Enrolled Act 38 (HB0197), which repealed the 200-transaction test in 39-15-501(a)(ii) effective July 1, 2024',
        ],
        'marketplace' => 'A marketplace facilitator over the remote seller threshold is treated as the vendor and must collect and remit tax on its own sales and on all sales it facilitates for marketplace sellers into Wyoming, whether or not those sellers hold a permit (W.S. 39-15-502).',
    ],
    'filing' => [
        'frequencies' => 'monthly, quarterly or annual',
        'rule' => 'The Department assigns a filing frequency at licensing and may change it based on the volume of tax collected and other criteria in its policy guidelines. The dollar thresholds are not stated in the rule.',
        'due_day' => 'last day of the month after the period (quarterly: January 31, April 30, July 31, October 31; annual: January 31)',
        'zero_return_required' => null,
        'prepayments' => null,
        'cite' => 'Wyo. Admin. Code 011.0002.2 §5 (Reporting)',
    ],
    'rates' => [
        'state_rate_pct' => 4,
        'local' => 'County local option taxes vary by county',
        'sourcing' => 'destination: rates are based on where the customer takes possession of the item or service',
        'cite' => 'W.S. 39-15-104(a), (b); Excise Tax Division FAQs',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'Local option taxes are administered with the state license and return; no separate county sales tax registration was found.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Operating without the required license is a violation of the sales tax article with no specific penalty, which the catch-all provision makes a misdemeanor; each violation is a separate offense. The Department may also revoke a vendor\'s license after two written notices.',
            'cite' => 'W.S. 39-15-106(a); W.S. 39-15-108(c)(vii), (viii)',
        ],
        'late_filing' => [
            'summary' => 'After a missed return the Department sends a delinquency notice. It may impose a $10 penalty if the return is filed within 30 days of the notice, or $25 if not. Delinquent tax draws interest at the average prime rate plus 4% (capped at 18%), and deficiencies due to negligence add a 10% penalty.',
            'cite' => 'W.S. 39-15-108(b)(i), (c)(i), (c)(xii), (c)(xiii)',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales tax',
            'use tax',
            'lodging tax',
        ],
        'prerequisites' => null,
        'cite' => 'Excise Tax Division, Sales/Use/Lodging Registration page',
    ],
    'facts' => [
        [
            'text' => 'Wyoming dropped the 200-transaction test for remote sellers on July 1, 2024 (Enrolled Act 38, HB0197). Only gross revenue over $100,000 into Wyoming now counts.',
            'source_url' => 'https://wyoleg.gov/2024/Enroll/HB0197.pdf',
        ],
        [
            'text' => 'Wyoming charges a $60 fee for a new sales tax license, and the same $60 to reinstate a license forfeited for not filing returns.',
            'source_url' => 'https://wyoleg.gov/2024/Enroll/HB0197.pdf',
        ],
        [
            'text' => 'Wyoming rules require exemption and resale certificates to be in the Streamlined Sales Tax format (Wyo. Admin. Code 011.0002.2 §7(b)(i)), so the SST certificate is the form to use.',
            'source_url' => 'https://excise-tax-div.wyo.gov/general-administrative/excise-tax-faqs',
        ],
        [
            'text' => 'Registration is a two-step process in WYIFS: open a WYIFS account, then apply for the license once the account is approved.',
            'source_url' => 'https://excise-tax-div.wyo.gov/general-administrative/excise-tax-faqs',
        ],
        [
            'text' => 'The state rate is 4%, made up of a 3% base tax and a 1% additional tax in effect since July 1, 1993.',
            'source_url' => 'https://wyoleg.gov/statutes/compress/title39.pdf',
        ],
    ],
    'state_notes' => 'In Wyoming you need a sales tax license. The Wyoming Department of Revenue\'s Excise Tax Division issues it. You apply online through the Wyoming Internet Filing System (WYIFS) in two steps. First you open a WYIFS account, and once it is approved you apply for the license there. The Division says the whole process usually takes about two weeks. Paper applications are still accepted but take longer. The license costs $60, a one-time fee for new vendors. There is no renewal, but the license can be forfeited if you stop filing returns, and reinstating it costs another $60. Sellers outside Wyoming need a license once their sales into the state pass $100,000 in the current or previous calendar year. Since July 1, 2024, the number of transactions no longer counts. The state rate is 4%, and counties add local taxes. Tax is based on where the customer takes possession. The Division assigns monthly, quarterly or annual filing based on how much tax you collect. Returns are due the last day of the month after the period. The mistake to avoid: letting returns lapse. Missed returns can lead to forfeiture of the license and penalties.',
    'sources' => [
        [
            'title' => 'Wyoming Excise Tax Division FAQs',
            'url' => 'https://excise-tax-div.wyo.gov/general-administrative/excise-tax-faqs',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Wyoming Excise Tax Division: Sales/Use/Lodging Registration',
            'url' => 'https://excise-tax-div.wyo.gov/salesuselodging-tax/salesuselodging-registration',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Wyoming Excise Tax Division home',
            'url' => 'https://excise-tax-div.wyo.gov/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => '2024 Wyoming Enrolled Act 38 (HB0197), Sales tax revisions',
            'url' => 'https://wyoleg.gov/2024/Enroll/HB0197.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Wyoming Statutes Title 39 (compiled PDF)',
            'url' => 'https://wyoleg.gov/statutes/compress/title39.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Wyo. Admin. Code 011.0002.2 §5, Reporting (Cornell LII)',
            'url' => 'https://www.law.cornell.edu/regulations/wyoming/011-2-Wyo-Code-R-SS-2-5',
            'accessed' => '2026-10-02',
        ],
    ],
];
