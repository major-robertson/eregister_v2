<?php

/*
 * Maryland: Comptroller of Maryland, Sales and Use Tax License. Researched
 * 2026-10-02 from marylandtaxes.gov, interactive.marylandtaxes.gov,
 * marylandcomptroller.gov, mgaleg.maryland.gov, harborcompliance.com.
 * Generated once from the EREG-13 sales tax research; edit this file
 * directly from now on.
 */

return [
    'state' => 'MD',
    'name' => 'Maryland',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Comptroller of Maryland',
        'short' => 'the Comptroller',
        'url' => 'https://www.marylandcomptroller.gov/',
    ],
    'registration' => [
        'term' => 'Sales and Use Tax License',
        'portal' => [
            'name' => 'Maryland Tax Connect (Combined Registration Application online); also Maryland Business Express',
            'url' => 'https://www.marylandcomptroller.gov/',
        ],
        'form' => [
            'number' => 'COM/RAD-093 (Form CRA)',
            'title' => 'Maryland Combined Registration Application',
            'pdf_url' => 'https://marylandtaxes.gov/forms/current_forms/cra.pdf',
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'No fee is listed on the Combined Registration Application for a sales and use tax license.',
            'cite' => 'Form CRA COM/RAD-093 (Rev 11/25); cross-check Harbor Compliance / TaxCloud',
        ],
        'renewal' => [
            'required' => false,
            'summary' => null,
            'cite' => null,
        ],
        'timing' => [
            'online' => 'confirmation number immediately; account information by mail',
            'paper' => 'about 2 weeks',
            'temporary_number' => false,
            'summary' => 'Online applicants get a confirmation number immediately, with account information mailed soon after. The Comptroller asks applicants to allow two weeks for processing the Combined Registration Application; the license arrives by U.S. mail.',
            'cite' => 'Form CRA COM/RAD-093 (Rev 11/25) instructions',
        ],
        'number' => [
            'name' => 'Maryland sales and use tax registration (Central Registration, CR) number',
            'format' => '8 digits, first digit 0 or 1 (temporary show licenses: 10 digits beginning 7125)',
            'cite' => 'resale research 2026-10-01; Business Tax Tip #22 (CR number)',
        ],
    ],
    'nexus' => [
        'physical' => 'A retail vendor that sells or delivers tangible personal property or a taxable service in Maryland must be licensed by the Comptroller before doing business (Tax-General 11-702).',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => 200,
            'period' => 'previous or current calendar year (gross revenue or separate transactions for delivery into Maryland, taxable or not)',
            'effective' => '2018-10-01',
            'cite' => 'COMAR 03.06.01.33; Maryland Tax Alert (Sept. 1, 2018)',
        ],
        'marketplace' => 'A marketplace facilitator must be licensed by the Comptroller and collect on marketplace sales once it meets the same $100,000 or 200-transaction test (Tax-General 11-702(3); threshold for facilitators not separately confirmed on a Comptroller page).',
    ],
    'filing' => [
        'frequencies' => 'monthly, quarterly, semiannual (bi-annual) or annual',
        'rule' => 'New accounts start on quarterly returns. The Comptroller may move a vendor to monthly, quarterly, bi-annual or annual filing based on actual payments, with advance notice. Payments of $10,000 or more must be filed and paid electronically.',
        'due_day' => '20th of the month after the period',
        'zero_return_required' => true,
        'prepayments' => null,
        'cite' => 'Business Tax Tip #22, Maryland Sales and Use Tax FAQ',
    ],
    'rates' => [
        'state_rate_pct' => 6.0,
        'local' => 'No local sales taxes. Special state rates include 3% on data, IT and software publishing services (since July 1, 2025) and 12% on adult-use cannabis (since July 1, 2025).',
        'sourcing' => null,
        'cite' => 'Maryland Tax Alert: Sales and Use Tax Updates 2025-2026',
    ],
    'local_registration' => [
        'required' => false,
        'summary' => 'No local sales tax registration. Separately, county Clerks of the Circuit Court issue local business licenses (for example a trader\'s license), and roadside or vehicle sellers need a transient vendor license.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => 'Operating without the required registration and licenses may bring civil and criminal penalties, including confiscation in some cases (specific statute not confirmed).',
            'cite' => 'Form CRA COM/RAD-093 (Rev 11/25) instructions',
        ],
        'late_filing' => [
            'summary' => 'Failure to pay tax when due: penalty of up to 10% of the unpaid tax, plus interest; the timely-filing discount is lost.',
            'cite' => 'Md. Code, Tax-General 13-701(a); Business Tax Tip #22',
        ],
    ],
    'connected' => [
        'covers' => [
            'sales and use tax license',
            'use tax account',
            'income tax withholding',
            'admissions and amusement tax',
            'tire recycling fee',
            'transient vendor license',
            'unemployment insurance account',
        ],
        'prerequisites' => 'FEIN for corporations, LLCs, partnerships, nonprofits and sole proprietors with employees (a sole proprietor with no employees applying only for a sales tax license may use an SSN); corporations and LLCs register with the State Department of Assessments and Taxation first.',
        'cite' => 'Form CRA COM/RAD-093 (Rev 11/25)',
    ],
    'facts' => [
        [
            'text' => 'Since July 1, 2025, Maryland taxes data, information technology and software publishing services (NAICS 518, 519, 5132 and 5415) at 3% under Chapter 604 of the Acts of 2025.',
            'source_url' => 'https://www.marylandcomptroller.gov/content/dam/mdcomp/tax/legal-publications/alerts/tax-alert-sales-and-use-tax-updates.pdf',
        ],
        [
            'text' => 'Since July 1, 2025, the sales tax rate on adult-use cannabis is 12%, up from 9%.',
            'source_url' => 'https://www.marylandcomptroller.gov/content/dam/mdcomp/tax/legal-publications/alerts/tax-alert-sales-and-use-tax-updates.pdf',
        ],
        [
            'text' => 'Vendors that file and pay on time keep a discount of 1.2% of the first $6,000 of tax and 0.9% above that, up to $500 per return.',
            'source_url' => 'https://www.marylandcomptroller.gov/content/dam/mdcomp/tax/legal-publications/tips/business/bustip22.pdf',
        ],
        [
            'text' => 'The 2025 law repealed the exemption for custom computer software and services related to it.',
            'source_url' => 'https://www.marylandcomptroller.gov/content/dam/mdcomp/tax/legal-publications/alerts/tax-alert-sales-and-use-tax-updates.pdf',
        ],
        [
            'text' => 'Maryland has no prescribed resale certificate form; a signed statement with the buyer\'s name, address and Maryland sales and use tax registration number is enough.',
            'source_url' => 'https://www.marylandcomptroller.gov/content/dam/mdcomp/tax/legal-publications/tips/business/bustip22.pdf',
        ],
    ],
    'state_notes' => 'In Maryland, you need a Sales and Use Tax License from the Comptroller of Maryland. You apply with the Combined Registration Application, Form CRA. You can file it online through Maryland Tax Connect or Maryland Business Express, or on paper. The application lists no fee. Online, you get a confirmation number right away. The Comptroller asks you to allow about two weeks for processing, and the license comes by mail. The same application can open withholding, unemployment and other accounts. Corporations and LLCs must register with the State Department of Assessments and Taxation first. New accounts start on quarterly returns, due on the 20th of the month after each quarter. The Comptroller may later move you to monthly, semiannual or annual filing based on your payments. The state rate is 6%, and there is no local sales tax. Since July 2025, many IT, data and software services are taxed at 3%. Remote sellers must register after $100,000 in sales or 200 transactions into Maryland in the current or prior year. The mistake to avoid: missing the timely-filing discount. You keep up to $500 of tax per return only when you file and pay by the due date.',
    'sources' => [
        [
            'title' => 'Maryland Combined Registration Application, COM/RAD-093 (Rev 11/25)',
            'url' => 'https://marylandtaxes.gov/forms/current_forms/cra.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Maryland Combined Registration Online Application',
            'url' => 'https://interactive.marylandtaxes.gov/webapps/comptrollercra/entrance.asp',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Business Tax Tip #22: Maryland Sales and Use Tax FAQ',
            'url' => 'https://www.marylandcomptroller.gov/content/dam/mdcomp/tax/legal-publications/tips/business/bustip22.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Maryland Tax Alert: Sales and Use Tax Updates 2025-2026',
            'url' => 'https://www.marylandcomptroller.gov/content/dam/mdcomp/tax/legal-publications/alerts/tax-alert-sales-and-use-tax-updates.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Maryland Tax Alert (Sept. 1, 2018): out-of-state vendor thresholds',
            'url' => 'https://www.marylandcomptroller.gov/legal-library/sut-ta-sep-01-2018.html',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => '2025 Tax Changes for Businesses',
            'url' => 'https://www.marylandcomptroller.gov/content/dam/mdcomp/md/legal-publications/2025-tax-changes-for-businesses.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Md. Code, Tax-General 11-702',
            'url' => 'https://mgaleg.maryland.gov/mgawebsite/Laws/StatuteText?article=gtg&section=11-702&enactments=false',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Md. Code, Tax-General 13-701',
            'url' => 'https://mgaleg.maryland.gov/mgawebsite/Laws/StatuteText?article=gtg&section=13-701&enactments=false',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Harbor Compliance: Maryland sales and use tax registration (cross-check)',
            'url' => 'https://www.harborcompliance.com/register-maryland-sales-use-tax-license-permit',
            'accessed' => '2026-10-02',
        ],
    ],
];
