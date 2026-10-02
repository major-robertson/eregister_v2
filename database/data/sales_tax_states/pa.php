<?php

/*
 * Pennsylvania: Pennsylvania Department of Revenue, Sales, Use and Hotel
 * Occupancy Tax License. Researched 2026-10-02 from pa.gov,
 * revenue-pa.custhelp.com, taxjar.com. Generated once from the EREG-13
 * sales tax research; edit this file directly from now on.
 */

return [
    'state' => 'PA',
    'name' => 'Pennsylvania',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Pennsylvania Department of Revenue',
        'short' => 'the Department of Revenue',
        'url' => 'https://www.pa.gov/agencies/revenue',
    ],
    'registration' => [
        'term' => 'Sales, Use and Hotel Occupancy Tax License',
        'portal' => [
            'name' => 'myPATH, Pennsylvania Online Business Tax Registration',
            'url' => 'https://mypath.pa.gov/_/',
        ],
        'form' => [
            'number' => null,
            'title' => 'Pennsylvania Online Business Tax Registration (myPATH)',
            'pdf_url' => null,
            'online_only' => true,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'No fee is listed for the license in the Department\'s registration guidance. All required Pennsylvania returns must be filed and taxes paid before a license is issued or renewed.',
            'cite' => 'REV-717, Retailer\'s Information Guide (02-26), How to Obtain a License; TaxJar cross-check',
        ],
        'renewal' => [
            'required' => true,
            'summary' => 'Licenses renew automatically every five years, provided there are no outstanding filing obligations or tax liabilities.',
            'cite' => 'REV-717 (02-26), How to Obtain a License',
        ],
        'timing' => [
            'online' => '7 to 10 business days for the mailed license',
            'paper' => null,
            'temporary_number' => false,
            'summary' => 'Registration is done online in myPATH. The Department\'s help center says the registration packet and license are mailed 7 to 10 business days after you complete registration; account details are also sent by email.',
            'cite' => 'PA Department of Revenue Customer Service Center answers 200 and 4152 (read via search excerpts)',
        ],
        'number' => [
            'name' => 'PA Sales Tax License number / 8-digit Account ID',
            'format' => '8 digits',
            'cite' => 'REV-717 (02-26), TeleFile identifiers',
        ],
    ],
    'nexus' => [
        'physical' => 'Every person or entity making taxable sales of tangible personal property or services in Pennsylvania, including rentals and hotel rooms, must be licensed before making sales; non-Pennsylvania transient vendors need a Transient Vendor Certificate.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => null,
            'period' => 'previous calendar year (gross sales into Pennsylvania)',
            'effective' => '2019-07-01',
            'cite' => 'Act 13 of 2019; Sales and Use Tax Bulletin 2019-01; Department of Revenue, Online Retailers',
        ],
        'marketplace' => 'Marketplace facilitators with more than $100,000 in Pennsylvania gross sales (counting facilitated and direct sales) must register, collect and remit on facilitated sales since July 1, 2019; Act 13 of 2019 removed the older option to send notices instead (Department of Revenue, Online Retailers).',
    ],
    'filing' => [
        'frequencies' => 'monthly (with prepayments for large accounts), quarterly or semiannual',
        'rule' => 'Monthly if actual tax liability is over $600 but under $25,000 per quarter; quarterly if under $600 per quarter but over $300 a year; semiannual if $300 or less a year. Accounts with $25,000 or more per quarter make Accelerated Sales Tax (AST) prepayments each month.',
        'due_day' => '20th of the month after the period (semiannual returns due August 20 and February 20)',
        'zero_return_required' => true,
        'prepayments' => 'AST Level 1 ($25,000 to under $100,000 per quarter): 50% of the same month\'s liability from the prior year, or at least 50% of the current month\'s, due by the 20th of the current month; AST Level 2 applies at $100,000 or more per quarter',
        'cite' => 'REV-717 (02-26), Tax Returns (72 P.S. 7217); Department of Revenue, Sales, Use and Hotel Occupancy Tax page',
    ],
    'rates' => [
        'state_rate_pct' => 6.0,
        'local' => '1% local tax in Allegheny County and 2% in Philadelphia, for 7% and 8% combined; no other local sales taxes.',
        'sourcing' => 'REV-717 applies the local tax to taxable sales originating in Allegheny County or Philadelphia',
        'cite' => 'Department of Revenue, Sales, Use and Hotel Occupancy Tax page; REV-717 (02-26)',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'The Allegheny County and Philadelphia local sales taxes are reported to the Department of Revenue on the state return. No separate local sales tax license.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Failure to be licensed may subject the seller to a fine.',
            'cite' => 'REV-717 (02-26), Persons Required to Be Licensed (61 Pa. Code 34.1)',
        ],
        'late_filing' => [
            'summary' => '5% of the tax due for each month or part of a month the return is late, up to 25%, never less than $2, plus interest.',
            'cite' => 'REV-717 (02-26), Additions',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales, use and hotel occupancy tax',
            'Public Transportation Assistance Fund taxes and fees',
            'vehicle rental tax',
            'other business taxes such as employer withholding through the same myPATH registration',
        ],
        'prerequisites' => 'All required Pennsylvania tax returns filed and taxes paid; the license will not be issued otherwise.',
        'cite' => 'REV-717 (02-26), How to Obtain a License; Register My Business for Taxes',
    ],
    'facts' => [
        [
            'text' => 'A remote seller with no physical presence can use an approved Certified Service Provider instead of getting a Pennsylvania license and filing returns itself.',
            'source_url' => 'https://www.pa.gov/agencies/revenue/resources/tax-types-and-information/sales-use-and-hotel-occupancy-tax/online-retailers',
        ],
        [
            'text' => 'The $100,000 remote seller threshold is measured by calendar year; after the first year, collection starts in the second quarter of the following year.',
            'source_url' => 'https://www.pa.gov/agencies/revenue/resources/tax-types-and-information/sales-use-and-hotel-occupancy-tax/online-retailers',
        ],
        [
            'text' => 'Licenses renew automatically every five years only if the business has no outstanding filings or tax debts.',
            'source_url' => 'https://www.pa.gov/content/dam/copapwp-pagov/en/revenue/documents/formsandpublications/formsforbusinesses/sut/documents/rev-717.pdf',
        ],
        [
            'text' => 'Show promoters need a Promoter License at least 30 days before the first show, renewed annually; out-of-state transient vendors must notify the Department 30 days before selling in Pennsylvania.',
            'source_url' => 'https://www.pa.gov/agencies/revenue/resources/tax-types-and-information/sales-use-and-hotel-occupancy-tax',
        ],
        [
            'text' => 'A business with more than one Pennsylvania location must display a copy of the license at each one.',
            'source_url' => 'https://www.pa.gov/content/dam/copapwp-pagov/en/revenue/documents/formsandpublications/formsforbusinesses/sut/documents/rev-717.pdf',
        ],
    ],
    'state_notes' => 'In Pennsylvania the registration is called a Sales, Use and Hotel Occupancy Tax License. The Pennsylvania Department of Revenue issues it. You apply online through the Pennsylvania Online Business Tax Registration in myPATH; there is no paper form. No fee is listed. The Department says the license is mailed 7 to 10 business days after you register. Before a license is issued, all of your Pennsylvania tax returns must be filed and paid. The license renews automatically every five years if you stay current. Remote sellers must register once they had more than $100,000 in Pennsylvania sales in the prior calendar year. There is no transaction count. After you register, the Department sets how often you file. Monthly filing applies above $600 of tax a quarter; quarterly and semiannual filing are for smaller accounts. Returns are due on the 20th of the month after the period. Large accounts, $25,000 or more a quarter, also prepay each month. File a return every period, even with no sales. Late returns cost 5% a month, up to 25%. The one mistake to avoid: letting any Pennsylvania tax account fall behind. Unpaid taxes can block the five-year renewal.',
    'sources' => [
        [
            'title' => 'Sales, Use and Hotel Occupancy Tax (PA Department of Revenue)',
            'url' => 'https://www.pa.gov/agencies/revenue/resources/tax-types-and-information/sales-use-and-hotel-occupancy-tax',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Register My Business for Taxes',
            'url' => 'https://www.pa.gov/services/revenue/register-my-business-for-taxes',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Online Retailers (PA Department of Revenue)',
            'url' => 'https://www.pa.gov/agencies/revenue/resources/tax-types-and-information/sales-use-and-hotel-occupancy-tax/online-retailers',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'REV-717, Retailer\'s Information Guide (02-26)',
            'url' => 'https://www.pa.gov/content/dam/copapwp-pagov/en/revenue/documents/formsandpublications/formsforbusinesses/sut/documents/rev-717.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'PA Revenue Customer Service Center: Is a Sales Tax license necessary to sell taxable items at craft shows?',
            'url' => 'https://revenue-pa.custhelp.com/app/answers/detail/a_id/200/~/is-a-sales-tax-license-necessary-to-sell-taxable-items-at-craft-shows',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'PA Revenue Customer Service Center: How do I register for business taxes?',
            'url' => 'https://revenue-pa.custhelp.com/app/answers/detail/a_id/4152/~/how-do-i-register-for-business-taxes',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'TaxJar: How to register for a sales tax permit in Pennsylvania (cross-check)',
            'url' => 'https://www.taxjar.com/blog/file/register-sales-tax-permit-in-pennsylvania',
            'accessed' => '2026-10-02',
        ],
    ],
];
