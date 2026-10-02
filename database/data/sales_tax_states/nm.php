<?php

/*
 * New Mexico: New Mexico Taxation and Revenue Department, Business Tax
 * Registration (New Mexico Business Tax Identification Number for gross
 * receipts tax). Researched 2026-10-02 from tax.newmexico.gov,
 * realfile.tax.newmexico.gov. Generated once from the EREG-13 sales tax
 * research; edit this file directly from now on.
 */

return [
    'state' => 'NM',
    'name' => 'New Mexico',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'New Mexico Taxation and Revenue Department',
        'short' => 'TRD',
        'url' => 'https://www.tax.newmexico.gov/',
    ],
    'registration' => [
        'term' => 'Business Tax Registration (New Mexico Business Tax Identification Number for gross receipts tax)',
        'portal' => [
            'name' => 'Taxpayer Access Point (TAP), Apply for a New Mexico Business Tax ID',
            'url' => 'https://tap.state.nm.us/',
        ],
        'form' => [
            'number' => 'ACD-31015',
            'title' => 'Business Tax Registration Application and Update Form',
            'pdf_url' => 'https://www.tax.newmexico.gov/wp-content/uploads/2022/11/acd-31015.pdf',
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'There is no fee to register or obtain a Business Tax Identification Number.',
            'cite' => 'TRD, Who must register a business?',
        ],
        'renewal' => [
            'required' => false,
            'summary' => null,
            'cite' => null,
        ],
        'timing' => [
            'online' => 'Login credentials work as soon as the online application is completed and approved',
            'paper' => 'Processed in the order received; processing times may vary',
            'temporary_number' => false,
            'summary' => 'TRD says that upon completion and approval of the online application you can log in to TAP. Paper ACD-31015 applications (by mail or at a district office by appointment) are processed in the order received, and TRD then mails the registration certificate. No day count is published.',
            'cite' => 'TRD, Who must register a business?',
        ],
        'number' => [
            'name' => 'New Mexico Business Tax Identification Number (NMBTIN)',
            'format' => '11 digits; ACD-31015 prints it as 0-_______-00-_',
            'cite' => 'ACD-31015 (Rev. 8/30/2024), header',
        ],
    ],
    'nexus' => [
        'physical' => 'Anyone who engages in business in New Mexico, meaning carrying on any activity for direct or indirect benefit, including selling goods or performing services in the state, must register with TRD.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => null,
            'period' => 'previous calendar year (taxable gross receipts sourced to New Mexico)',
            'effective' => '2019-07-01',
            'cite' => 'NMSA 1978, 7-9-3.3 (engaging in business), as amended by HB 6 (2019); TRD, Who must register a business?; TRD-41413 instructions',
        ],
        'marketplace' => 'Marketplace providers without physical presence are engaging in business, and must register and report, once they have $100,000 of taxable gross receipts sourced to New Mexico in the previous calendar year (TRD, Who must register a business?; TRD-41413 instructions).',
    ],
    'filing' => [
        'frequencies' => 'monthly, quarterly or semiannual (plus seasonal, special event and temporary statuses)',
        'rule' => 'Monthly if combined taxes average more than $200 a month (or by choice); quarterly if under $600 a quarter (under $200 a month on average); semiannual if under $1,200 for the six months. Your status is printed on the registration certificate.',
        'due_day' => '25th of the month after the period',
        'zero_return_required' => true,
        'prepayments' => null,
        'cite' => 'TRD GRT Filer\'s Kit (January 2024), Filing status; TRD-41413 instructions (Rev. 07/01/2025)',
    ],
    'rates' => [
        'state_rate_pct' => 4.875,
        'local' => 'Counties and municipalities add local option gross receipts taxes; the combined rate depends on the location code in TRD\'s semiannual rate schedule (updated January 1 and July 1).',
        'sourcing' => 'destination-based since July 1, 2021 (reported under the location code where the customer is), with exceptions such as construction, real estate and utilities',
        'cite' => 'TRD-41413 instructions (Rev. 07/01/2025), Location Code and Tax Rate',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'Local gross receipts taxes are reported to TRD by location code on the state return. Some cities run their own general business licenses, but no separate local sales tax registration.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => null,
            'cite' => null,
        ],
        'late_filing' => [
            'summary' => '2% of the tax due for each month or partial month late, up to 20%, or a $5 minimum, whichever is greater, plus interest.',
            'cite' => 'NMSA 1978, 7-1-69; TRD-41413 instructions, Line 4 Penalty',
        ],
    ],
    'connected' => [
        'covers' => [
            'gross receipts tax',
            'compensating tax',
            'wage withholding tax',
            'non-wage withholding tax',
            'governmental gross receipts tax',
            'leased vehicle gross receipts tax',
        ],
        'prerequisites' => 'An FEIN for any entity other than a sole proprietor without employees. Corporations also register with the New Mexico Secretary of State.',
        'cite' => 'TRD, Who must register a business?',
    ],
    'facts' => [
        [
            'text' => 'New Mexico has a gross receipts tax, not a sales tax. It is imposed on the business, which may pass it on to the buyer, and it applies to most services as well as goods.',
            'source_url' => 'https://realfile.tax.newmexico.gov/trd-41413ins.pdf',
        ],
        [
            'text' => 'Since July 1, 2021, gross receipts are reported under the location code where the customer is (destination sourcing). Before that, most receipts were reported at the seller\'s business location.',
            'source_url' => 'https://realfile.tax.newmexico.gov/trd-41413ins.pdf',
        ],
        [
            'text' => 'New Mexico\'s remote seller threshold is $100,000 of taxable gross receipts in the previous calendar year, with no transaction count.',
            'source_url' => 'https://www.tax.newmexico.gov/businesses/who-must-register-a-business/',
        ],
        [
            'text' => 'Taxpayers whose average monthly gross receipts tax was $1,000 or more in the preceding calendar year must file and pay electronically.',
            'source_url' => 'https://realfile.tax.newmexico.gov/trd-41413ins.pdf',
        ],
        [
            'text' => 'Buyers prove resale or other deductions with Nontaxable Transaction Certificates (NTTCs), which registered buyers obtain from TRD in TAP rather than downloading a form.',
            'source_url' => 'https://www.tax.newmexico.gov/businesses/non-taxable-transaction-certificates-nttc/',
        ],
    ],
    'state_notes' => 'New Mexico does not issue a sales tax permit. It has a gross receipts tax, and you register for a New Mexico Business Tax Identification Number (NMBTIN) with the Taxation and Revenue Department (TRD). Apply online in the Taxpayer Access Point (TAP), or file Form ACD-31015, Business Tax Registration Application, at a district office by appointment or by mail. There is no fee. Online, you can log in once the application is approved. Paper applications are processed in the order received, and TRD mails a registration certificate. Get an FEIN first unless you are a sole owner with no employees. Out-of-state sellers must register once they have $100,000 of taxable New Mexico receipts in the previous calendar year. There is no transaction count. The tax applies to most services, not just goods. After you register, your certificate shows your filing status. Monthly applies if your tax averages more than $200 a month; smaller accounts file quarterly or every six months. Returns (TRD-41413) are due on the 25th of the following month, even with zero receipts. Late returns cost 2% a month, up to 20%. The one mistake to avoid: reporting at your own address. Since July 2021, receipts are reported where the customer is.',
    'sources' => [
        [
            'title' => 'TRD, Who must register a business?',
            'url' => 'https://www.tax.newmexico.gov/businesses/who-must-register-a-business/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'ACD-31015, Business Tax Registration Application and Update Form (Rev. 8/30/2024)',
            'url' => 'https://www.tax.newmexico.gov/wp-content/uploads/2022/11/acd-31015.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'TRD-41413 Gross Receipts Tax Return instructions (Rev. 07/01/2025)',
            'url' => 'https://realfile.tax.newmexico.gov/trd-41413ins.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'GRT Filer\'s Kit (January 2024)',
            'url' => 'https://realfile.tax.newmexico.gov/2024_Jan_GRT%20FilersKit.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'TRD, Nontaxable Transaction Certificates',
            'url' => 'https://www.tax.newmexico.gov/businesses/non-taxable-transaction-certificates-nttc/',
            'accessed' => '2026-10-02',
        ],
    ],
];
