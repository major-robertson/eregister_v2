<?php

/*
 * Louisiana: Louisiana Department of Revenue, Louisiana Revenue Account
 * Number for sales tax (state sales tax account with LDR), plus a separate
 * local sales tax account with each parish collector. Researched
 * 2026-10-02 from revenue.louisiana.gov, dam.ldr.la.gov, legis.la.gov,
 * remotesellers.louisiana.gov, parishe-file.revenue.louisiana.gov,
 * lulstb.com, salestaxinstitute.com. Generated once from the EREG-13 sales
 * tax research; edit this file directly from now on.
 */

return [
    'state' => 'LA',
    'name' => 'Louisiana',
    'term_label' => 'Louisiana Revenue Account Number',
    'researched_on' => '2026-10-02',
    'agency' => [
        'name' => 'Louisiana Department of Revenue',
        'short' => 'LDR',
        'url' => 'https://revenue.louisiana.gov/',
    ],
    'registration' => [
        'term' => 'Louisiana Revenue Account Number for sales tax (state sales tax account with LDR), plus a separate local sales tax account with each parish collector',
        'portal' => [
            'name' => 'LaTAP (Louisiana Taxpayer Access Point), Register My Business; geauxBIZ for new entities; Parish E-File for local parish accounts',
            'url' => 'https://latap.revenue.louisiana.gov/',
        ],
        'form' => [
            'number' => 'R-16019',
            'title' => 'Application for Louisiana Revenue Account Number',
            'pdf_url' => 'https://dam.ldr.la.gov/taxforms/16019-5-24-F.pdf',
            'online_only' => false,
        ],
        'fee' => [
            'amount_cents' => 0,
            'summary' => 'No LDR registration fee is listed on Form R-16019 or LDR\'s registration pages. Parish collectors set their own rules; none is confirmed here.',
            'cite' => 'Form R-16019 and instructions R-16019i (5/24)',
        ],
        'renewal' => [
            'required' => false,
            'summary' => 'The state sales tax account does not expire; no renewal is described by LDR.',
            'cite' => null,
        ],
        'timing' => [
            'online' => null,
            'paper' => null,
            'temporary_number' => false,
            'summary' => 'LDR does not publish a processing time on the pages reviewed. A business that has applied for a parish account but has no number yet may file its first return with \'applied for\' as the account number.',
            'cite' => 'LULSTB FAQs for Combined State and Local Return',
        ],
        'number' => [
            'name' => 'LDR Account Number (consolidated filers also have a Location ID); remote sellers get a separate Commission account number',
            'format' => '10-digit LDR account number (e.g. 1234567001); consolidated-filer location numbers are B followed by 11 digits; remote seller Commission accounts are 9 digits',
            'cite' => 'LULSTB FAQs for Combined State and Local Return (state number is the first 10 digits; B + 11 digits); resale research 2026-10-01',
        ],
    ],
    'nexus' => [
        'physical' => 'A dealer that sells, leases or rents tangible personal property or digital products in Louisiana, sells taxable services, or has salesmen or agents in the state must register with LDR and with the parish collector for each parish where it makes sales.',
        'economic' => [
            'revenue_usd' => 100000,
            'transactions' => null,
            'period' => 'previous or current calendar year (gross revenue from retail sales delivered into Louisiana)',
            'effective' => '2023-08-01',
            'cite' => 'La. R.S. 47:301(4)(m)(i) as amended by 2023 HB 171 (removed the 200-transaction test effective Aug. 1, 2023)',
        ],
        'marketplace' => 'A marketplace facilitator with more than $100,000 in Louisiana retail sales in the current or prior year must register with the Louisiana Sales and Use Tax Commission for Remote Sellers and collect state and local tax on facilitated sales; 2023 HB 171 also dropped its 200-transaction test (La. R.S. 47:340.1).',
    ],
    'filing' => [
        'frequencies' => 'monthly or quarterly (occasional filers by approval)',
        'rule' => 'LDR registers new dealers as monthly filers; dealers with only occasional sales may request casual filing. Under the combined state and local return, each location must use the same frequency for the state and every parish, monthly or quarterly, matching the most frequent jurisdiction. Since Jan. 1, 2026 all Forms R-1029 must be filed and paid electronically.',
        'due_day' => '20th of the month after the month or quarter',
        'zero_return_required' => true,
        'prepayments' => null,
        'cite' => 'Form R-1029i (1/25); R-16019i (5/24); RIB 25-030; LULSTB FAQs for Combined State and Local Return',
    ],
    'rates' => [
        'state_rate_pct' => 5.0,
        'local' => 'Parish, city and school-board taxes are added on top of the 5% state rate and vary by location; rates are looked up through the parish collectors\' Sales Tax Portal. The 5% state rate runs until Dec. 31, 2029.',
        'sourcing' => null,
        'cite' => 'RIB 25-007; LDR Tax Reform FAQs (9.23.25); https://revenue.louisiana.gov/tax-education-and-faqs/faqs/sales-tax/what-is-the-sales-tax-rate-in-louisiana/',
    ],
    'local_registration' => [
        'required' => true,
        'summary' => 'Yes. Louisiana\'s parish sales taxes are collected locally, not by LDR. An in-state seller must get a local account number from the sales tax collector of the parish where it is located and of each parish it delivers into, in addition to its LDR account. Applications go through Parish E-File, which can now request numbers for several parishes in one application and assigns a Master Location Number per business location. Since early 2026, Parish E-File offers a Combined State and Local Sales Tax Return that files the state and all parish returns at once. There are 64 parish jurisdictions; examples: Orleans Parish (City of New Orleans Bureau of Revenue), Jefferson Parish (Sheriff\'s Office Bureau of Revenue and Taxation), East Baton Rouge Parish (Baton Rouge Department of Finance), Caddo Parish (sales tax office at 3300 Dee Street, Shreveport), Lafayette Parish (Lafayette Parish School System Sales Tax Division) and St. Tammany Parish (Sheriff\'s Office). Cameron Parish is reported on the combined return without a separate account number. Remote sellers without physical presence register only with the Louisiana Sales and Use Tax Commission for Remote Sellers, which covers state and local tax.',
    ],
    'penalties' => [
        'no_permit' => [
            'summary' => null,
            'cite' => null,
        ],
        'late_filing' => [
            'summary' => '5% of the net tax for each 30 days or part of 30 days the return is late, up to 25%, plus interest. Failing to file electronically when required adds the greater of $100 or 5% of the tax.',
            'cite' => 'Form R-1029i (1/25) Line 12 (La. R.S. 47:1602); RIB 25-030 (La. R.S. 47:1520(B))',
        ],
    ],
    'connected' => [
        'covers' => [
            'state sales tax',
            'use tax',
            'withholding tax',
            'statewide hotel/motel tax',
            'New Orleans Exhibition Hall and hotel/motel taxes',
            'automobile rental tax',
            'prepaid wireless 911 charge',
            'corporation income and franchise tax',
        ],
        'prerequisites' => 'FEIN with a copy of the IRS letter (CP 575 or 147C) for all sales tax accounts; Louisiana Secretary of State registration for corporations and LLCs (geauxBIZ combines both). Parish accounts are separate.',
        'cite' => 'Form R-16019i (5/24); Form R-1029i (1/25) item 3',
    ],
    'facts' => [
        [
            'text' => 'Since Jan. 1, 2025, the Louisiana state sales tax rate is 5% (Act 11 of the 2024 Third Extraordinary Session), up from 4.45%; the increase runs through Dec. 31, 2029.',
            'source_url' => 'https://dam.ldr.la.gov/lawspolicies/RIB%2025-007%20-%20State%20Sales%20Tax%20Rate.pdf',
        ],
        [
            'text' => 'Since Jan. 1, 2025, Louisiana taxes digital products (digital audio and video, books, codes, apps and games, periodicals) and prewritten computer software access services such as Microsoft 365 and Zoom.',
            'source_url' => 'https://dam.ldr.la.gov/miscellaneous/Sales%20Tax%20Reform%20FAQs%209.23.25.pdf',
        ],
        [
            'text' => 'From Jan. 1, 2026, every Form R-1029 sales tax return and payment must be made electronically, through LaTAP, Parish E-File, Sales Tax Online or approved software.',
            'source_url' => 'https://dam.ldr.la.gov/lawspolicies/RIB%2025-030%20Electronic%20Filing%20and%20Payment%20Mandates%20Expanded.pdf',
        ],
        [
            'text' => 'State vendor\'s compensation is 0.84% of state tax for timely filers, capped at $750 per dealer per month since Jan. 1, 2025.',
            'source_url' => 'https://dam.ldr.la.gov/taxforms/1029i(1_25)%20FINAL%20_RV.pdf',
        ],
        [
            'text' => 'Remote sellers register only with the Louisiana Sales and Use Tax Commission for Remote Sellers, not with LDR and each parish, and remit state and local tax at actual rates to the Commission.',
            'source_url' => 'https://remotesellers.louisiana.gov/FAQ',
        ],
    ],
    'state_notes' => 'Louisiana registration has two layers. First, you get a Louisiana Revenue Account Number for sales tax from the Louisiana Department of Revenue (LDR). You apply online through LaTAP or geauxBIZ, or mail Form R-16019. LDR lists no registration fee, and you need an FEIN. Second, parish sales taxes are run by local collectors, not LDR. You must also get a local account from the parish where you are located and from each parish you deliver into. You apply for these through Parish E-File, which can now handle several parishes in one application. Returns are due by the 20th of the month after the period. New dealers start as monthly filers. Since 2026, all returns must be filed online, and Parish E-File offers one combined state and local return per location. The state rate is 5%, and parish and city taxes are added on top. Louisiana also taxes digital products and software access services since 2025. Remote sellers with over $100,000 in Louisiana retail sales register with the Remote Sellers Commission instead. The mistake to avoid: registering with the state only. A Louisiana seller who skips its parish accounts still owes local tax, penalties and interest to each parish.',
    'sources' => [
        [
            'title' => 'LDR: Starting or Buying a Business',
            'url' => 'https://revenue.louisiana.gov/businesses/general-resources/business-registration/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'LDR: General Sales & Use Tax',
            'url' => 'https://revenue.louisiana.gov/businesses/general-sales-and-use-taxes/general-sales-use-tax/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Form R-16019 Application for Louisiana Revenue Account Number',
            'url' => 'https://dam.ldr.la.gov/taxforms/16019-5-24-F.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'R-16019i Instructions (5/24)',
            'url' => 'https://dam.ldr.la.gov/taxforms/16019i-5-24.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'R-1029i Sales Tax Return General Instructions (1/25)',
            'url' => 'https://dam.ldr.la.gov/taxforms/1029i(1_25)%20FINAL%20_RV.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'RIB 25-007 State Sales Tax Rate',
            'url' => 'https://dam.ldr.la.gov/lawspolicies/RIB%2025-007%20-%20State%20Sales%20Tax%20Rate.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'RIB 25-030 Electronic Filing and Payment Mandates Expanded',
            'url' => 'https://dam.ldr.la.gov/lawspolicies/RIB%2025-030%20Electronic%20Filing%20and%20Payment%20Mandates%20Expanded.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'LDR Tax Reform FAQs (9.23.25)',
            'url' => 'https://dam.ldr.la.gov/miscellaneous/Sales%20Tax%20Reform%20FAQs%209.23.25.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => '2023 HB 171 (reengrossed)',
            'url' => 'https://legis.la.gov/legis/ViewDocument.aspx?d=1317109',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Louisiana Sales and Use Tax Commission for Remote Sellers FAQ',
            'url' => 'https://remotesellers.louisiana.gov/FAQ',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Parish E-File: About Us',
            'url' => 'https://parishe-file.revenue.louisiana.gov/aboutus.aspx',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'LULSTB: Parish E-File Online Sales Tax Filing Information',
            'url' => 'https://lulstb.com/resources/online-sales-tax-filing-information/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'LULSTB: FAQs for Combined State and Local Return',
            'url' => 'https://lulstb.com/download/146/parish-e-file-online-filing/24464/faqs-for-combined-state-and-local-return.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'LULSTB: Local Parish Tax Account Number Registration Application',
            'url' => 'https://lulstb.com/download/146/parish-e-file-online-filing/24384/electronic-registration-application-for-local-sales-tax-account-numbers.pdf',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'LULSTB: Jurisdiction List',
            'url' => 'https://lulstb.com/jurisdiction-list/',
            'accessed' => '2026-10-02',
        ],
        [
            'title' => 'Sales Tax Institute: Louisiana combined state-local filing system (cross-check)',
            'url' => 'https://www.salestaxinstitute.com/resources/louisiana-combined-state-local-sales-tax-filing-system',
            'accessed' => '2026-10-02',
        ],
    ],
];
