<?php

/*
 * Florida: Florida Department of Revenue, Certificate of Registration
 * (Form DR-11) as a sales and use tax dealer. Researched 2026-10-02 from
 * floridarevenue.com, leg.state.fl.us. Generated once from the EREG-13
 * sales tax research; edit this file directly from now on.
 */

return [
    'state' => 'FL',
    'name' => 'Florida',
    'term_label' => 'Certificate of Registration',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Florida Department of Revenue',
        'short' => 'the Department',
        'url' => 'https://floridarevenue.com/',
    ],
    'registration' => [
        'term' => 'Certificate of Registration (Form DR-11) as a sales and use tax dealer',
        'portal' => [
            'name' => 'Florida Business Tax Application (online registration)',
            'url' => 'https://floridarevenue.com/taxes/registration',
        ],
        'form' => [
            'number' => 'DR-1',
            'title' => 'Florida Business Tax Application (R. 01/26)',
            'pdf_url' => 'https://floridarevenue.com/Forms_library/current/dr1.pdf',
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'No registration fee appears in s. 212.18(3), F.S., Form DR-1 or its instructions DR-1N (both R. 01/26). A business that operates without registering can be charged a $100 registration fee.',
            'cite' => 's. 212.18(3), F.S.; DR-1N (R. 01/26), https://floridarevenue.com/Forms_library/current/dr1n.pdf',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'The Certificate of Registration does not expire. The Department issues each active dealer a new Annual Resale Certificate (DR-13) every year in mid-November.',
            'cite' => 's. 212.18(3)(e), F.S.; GT-300015 (R. 11/25), https://floridarevenue.com/Forms_library/current/guides/gt300015.pdf',
        ],
        'timing' => [
            'online' => 'about 3 business days',
            'paper' => null,
            'temporary_number' => false,
            'summary' => 'The Department says to allow three business days for an online application before checking its status or retrieving the certificate number online. The Certificate of Registration, Annual Resale Certificate and welcome materials then arrive by mail.',
            'cite' => 'https://floridarevenue.com/taxes/eservices/Pages/registration.aspx; DR-1N (R. 01/26)',
        ],
        'number' => [
            'name' => 'Florida sales and use tax certificate number',
            'format' => '13 digits (from the resale certificate research)',
            'cite' => null,
        ],
    ],
    'nexus' => [
        'physical' => 'Anyone selling, leasing or renting tangible personal property, renting transient accommodations, selling admissions or certain services in Florida must register each place of business before starting (s. 212.18(3)(a), F.S.).',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => null,
            'period' => 'previous calendar year (taxable remote sales into Florida)',
            'effective' => '2021-07-01',
            'cite' => 's. 212.0596(1), F.S. (SB 50, 2021)',
        ],
        'marketplace' => 'From July 1, 2021, a marketplace provider with more than $100,000 of sales into Florida in the previous calendar year must register electronically and collect tax on sales it facilitates (s. 212.18(3)(c), F.S.; https://floridarevenue.com/taxes/Documents/dr1mp.pdf).',
    ],
    'filing' => [
        'frequencies' => 'monthly, quarterly, semiannual or annual by annual tax collected',
        'rule' => 'Monthly if annual sales tax collections exceed $1,000; quarterly for $501 to $1,000; semiannual for $101 to $500; annual for $100 or less. Most new businesses start quarterly unless they ask for another frequency. The Department reviews accounts each year.',
        'due_day' => 'due the 1st and late after the 20th of the month after the period',
        'zero_return_required' => true,
        'prepayments' => 'Dealers who paid $200,000 or more in sales tax (excluding surtax and transient rental taxes) in the prior state fiscal year make estimated payments the next calendar year.',
        'cite' => 'GT-300015 (R. 11/25), https://floridarevenue.com/Forms_library/current/guides/gt300015.pdf',
    ],
    'rates' => [
        'state_rate_pct' => 6.0,
        'local' => 'County discretionary sales surtax adds to the 6% state rate; surtax applies only to the first $5,000 of a single sale of tangible personal property.',
        'sourcing' => 'surtax follows the county where the goods are delivered (destination)',
        'cite' => 'GT-300015 (R. 11/25); Form DR-15DSS',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => null,
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Failing or refusing to register is a first-degree misdemeanor and carries a $100 registration fee; willfully failing to register after notice from the Department is a third-degree felony.',
            'cite' => 's. 212.18(3)(d), F.S.',
        ],
        'late_filing' => [
            'summary' => '10% of the tax not filed or paid on time, with a $50 minimum that applies even when no tax is due, plus interest.',
            'cite' => 's. 212.12(2)(a), F.S.; GT-300015 (R. 11/25)',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales and use tax',
            'discretionary sales surtax',
            'reemployment tax',
            'documentary stamp tax',
            'communications services tax',
            'other Department taxes and fees on the same DR-1',
        ],
        'prerequisites' => null,
        'cite' => 'DR-1N (R. 01/26), https://floridarevenue.com/Forms_library/current/dr1n.pdf',
    ],
    'facts' => [
        [
            'text' => 'Florida repealed the state sales tax and surtax on commercial rent (office, retail, warehouse and self-storage space) for rental periods beginning on or after October 1, 2025.',
            'source_url' => 'https://floridarevenue.com/taxes/tips/Documents/TIP_25A01-04.pdf',
        ],
        [
            'text' => 'Florida\'s $100,000 remote seller threshold counts only taxable remote sales in the previous calendar year, and has no transaction count.',
            'source_url' => 'https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&URL=0200-0299/0212/Sections/0212.0596.html',
        ],
        [
            'text' => 'Dealers that file and pay electronically on time keep a collection allowance of 2.5% of the first $1,200 of tax, up to $30 per return.',
            'source_url' => 'https://floridarevenue.com/Forms_library/current/guides/gt300015.pdf',
        ],
        [
            'text' => 'Businesses that paid $5,000 or more in tax in the prior state fiscal year must file and pay electronically the next calendar year.',
            'source_url' => 'https://floridarevenue.com/Forms_library/current/guides/gt300015.pdf',
        ],
        [
            'text' => 'The Department mails registered dealers a new Annual Resale Certificate (DR-13) each year; dealers print it at floridarevenue.com/taxes/printcertificate.',
            'source_url' => 'https://floridarevenue.com/taxes/taxesfees/Pages/annual_resale_certificate_sut.aspx',
        ],
    ],
    'state_notes' => 'In Florida, you register as a sales and use tax dealer with the Florida Department of Revenue. The state issues a Certificate of Registration (Form DR-11). Apply online through the Florida Business Tax Application, or on paper Form DR-1. You need a separate registration for each business location. The current form and statute show no registration fee. The Department says to allow three business days after an online application before you check its status. Your certificate, an Annual Resale Certificate and return forms then come by mail. The certificate does not expire, but you get a new Annual Resale Certificate each year. Most new businesses file quarterly. The Department moves you to monthly if you collect more than $1,000 a year. Returns are due on the 1st and are late after the 20th of the next month. The state rate is 6%. Most counties add a surtax, based on where you deliver. The one mistake to avoid: skipping a return when you had no sales. Florida charges a $50 minimum late penalty even when no tax is due.',
    'sources' => [
        [
            'title' => 'Florida DOR: Account Management and Registration',
            'url' => 'https://floridarevenue.com/taxes/eservices/Pages/registration.aspx',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Florida DOR: Business Owner\'s Guide for the Major Florida Taxes, GT-300015 (R. 11/25)',
            'url' => 'https://floridarevenue.com/Forms_library/current/guides/gt300015.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Florida DOR: Form DR-1 (R. 01/26)',
            'url' => 'https://floridarevenue.com/Forms_library/current/dr1.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Florida DOR: DR-1N Registering Your Business (R. 01/26)',
            'url' => 'https://floridarevenue.com/Forms_library/current/dr1n.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Florida Statutes s. 212.18',
            'url' => 'https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&URL=0200-0299/0212/Sections/0212.18.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Florida Statutes s. 212.12',
            'url' => 'https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&URL=0200-0299/0212/Sections/0212.12.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Florida Statutes s. 212.0596',
            'url' => 'https://www.leg.state.fl.us/statutes/index.cfm?App_mode=Display_Statute&URL=0200-0299/0212/Sections/0212.0596.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Florida DOR TIP 25A01-04: Repeal of tax on commercial rentals',
            'url' => 'https://floridarevenue.com/taxes/tips/Documents/TIP_25A01-04.pdf',
            'accessed' => '2026-10-02',
        ],
    ],
];
